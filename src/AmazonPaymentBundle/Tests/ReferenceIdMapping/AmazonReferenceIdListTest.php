<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\ReferenceIdMapping;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdList;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;

class AmazonReferenceIdListTest extends AbstractAmazonPayment
{
    public function test_getLast()
    {
        $itemList = array();
        $itemList[] = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, 'LOCAL-'.count($itemList), 10, null);
        $itemList[] = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, 'LOCAL-'.count($itemList), 10, null);
        $itemList[] = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, 'LOCAL-'.count($itemList), 10, null);
        $itemList[] = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, 'LOCAL-'.count($itemList), 10, null);

        $list = new AmazonReferenceIdList('ref', IAmazonReferenceId::TYPE_AUTHORIZE);

        foreach ($itemList as $item) {
            $list->addItem($item);
        }

        $this->assertEquals($itemList[count($itemList) - 1], $list->getLast());
    }
}
