<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Tests\Service;

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Bridge\Service\RequestToListUrlConverter;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\RequestToListUrlConverterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class RequestToListUrlConverterTest extends TestCase
{
    use ProphecyTrait;

    private $referrer;
    /**
     * @var InputFilterUtilInterface
     */
    private $inputFilterUtil;
    /**
     * @var RequestToListUrlConverter
     */
    private $converter;
    private $listUrl;
    /**
     * @var array
     */
    private $requestParameter;
    private $spotName;
    /**
     * @var array
     */
    private $pagerParameters;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('TCMSUSERINPUT_DEFAULTFILTER')) {
            define('TCMSUSERINPUT_DEFAULTFILTER', 'TCMSUserInput_BaseText');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->referrer = null;
        $this->inputFilterUtil = null;
        $this->converter = null;
        $this->listUrl = null;
        $this->requestParameter = null;
        $this->spotName = null;
    }

    /**
     * @test
     */
    public function itShouldReturnTheListUrlFromParameter()
    {
        $this->given_the_referrer('myreferer');
        $this->given_the_request_parameters(['url' => 'listurl']);
        $this->given_an_input_filter_util();
        $this->given_an_instance_of_the_converter_with_the_current_request_stack();
        $this->when_we_call_getListUrl();
        $this->then_we_should_get_the_list_url('listurl');
    }

    /**
     * @test
     */
    public function itShouldReturnTheListSpotName()
    {
        $this->given_the_referrer('myreferer');
        $this->given_the_request_parameters(['_ref' => 'myspotname']);
        $this->given_an_input_filter_util();
        $this->given_an_instance_of_the_converter_with_the_current_request_stack();
        $this->when_we_call_getListSpotName();
        $this->then_we_should_get_the_spot_name('myspotname');
    }

    /**
     * @test
     *
     * @dataProvider dataProviderGetPagerQueryParameter
     */
    public function itShouldReturnThePagerRelevantQueryParameter($listSpotName, $listPageUrl, $expectedPagerParameter)
    {
        $this->given_the_list_spot($listSpotName);
        $this->given_the_request_parameters(['_ref' => $listSpotName]);
        $this->given_an_input_filter_util();
        $this->given_an_instance_of_the_converter_with_the_current_request_stack();
        $this->when_we_call_getPagerParameter_with($listPageUrl);
        $this->then_we_expect_to_get_pager_parameter_matching($expectedPagerParameter);
    }

    private function given_an_input_filter_util()
    {
        /** @var $inputFilterUtil InputFilterUtilInterface|ObjectProphecy */
        $inputFilterUtil = $this->prophesize('ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface');

        if (null !== $this->referrer) {
            $inputFilterUtil->getFilteredInput('HTTP_REFERER', null)->willReturn($this->referrer);
        }

        if (null !== $this->requestParameter) {
            foreach ($this->requestParameter as $key => $value) {
                $inputFilterUtil->getFilteredInput($key, null)->willReturn($value);
            }
        }
        $inputFilterUtil->getFilteredInput(Argument::any(), null)->willReturn(null);

        $this->inputFilterUtil = $inputFilterUtil->reveal();
    }

    private function given_the_referrer($referrer)
    {
        $this->referrer = $referrer;
    }

    private function given_an_instance_of_the_converter_with_the_current_request_stack()
    {
        $this->converter = new RequestToListUrlConverter($this->inputFilterUtil);
    }

    private function when_we_call_getListUrl()
    {
        $this->listUrl = $this->converter->getListUrl();
    }

    private function then_we_should_get_the_list_url($expectedListUrl)
    {
        $this->assertEquals($expectedListUrl, $this->listUrl);
    }

    private function given_the_request_parameters($requestParameter)
    {
        $this->requestParameter = $requestParameter;
    }

    private function when_we_call_getListSpotName()
    {
        $this->spotName = $this->converter->getListSpotName();
    }

    private function then_we_should_get_the_spot_name($expectedSpotName)
    {
        $this->assertEquals($expectedSpotName, $this->spotName);
    }

    public function dataProviderGetPagerQueryParameter()
    {
        return [
            [
                'listSpotName',
                'http://mydomain.tld/my/path?some=query#marker', // $listPageUrl
                [
                    RequestToListUrlConverterInterface::URL_PARAMETER_SPOT_NAME => 'listSpotName',
                    RequestToListUrlConverterInterface::URL_PARAMETER_LIST_URL => 'http://mydomain.tld/my/path?some=query#marker',
                ], // $expectedPagerParameter
            ],
        ];
    }

    private function when_we_call_getPagerParameter_with($listPageUrl)
    {
        $this->pagerParameters = $this->converter->getPagerParameter($this->spotName, $listPageUrl);
    }

    private function then_we_expect_to_get_pager_parameter_matching($expectedPagerParameter)
    {
        $this->assertEquals($expectedPagerParameter, $this->pagerParameters);
    }

    private function given_the_list_spot($listSpotName)
    {
        $this->spotName = $listSpotName;
    }
}
