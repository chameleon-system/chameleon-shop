<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces;

interface ListItemInterface
{
    /**
     * @return string|null
     */
    public function getUrl();

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return string|null
     */
    public function getId();

    /**
     * @param string $url
     *
     * @return void
     */
    public function setUrl($url);

    /**
     * @param null $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId($id);
}
