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
    /**
     * @var string
     */
    private $orderIdRequested;

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

    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
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
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        /** @var $viewOrderDetailHandler IPkgShopViewMyOrderDetails */
        $viewOrderDetailHandler = $oVisitor->GetSourceObject('viewOrderDetailHandler');
        $orderIdRequested = $oVisitor->GetSourceObject('orderIdRequested');
        $extranetUserId = $oVisitor->GetSourceObject('extranetUserId');

        $order = $this->getDbAdapter()->getOrder($orderIdRequested);

        $viewData = [
            'error' => false,
            'errorCode' => null,
            'notMyOrderError' => false,
            'orderNotFoundError' => false,
            'order' => $order,
        ];

        if (null === $order) {
            $viewData['error'] = true;
            $viewData['errorCode'] = 'orderNotFoundError';
        } elseif (false === $viewOrderDetailHandler->orderIdBelongsToUser($orderIdRequested, $extranetUserId)) {
            $viewData['error'] = true;
            $viewData['errorCode'] = 'notMyOrderError';
        }

        $oVisitor->SetMappedValueFromArray($viewData);
    }

    /**
     * @return Request
     */
    private function getRequest()
    {
        /** @var Request $request */
        $request = ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();

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
     * @return IPkgShopViewMyOrderDetailsDbAdapter
     */
    private function getDbAdapter()
    {
        $dbAdapter = new TPkgShopViewMyOrderDetailsDbAdapter(ChameleonSystem\CoreBundle\ServiceLocator::get(
            'database_connection'
        ));

        return $dbAdapter;
    }

    /**
     * @return InputFilterUtilInterface
     */
    private function getInputFilterUtil()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.input_filter');
    }
}
