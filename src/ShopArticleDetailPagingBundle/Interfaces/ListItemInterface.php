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
    public function getUrl();

    public function getName();

    public function getId();

    public function setUrl($url);

    public function setName($name);

    public function setId($id);
}
