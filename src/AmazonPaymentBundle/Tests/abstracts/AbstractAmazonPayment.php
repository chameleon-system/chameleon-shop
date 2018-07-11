<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\abstracts;

use ChameleonSystem\AmazonPaymentBundle\AmazonDataConverter;
use ChameleonSystem\CoreBundle\ServiceLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;

require_once __DIR__.'/../fixtures/AmazonPaymentFixturesFactory.php';
abstract class AbstractAmazonPayment extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $config = null;
    protected static $dbal = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('../config.yml');

        $containerBuilder->addCompilerPass(new \TPkgDependencyInjection_CompileConstantsPass());
        $containerBuilder->compile();

        ServiceLocator::setContainer($containerBuilder);

        $chameleon = new \chameleon();
        $chameleon->setRequestType($chameleon::REQUEST_TYPE_UNITTEST);
        $chameleon->run();
        self::$dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => ServiceLocator::get('testpdo'), 'charset' => 'UTF8'));
    }

    /**
     * @param \TdbShopOrder $order
     *
     * @return \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTransactionManager(\TdbShopOrder $order)
    {
        return $this->getMockBuilder('TPkgShopPaymentTransactionManager')->setConstructorArgs(array($order))->getMock();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();

        $sessionMock = $this->getMockBuilder('TPKgCmsSession')->disableOriginalConstructor()->getMock();
        $sessionMock->expects($this->any())->method('isStarted')->will($this->returnValue(true));

        $request = ServiceLocator::get('request_stack')->getCurrentRequest();
        $request->setSession($sessionMock);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->config = null;
        $request = ServiceLocator::get('request_stack')->getCurrentRequest();
        $request->setSession(null);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AmazonPaymentGroupConfig
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array                                    $amazonShippingAddress
     * @param \PHPUnit_Framework_MockObject_MockObject $api
     */
    protected function helperAddGetOrderReferenceDetailsDestination($amazonShippingAddress, $api = null)
    {
        $details = $this->helperGetOrderReferenceDetail($amazonShippingAddress);

        $result = new \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResult();
        $result->setOrderReferenceDetails($details);

        $response = new \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse();
        $response->setGetOrderReferenceDetailsResult($result);

        if (null === $api) {
            $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        }
        $api->expects($this->any())->method('getOrderReferenceDetails')->will($this->returnValue($response));

        $this->getConfig()->expects($this->any())->method('getAmazonAPI')->will($this->returnValue($api));
    }

    protected function helperGetOrderReferenceDetail($amazonShippingAddress)
    {
        $address = new \OffAmazonPaymentsService_Model_Address($amazonShippingAddress);
        $destination = new \OffAmazonPaymentsService_Model_Destination();
        $destination->setPhysicalDestination($address);

        $responseData = array(
            'AmazonOrderReferenceId' => '123',
            'Destination' => $destination,
        );
        $details = new \OffAmazonPaymentsService_Model_OrderReferenceDetails();
        $details->setAmazonOrderReferenceId($responseData['AmazonOrderReferenceId']);
        $details->setDestination($destination);

        return $details;
    }

    /**
     * @param array $amazonShippingAddress
     */
    protected function helperAddGetOrderReferenceDetailsNoShippingAddressConstraint($amazonShippingAddress, $constraintID = 'ShippingAddressNotSet')
    {
        $address = new \OffAmazonPaymentsService_Model_Address($amazonShippingAddress);
        $destination = new \OffAmazonPaymentsService_Model_Destination();
        $destination->setPhysicalDestination($address);

        $responseData = array(
            'AmazonOrderReferenceId' => '123',
            'Destination' => $destination,
        );

        $constraints = new \OffAmazonPaymentsService_Model_Constraints();

        $constraint = new \OffAmazonPaymentsService_Model_Constraint(array(
            'ConstraintID' => $constraintID,
            'Description' => 'description',
        ));
        $constraints->withConstraint($constraint);

        $details = new \OffAmazonPaymentsService_Model_OrderReferenceDetails();
        $details->setAmazonOrderReferenceId($responseData['AmazonOrderReferenceId']);
        $details->setDestination($destination);
        $details->setConstraints($constraints);

        $result = new \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResult();
        $result->setOrderReferenceDetails($details);

        $response = new \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse();
        $response->setGetOrderReferenceDetailsResult($result);

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        $api->expects($this->any())->method('getOrderReferenceDetails')->will($this->returnValue($response));

        $this->getConfig()->expects($this->any())->method('getAmazonAPI')->will($this->returnValue($api));
    }

    /**
     * causes the getOrderReferenceDetails method of the amazon api to throw an amazon api exception with the error code passed.
     *
     * @param string $exceptionErrorCode
     */
    protected function helperAddGetOrderReferenceDetailsAPIError($exceptionErrorCode = 'InternalServerError')
    {
        $exception = $this->helperGetAmazonApiException($exceptionErrorCode);

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        $api->expects($this->once())->method('getOrderReferenceDetails')->will($this->throwException($exception));

        $this->getConfig()->expects($this->any())->method('getAmazonAPI')->will($this->returnValue($api));
    }

    /**
     * @param $address
     * @param $countryId
     *
     * @return array
     */
    protected function convertAmazonToLocalAddress($address, $countryId)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AmazonDataConverter $amazonConverter */
        $amazonConverter = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonDataConverter')->setMethods(array('getCountryIdFromAmazonCountryCode'))->getMock();
        if (null !== $countryId) {
            $amazonConverter->expects($this->any())->method('getCountryIdFromAmazonCountryCode')->will($this->returnValue($countryId));
        } else {
            $amazonConverter->expects($this->any())->method('getCountryIdFromAmazonCountryCode')->will($this->throwException(new \InvalidArgumentException()));
        }

        return $amazonConverter->convertAddressFromAmazonToLocal($address, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);
    }

    /**
     * @param string $exceptionErrorCode
     *
     * @return \OffAmazonPaymentsService_Exception
     */
    protected function helperGetAmazonApiException($exceptionErrorCode = 'InternalServerError')
    {
        $exceptionData = array(
            'Message' => 'There was an unknown error in the service',
            'StatusCode' => '123',
            'ErrorCode' => $exceptionErrorCode,
            'ErrorType' => 'Unknown',
            'RequestId' => '123',
            'XML' => '',
            'ResponseHeaderMetadata' => '',
        );
        $exception = new \OffAmazonPaymentsService_Exception($exceptionData);

        return $exception;
    }
}
