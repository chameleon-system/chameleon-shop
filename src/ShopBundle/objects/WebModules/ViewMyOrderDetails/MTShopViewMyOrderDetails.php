<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use Symfony\Component\HttpFoundation\Request;

class MTShopViewMyOrderDetails extends MTPkgViewRendererAbstractModuleMapper
{
    public function Init()
    {
        parent::Init();

        $this->orderIdRequested = $this->getInputFilterUtil()->getFilteredInput('id', null);
    }

    /**
     * @return IPkgShopViewMyOrderDetails
     */
    public function myOrderDetailHandlerFactory()
    {
        return new TPkgShopViewMyOrderDetails($this->getDbAdapter(), $this->getSessionAdapter());
    }

    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        parent::GetRequirements($oRequirements);

        $oRequirements->NeedsSourceObject(
            'viewOrderDetailHandler',
            'IPkgShopViewMyOrderDetails',
            $this->myOrderDetailHandlerFactory()
        );
        $oRequirements->NeedsSourceObject('orderIdRequested', 'string', $this->getInputFilterUtil()->getFilteredInput('id', null), true);

        $oRequirements->NeedsSourceObject('extranetUserId', 'string', TdbDataExtranetUser::GetInstance()->id, true);
    }

    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapperVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ) {
        /** @var $viewOrderDetailHandler IPkgShopViewMyOrderDetails */
        $viewOrderDetailHandler = $oVisitor->GetSourceObject('viewOrderDetailHandler');
        $orderIdRequested = $oVisitor->GetSourceObject('orderIdRequested');
        $extranetUserId = $oVisitor->GetSourceObject('extranetUserId');

        $viewData = array(
            'error' => false,
            'errorCode' => null,
            'notMyOrderError' => false,
            'orderNotFoundError' => false,
            'order' => null,
        );

        if (false === $viewOrderDetailHandler->orderIdBelongsToUser($orderIdRequested, $extranetUserId)) {
            // this is not our order. access denied
            $viewData['error'] = true;
            $viewData['errorCode'] = 'notMyOrderError';
        }

        $order = $this->getDbAdapter()->getOrder($orderIdRequested);
        if (null === $order) {
            // this is not our order. access denied
            $viewData['error'] = true;
            $viewData['errorCode'] = 'orderNotFoundError';
        }

        $viewData['order'] = $order;

        $oVisitor->SetMappedValueFromArray($viewData);
    }

    /**
     * @return Request
     */
    private function getRequest()
    {
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();

        return $request;
    }

    /**
     * @return TPkgShopViewMyOrderDetailsSessionAdapter
     */
    private function getSessionAdapter()
    {
        $sessionAdapter = new TPkgShopViewMyOrderDetailsSessionAdapter($this->getRequest()->getSession());

        return $sessionAdapter;
    }

    /**
     * @return TPkgShopViewMyOrderDetailsDbAdapter
     */
    private function getDbAdapter()
    {
        $dbAdapter = new TPkgShopViewMyOrderDetailsDbAdapter(\ChameleonSystem\CoreBundle\ServiceLocator::get(
            'database_connection'
        ));

        return $dbAdapter;
    }

    /**
     * @return InputFilterUtilInterface
     */
    private function getInputFilterUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.input_filter');
    }
}
