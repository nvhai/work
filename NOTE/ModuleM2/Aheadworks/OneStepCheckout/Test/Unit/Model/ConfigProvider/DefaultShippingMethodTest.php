<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\ConfigProvider;

use Aheadworks\OneStepCheckout\Model\ConfigProvider\DefaultShippingMethod;
use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\ConfigProvider\DefaultShippingMethod
 */
class DefaultShippingMethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefaultShippingMethod
     */
    private $configProvider;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CarrierFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $carrierFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultShippingMethod'])
            ->getMock();
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->carrierFactoryMock = $this->getMockBuilder(CarrierFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->configProvider = $objectManager->getObject(
            DefaultShippingMethod::class,
            [
                'config' => $this->configMock,
                'scopeConfig' => $this->scopeConfigMock,
                'carrierFactory' => $this->carrierFactoryMock
            ]
        );
    }

    public function testGetShippingMethod()
    {
        $carrierCode = 'carrier';
        $methodCode = 'shipping_method';
        $carrierTitle = 'Carrier';
        $methodTitle = 'Shipping Method';

        $carrierMock = $this->getMockForAbstractClass(CarrierInterface::class);

        $this->configMock->expects($this->once())
            ->method('getDefaultShippingMethod')
            ->willReturn($carrierCode . '_' . $methodCode);
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['carriers', 'default', null, [$carrierCode => ['field' => 'value']]],
                    ['carriers/' . $carrierCode . '/title', ScopeInterface::SCOPE_STORE, null, $carrierTitle],
                ]
            );
        $this->carrierFactoryMock->expects($this->once())
            ->method('create')
            ->with($carrierCode)
            ->willReturn($carrierMock);
        $carrierMock->expects($this->once())
            ->method('getAllowedMethods')
            ->willReturn([$methodCode => $methodTitle]);

        $this->assertEquals(
            [
                ShippingMethodInterface::KEY_CARRIER_CODE => $carrierCode,
                ShippingMethodInterface::KEY_METHOD_CODE => $methodCode,
                ShippingMethodInterface::KEY_METHOD_TITLE => $methodTitle,
                ShippingMethodInterface::KEY_CARRIER_TITLE => $carrierTitle
            ],
            $this->configProvider->getShippingMethod()
        );
    }
}
