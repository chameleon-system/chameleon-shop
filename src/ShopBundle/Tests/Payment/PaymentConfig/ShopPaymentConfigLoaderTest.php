<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Tests\Payment\PaymentConfig;

use ChameleonSystem\ShopBundle\Exception\ConfigurationException;
use ChameleonSystem\ShopBundle\Exception\DataAccessException;
use ChameleonSystem\ShopBundle\Payment\DataModel\OrderPaymentInfo;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\ShopPaymentConfigLoader;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\ShopPaymentConfigRawValue;
use PHPUnit\Framework\TestCase;

class ShopPaymentConfigLoaderTest extends TestCase
{
    private $shopPaymentConfigLoaderDataAccess;
    private $shopPaymentConfigProvider;

    private $expectedConfig;

    /**
     * @test
     */
    public function it_loads_empty_values_on_empty_configuration_in_loadFromPaymentHandlerId()
    {
        $this->shopPaymentConfigLoaderDataAccess->getEnvironment('groupId')->willReturn(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $this->shopPaymentConfigLoaderDataAccess->loadPaymentHandlerGroupConfig('groupId')->willReturn(array());
        $this->shopPaymentConfigLoaderDataAccess->loadPaymentHandlerConfig('handlerId')->willReturn(array());

        $expected = array();

        $loader = $this->getShopPaymentConfigLoader(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $config = $loader->loadFromPaymentHandlerId('handlerId', 'portalId1');
        $allValues = $config->getAllValues();

        $this->assertEquals($expected, $allValues);
    }

    /**
     * @test
     */
    public function it_loads_correct_sandbox_configuration_in_loadFromOrderId()
    {
        $this->shopPaymentConfigLoaderDataAccess->getEnvironment('groupId')->willReturn(
            \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX
        );
        $this->shopPaymentConfigLoaderDataAccess->getDataFromOrderId('orderId')->willReturn(new OrderPaymentInfo('orderId', 'handlerId', 'portalId1'));
        $this->initFullConfig();
        $this->initFullExpectedValue();

        $loader = $this->getShopPaymentConfigLoader(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $loader->addConfigProvider('groupSystemName', $this->shopPaymentConfigProvider->reveal());

        $config = $loader->loadFromOrderId('orderId');
        $allValues = $config->getAllValues();
        ksort($allValues);

        $this->assertEquals($this->expectedConfig, $allValues);
    }

    /**
     * @test
     */
    public function it_loads_correct_sandbox_configuration_in_loadFromPaymentHandlerId()
    {
        $this->shopPaymentConfigLoaderDataAccess->getEnvironment('groupId')->willReturn(
            \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX
        );
        $this->initFullConfig();
        $this->initFullExpectedValue();

        $loader = $this->getShopPaymentConfigLoader(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $loader->addConfigProvider('groupSystemName', $this->shopPaymentConfigProvider->reveal());

        $config = $loader->loadFromPaymentHandlerId('handlerId', 'portalId1');
        $allValues = $config->getAllValues();
        ksort($allValues);

        $this->assertEquals($this->expectedConfig, $allValues);
    }

    /**
     * @test
     */
    public function it_loads_correct_sandbox_configuration_with_default_environment_in_loadFromPaymentHandlerId()
    {
        $this->shopPaymentConfigLoaderDataAccess->getEnvironment('groupId')->willReturn('default');
        $this->initFullConfig();
        $this->initFullExpectedValue();

        $loader = $this->getShopPaymentConfigLoader(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $loader->addConfigProvider('groupSystemName', $this->shopPaymentConfigProvider->reveal());

        $config = $loader->loadFromPaymentHandlerId('handlerId', 'portalId1');
        $allValues = $config->getAllValues();
        ksort($allValues);

        $this->assertEquals($this->expectedConfig, $allValues);
    }

    /**
     * @test
     */
    public function it_loads_correct_sandbox_configuration_in_loadFromPaymentHandlerGroupId()
    {
        $this->shopPaymentConfigLoaderDataAccess->getEnvironment('groupId')->willReturn(
            \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX
        );
        $this->initFullConfig(false);
        $this->initPaymentHandlerGroupOnlyExpectedValue();

        $loader = $this->getShopPaymentConfigLoader(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $loader->addConfigProvider('groupSystemName', $this->shopPaymentConfigProvider->reveal());

        $config = $loader->loadFromPaymentHandlerGroupId('groupId', 'portalId1');
        $allValues = $config->getAllValues();
        ksort($allValues);

        $this->assertEquals($this->expectedConfig, $allValues);
    }

    /**
     * @test
     */
    public function it_loads_correct_sandbox_configuration_in_loadFromPaymentHandlerGroupSystemName()
    {
        $this->shopPaymentConfigLoaderDataAccess->getEnvironment('groupId')->willReturn(
            \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX
        );
        $this->shopPaymentConfigLoaderDataAccess->getPaymentHandlerGroupIdFromSystemName('groupSystemName')->willReturn('groupId');
        $this->initFullConfig(false);
        $this->initPaymentHandlerGroupOnlyExpectedValue();

        $loader = $this->getShopPaymentConfigLoader(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $loader->addConfigProvider('groupSystemName', $this->shopPaymentConfigProvider->reveal());

        $config = $loader->loadFromPaymentHandlerGroupSystemName('groupSystemName', 'portalId1');
        $allValues = $config->getAllValues();
        ksort($allValues);

        $this->assertEquals($this->expectedConfig, $allValues);
    }

    /**
     * @test
     */
    public function it_throws_configuration_exception_on_error()
    {
        $this->shopPaymentConfigLoaderDataAccess->getEnvironment('groupId')->willReturn(
            \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX
        );
        $this->shopPaymentConfigLoaderDataAccess->getPaymentHandlerGroupIdFromSystemName('groupSystemName')->willThrow(new DataAccessException('This is a test exception.'));

        $loader = $this->getShopPaymentConfigLoader(\IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX);
        $loader->addConfigProvider('groupSystemName', $this->shopPaymentConfigProvider->reveal());

        try {
            $config = $loader->loadFromPaymentHandlerGroupSystemName('groupSystemName', 'portalId1');
            $this->fail();
        } catch (ConfigurationException $e) {
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail();
        }
    }

    private function initFullConfig($includeHandlerOnlyValues = true)
    {
        $groupConfig = array();
        $additionalConfig = array();
        $handlerConfig = array();

        /*
         * values that are only present in the group (no portal restriction)
         */

        $groupConfig[] = new ShopPaymentConfigRawValue('name01', 'groupValue01common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);

        $groupConfig[] = new ShopPaymentConfigRawValue('name02', 'groupValue02common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name02', 'groupValue02sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_GROUP);

        $groupConfig[] = new ShopPaymentConfigRawValue('name03', 'groupValue03common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name03', 'groupValue03live', \IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION, '', ShopPaymentConfigRawValue::SOURCE_GROUP);

        $groupConfig[] = new ShopPaymentConfigRawValue('name04', 'groupValue03live', \IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION, '', ShopPaymentConfigRawValue::SOURCE_GROUP);

        /*
         * values that are present in the group and in the handler (no portal restriction)
         */

        $groupConfig[] = new ShopPaymentConfigRawValue('name11', 'groupValue11common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name11', 'handlerValue11common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name12', 'groupValue12common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name12', 'groupValue12sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name12', 'handlerValue12common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name13', 'groupValue13common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name13', 'groupValue13live', \IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name13', 'handlerValue13common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        if ($includeHandlerOnlyValues) {
            /*
             * values that are only present in the handler (no portal restriction)
             */

            $groupConfig[] = new ShopPaymentConfigRawValue('name21', 'handlerValue21common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);

            $groupConfig[] = new ShopPaymentConfigRawValue('name22', 'handlerValue22common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
            $groupConfig[] = new ShopPaymentConfigRawValue('name22', 'handlerValue22sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_GROUP);

            $groupConfig[] = new ShopPaymentConfigRawValue('name23', 'handlerValue23common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
            $groupConfig[] = new ShopPaymentConfigRawValue('name23', 'handlerValue23live', \IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        }

        /*
         * values that are only present in the additional config (no portal restriction)
         */

        $additionalConfig[] = new ShopPaymentConfigRawValue('name31', 'additionalValue31common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        $additionalConfig[] = new ShopPaymentConfigRawValue('name32', 'additionalValue32common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name32', 'additionalValue32sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        $additionalConfig[] = new ShopPaymentConfigRawValue('name33', 'additionalValue33common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name33', 'additionalValue33live', \IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        /*
         * values that are present in group and additional config (no portal restriction)
         */

        $groupConfig[] = new ShopPaymentConfigRawValue('name41', 'groupValue41common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name41', 'additionalValue41common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        $groupConfig[] = new ShopPaymentConfigRawValue('name42', 'groupValue42sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name42', 'additionalValue42common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        $groupConfig[] = new ShopPaymentConfigRawValue('name43', 'groupValue43common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name43', 'additionalValue43sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        /*
         * values that are present in handler and additional config (no portal restriction)
         */

        $additionalConfig[] = new ShopPaymentConfigRawValue('name51', 'additionalValue51common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name51', 'handlerValue51common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $additionalConfig[] = new ShopPaymentConfigRawValue('name52', 'additionalValue52common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name52', 'handlerValue52sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $additionalConfig[] = new ShopPaymentConfigRawValue('name53', 'additionalValue53sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name53', 'handlerValue53common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        /*
         * values that are present in all configs (no portal restriction)
         */

        $groupConfig[] = new ShopPaymentConfigRawValue('name61', 'groupValue61common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name61', 'additionalValue61common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name61', 'handlerValue61common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name62', 'groupValue62common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name62', 'additionalValue62sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name62', 'handlerValue62common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name63', 'groupValue63common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name63', 'additionalValue63common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name63', 'handlerValue63common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name64', 'groupValue64common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name64', 'additionalValue64common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name64', 'handlerValue64sandbox', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name65', 'groupValue65common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name65', 'additionalValue65common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name65', 'handlerValue65live', \IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        /*
         * values that are only present in the group (with portal restriction)
         */

        $groupConfig[] = new ShopPaymentConfigRawValue('name71', 'groupValue71common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name71', 'groupValue71common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);

        $groupConfig[] = new ShopPaymentConfigRawValue('name72', 'groupValue72common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name72', 'groupValue72sandbox_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);

        $groupConfig[] = new ShopPaymentConfigRawValue('name73', 'groupValue73common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name73', 'groupValue73live', \IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);

        $groupConfig[] = new ShopPaymentConfigRawValue('name74', 'groupValue74common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $groupConfig[] = new ShopPaymentConfigRawValue('name74', 'groupValue74sandbox_otherportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, 'portalId2', ShopPaymentConfigRawValue::SOURCE_GROUP);

        /*
         * misc combinations (with portal restriction)
         */

        $groupConfig[] = new ShopPaymentConfigRawValue('name81', 'groupValue81common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name81', 'additionalValue81common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        $groupConfig[] = new ShopPaymentConfigRawValue('name82', 'groupValue82common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name82', 'handlerValue82common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $additionalConfig[] = new ShopPaymentConfigRawValue('name83', 'additionalValue83common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name83', 'handlerValue83common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name84', 'groupValue84sandbox_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name84', 'handlerValue84common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name85', 'groupValue85sandbox_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name85', 'handlerValue85common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name86', 'groupValue86sandbox_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $handlerConfig[] = new ShopPaymentConfigRawValue('name86', 'handlerValue86common', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, '', ShopPaymentConfigRawValue::SOURCE_HANDLER);

        $groupConfig[] = new ShopPaymentConfigRawValue('name87', 'groupValue87common_sameportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId1', ShopPaymentConfigRawValue::SOURCE_GROUP);
        $additionalConfig[] = new ShopPaymentConfigRawValue('name87', 'additionalValue87common_otherportal', \IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON, 'portalId2', ShopPaymentConfigRawValue::SOURCE_ADDITIONAL);

        $this->shopPaymentConfigLoaderDataAccess->loadPaymentHandlerGroupConfig('groupId')->willReturn($groupConfig);
        $this->shopPaymentConfigLoaderDataAccess->loadPaymentHandlerConfig('handlerId')->willReturn($handlerConfig);
        $this->shopPaymentConfigProvider->getAdditionalConfiguration()->willReturn($additionalConfig);
    }

    private function initFullExpectedValue()
    {
        $this->expectedConfig = array(
            'name01' => 'groupValue01common',
            'name02' => 'groupValue02sandbox',
            'name03' => 'groupValue03common',
            'name11' => 'handlerValue11common',
            'name12' => 'handlerValue12common',
            'name13' => 'handlerValue13common',
            'name21' => 'handlerValue21common',
            'name22' => 'handlerValue22sandbox',
            'name23' => 'handlerValue23common',
            'name31' => 'additionalValue31common',
            'name32' => 'additionalValue32sandbox',
            'name33' => 'additionalValue33common',
            'name41' => 'additionalValue41common',
            'name42' => 'additionalValue42common',
            'name43' => 'additionalValue43sandbox',
            'name51' => 'handlerValue51common',
            'name52' => 'handlerValue52sandbox',
            'name53' => 'handlerValue53common',
            'name61' => 'handlerValue61common',
            'name62' => 'handlerValue62common',
            'name63' => 'handlerValue63common',
            'name64' => 'handlerValue64sandbox',
            'name65' => 'additionalValue65common',
            'name71' => 'groupValue71common_sameportal',
            'name72' => 'groupValue72sandbox_sameportal',
            'name73' => 'groupValue73common',
            'name74' => 'groupValue74common',
            'name81' => 'additionalValue81common_sameportal',
            'name82' => 'handlerValue82common_sameportal',
            'name83' => 'handlerValue83common_sameportal',
            'name84' => 'handlerValue84common_sameportal',
            'name85' => 'handlerValue85common',
            'name86' => 'handlerValue86common',
            'name87' => 'groupValue87common_sameportal',
        );
    }

    private function initPaymentHandlerGroupOnlyExpectedValue()
    {
        $this->expectedConfig = array(
            'name01' => 'groupValue01common',
            'name02' => 'groupValue02sandbox',
            'name03' => 'groupValue03common',
            'name11' => 'groupValue11common',
            'name12' => 'groupValue12sandbox',
            'name13' => 'groupValue13common',
            'name31' => 'additionalValue31common',
            'name32' => 'additionalValue32sandbox',
            'name33' => 'additionalValue33common',
            'name41' => 'additionalValue41common',
            'name42' => 'additionalValue42common',
            'name43' => 'additionalValue43sandbox',
            'name51' => 'additionalValue51common',
            'name52' => 'additionalValue52common',
            'name53' => 'additionalValue53sandbox',
            'name61' => 'additionalValue61common',
            'name62' => 'additionalValue62sandbox',
            'name63' => 'additionalValue63common',
            'name64' => 'additionalValue64common',
            'name65' => 'additionalValue65common',
            'name71' => 'groupValue71common_sameportal',
            'name72' => 'groupValue72sandbox_sameportal',
            'name73' => 'groupValue73common',
            'name74' => 'groupValue74common',
            'name81' => 'additionalValue81common_sameportal',
            'name82' => 'groupValue82common_sameportal',
            'name83' => 'additionalValue83common',
            'name84' => 'groupValue84sandbox_sameportal',
            'name85' => 'groupValue85sandbox_sameportal',
            'name86' => 'groupValue86sandbox_sameportal',
            'name87' => 'groupValue87common_sameportal',
        );
    }

    private function getShopPaymentConfigLoader($environment)
    {
        $shopPaymentHandlerGroupPersistenceMock = null;
        $shopPaymentHandlerParameterPersistenceMock = null;
        $loader = new ShopPaymentConfigLoader($this->shopPaymentConfigLoaderDataAccess->reveal(), $environment);

        return $loader;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->shopPaymentConfigLoaderDataAccess = $this->prophesize('ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderDataAccessInterface');
        $this->shopPaymentConfigLoaderDataAccess->getPaymentHandlerGroupIdFromPaymentHandlerId('handlerId')->willReturn('groupId');
        $this->shopPaymentConfigLoaderDataAccess->getPaymentHandlerGroupSystemNameFromId('groupId')->willReturn('groupSystemName');

        $this->shopPaymentConfigProvider = $this->prophesize('ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigProviderInterface');
    }
}
