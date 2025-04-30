<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentHandlerMapper_IPaymentCreditCard extends AbstractPkgShopPaymentHandlerMapper_iPayment
{
    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ) {
        parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);

        /** @var $oPaymentHandler TShopPaymentHandlerIPaymentCreditCard */
        $oPaymentHandler = $oVisitor->GetSourceObject('oPaymentHandler');
        $cards = $oPaymentHandler->GetConfigParameter('cards');

        // {'sValue':'AmexCard','sName':'AmericanExpress'},{'sValue':'Mastercard','sName':'Mastercard'},{'sValue':'VisaCard','sName':'Visa'}
        $allCards = [
            'MasterCard' => 'MasterCard',
            'VisaCard' => 'Visa',
            'AmexCard' => 'Amex',
            'DinersClubCard' => 'Diners Club Card',
            'JCBCard' => 'JCB Card',
            'SoloCard' => 'Solo Card',
            'DiscoverCard' => 'Discover Card',
            'MaestroCard' => 'Maestro Card',
        ];
        $lookup = [];
        foreach ($allCards as $card => $cardName) {
            $lookup[strtolower($card)] = $card;
        }
        $cardList = [];
        if (false === $cards) {
            $cardList = ['Mastercard', 'VisaCard'];
        } else {
            $tmpList = explode(',', $cards);
            foreach ($tmpList as $card) {
                $card = trim($card);
                if ('' === $card) {
                    continue;
                }
                $cardList[] = $card;
            }
        }

        $oVisitor->SetMappedValue('amexEnabled', false);

        $useCards = [];
        foreach ($cardList as $cardLookup) {
            $cardLookup = strtolower($cardLookup);
            if (false === isset($lookup[$cardLookup])) {
                continue;
            }
            $useCards[] = ['sValue' => $lookup[$cardLookup], 'sName' => $allCards[$lookup[$cardLookup]]];
            if ('amexcard' === $cardLookup) {
                $oVisitor->SetMappedValue('amexEnabled', true);
            }
        }
        $oVisitor->SetMappedValue('cardList', $useCards);
    }
}
