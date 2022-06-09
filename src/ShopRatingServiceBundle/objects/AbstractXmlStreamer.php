<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
https://github.com/prewk/XmlStreamer

Licensed under MIT:
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
abstract class XmlStreamer
{
    private $handle;
    private $totalBytes;
    private $readBytes = 0;
    private $nodeIndex = 0;
    private $chunk = '';
    private $chunkSize;
    private $readFromChunkPos;

    private $rootNode;
    private $customRootNode;

    /**
     * @param string|resource $mixed Path to XML file OR file handle
     * @param int $chunkSize Bytes to read per cycle (Optional, default is 16 KiB)
     * @param string|null $customRootNode Specific root node to use (Optional)
     * @param int|null $totalBytes Xml file size - Required if supplied file handle
     */
    public function __construct($mixed, $chunkSize = 16384, $customRootNode = null, $totalBytes = null, $customChildNode = null)
    {
        if (is_string($mixed)) {
            $this->handle = fopen($mixed, 'r');
            if (isset($totalBytes)) {
                $this->totalBytes = $totalBytes;
            } else {
                $this->totalBytes = filesize($mixed);
            }
        } elseif (is_resource($mixed)) {
            $this->handle = $mixed;
            if (!isset($totalBytes)) {
                throw new \Exception('totalBytes parameter required when supplying a file handle.');
            }
            $this->totalBytes = $totalBytes;
        }

        $this->chunkSize = $chunkSize;
        $this->customRootNode = $customRootNode;
        $this->customChildNode = $customChildNode;
        $this->init();
    }

    /**
     * Called after the constructor completed setup of the class. Can be overriden in a child class.
     */
    public function init()
    {
    }

    /**
     * Called after a chunk was completed. Useful to chunk INSERT data into DB.
     */
    public function chunkCompleted()
    {
    }

    /**
     * Gets called for every XML node that is found as a child to the root node.
     *
     * @param string $xmlString     Complete XML tree of the node as a string
     * @param string $elementName   Name of the node for easy access
     * @param int $nodeIndex     Zero-based index that increments for every node
     *
     * @return bool - If false is returned, the streaming will stop
     */
    abstract public function processNode($xmlString, $elementName, $nodeIndex);

    /**
     * Gets the total read bytes so far.
     */
    public function getReadBytes()
    {
        return $this->readBytes;
    }

    /**
     * Gets the total file size of the xml.
     */
    public function getTotalBytes()
    {
        return $this->totalBytes;
    }

    /**
     * Starts the streaming and parsing of the XML file.
     */
    public function parse()
    {
        $counter = 0;
        $continue = true;
        while ($continue) {
            $continue = $this->readNextChunk();

            ++$counter;
            if (!isset($this->rootNode)) {
                // Find root node
                if (isset($this->customRootNode)) {
                    $customRootNodePos = strpos($this->chunk, "<{$this->customRootNode}");
                    if (false !== $customRootNodePos) {
                        // Found custom root node
                        // Support attributes
                        $closer = strpos(substr($this->chunk, $customRootNodePos), '>');
                        $readFromChunkPos = $customRootNodePos + $closer + 1;

                        // Custom child node?
                        if (isset($this->customChildNode)) {
                            // Find it in the chunk
                            $customChildNodePos = strpos(substr($this->chunk, $readFromChunkPos), "<{$this->customChildNode}");
                            if (false !== $customChildNodePos) {
                                // Found it!
                                $readFromChunkPos = $readFromChunkPos + $customChildNodePos;
                            } else {
                                // Didn't find it - read a larger chunk and do everything again
                                continue;
                            }
                        }

                        $this->rootNode = $this->customRootNode;
                        $this->readFromChunkPos = $readFromChunkPos;
                    } else {
                        // Clear chunk to save memory, it doesn't contain the root anyway
                        $this->readFromChunkPos = 0;
                        $this->chunk = '';
                        continue;
                    }
                } else {
                    // XML1.0 standard allows almost all Unicode characters even Chinese and Cyrillic.
                    // see: http://en.wikipedia.org/wiki/XML#International_use
                    preg_match('/<([^>\?]+)>/', $this->chunk, $matches);
                    if (isset($matches[1])) {
                        // Found root node
                        $this->rootNode = $matches[1];
                        $this->readFromChunkPos = strpos($this->chunk, $matches[0]) + strlen($matches[0]);
                    } else {
                        // Clear chunk to save memory, it doesn't contain the root anyway
                        $this->readFromChunkPos = 0;
                        $this->chunk = '';
                        continue;
                    }
                }
            }

            while (true) {
                $fromChunkPos = substr($this->chunk, $this->readFromChunkPos);

                // Find element
                // XML1.0 standard allows almost all Unicode characters even Chinese and Cyrillic.
                // see: http://en.wikipedia.org/wiki/XML#International_use
                preg_match('/<([^>]+)>/', $fromChunkPos, $matches);
                if (isset($matches[1])) {
                    // Found element
                    $element = $matches[1];

                    // Is there an end to this element tag?
                    $spacePos = strpos($element, ' ');
                    $crPos = strpos($element, "\r");
                    $lfPos = strpos($element, "\n");
                    $tabPos = strpos($element, "\t");

                    // find min. (exclude false, as it would convert to int 0)
                    $aPositionsIn = array($spacePos, $crPos, $lfPos, $tabPos);
                    $aPositions = array();
                    foreach ($aPositionsIn as $iPos) {
                        if (false !== $iPos) {
                            $aPositions[] = $iPos;
                        }
                    }

                    $minPos = $aPositions === array() ? false : min($aPositions);

                    if (false !== $minPos && 0 != $minPos) {
                        $sElementName = substr($element, 0, $minPos);
                        $endTag = '</'.$sElementName.'>';
                    } else {
                        $sElementName = $element;
                        $endTag = "</$sElementName>";
                    }

                    $endTagPos = false;

                    // try selfclosing first!
                    // NOTE: selfclosing is inside the element
                    $lastCharPos = strlen($element) - 1;
                    if ('/' == substr($element, $lastCharPos)) {
                        $endTag = '/>';
                        $endTagPos = $lastCharPos;

                        $iPos = strpos($fromChunkPos, '<');
                        if (false !== $iPos) {
                            // correct difference between $element and $fromChunkPos
                            // "+1" is for the missing '<' in $element
                            $endTagPos += $iPos + 1;
                        }
                    }

                    if (false === $endTagPos) {
                        $endTagPos = strpos($fromChunkPos, $endTag);
                    }

                    if (false !== $endTagPos) {
                        // Found end tag
                        $endTagEndPos = $endTagPos + strlen($endTag);
                        $elementWithChildren = trim(substr($fromChunkPos, 0, $endTagEndPos));

                        $continueParsing = $this->processNode($elementWithChildren, $sElementName, $this->nodeIndex++);
                        $this->chunk = substr($this->chunk, strpos($this->chunk, $endTag) + strlen($endTag));
                        $this->readFromChunkPos = 0;

                        if (isset($continueParsing) && false === $continueParsing) {
                            $this->chunkCompleted();
                            break 2;
                        }
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
            $this->chunkCompleted();
        }

        return isset($this->rootNode);
        fclose($this->handle);
    }

    private function readNextChunk()
    {
        $this->chunk .= fread($this->handle, $this->chunkSize);
        $this->readBytes += $this->chunkSize;
        if ($this->readBytes >= $this->totalBytes) {
            $this->readBytes = $this->totalBytes;

            return false;
        }

        return true;
    }
}
