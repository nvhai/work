<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\DeliveryDate;

use Aheadworks\OneStepCheckout\Model\DeliveryDate\ConfigProvider;
use Aheadworks\OneStepCheckout\Model\Config;
use Aheadworks\OneStepCheckout\Model\Config\Source\DeliveryDate\DisplayOption;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\DeliveryDate\ConfigProvider
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDeliveryDateDisplayOption',
                'getDeliveryDateAvailableWeekdays',
                'getNonDeliveryPeriods',
                'getMinOrderDeliveryPeriod'
            ])
            ->getMock();
        $this->configProvider = $objectManager->getObject(
            ConfigProvider::class,
            ['config' => $this->configMock]
        );
    }

    public function testGetConfig()
    {
        $displayOption = DisplayOption::DATE;
        $isEnabled = true;
        $weekDays = [1, 2, 3, 4, 5];
        $nonDeliveryPeriods = [
            [
                'period_type' => 'single_day',
                'period' => ['from_date' => '07/01/2017']
            ]
        ];
        $minDeliveryPeriod = 3;

        $this->configMock->expects($this->once())
            ->method('getDeliveryDateDisplayOption')
            ->willReturn($displayOption);
        $this->configMock->expects($this->once())
            ->method('getDeliveryDateAvailableWeekdays')
            ->willReturn($weekDays);
        $this->configMock->expects($this->once())
            ->method('getNonDeliveryPeriods')
            ->willReturn($nonDeliveryPeriods);
        $this->configMock->expects($this->once())
            ->method('getMinOrderDeliveryPeriod')
            ->willReturn($minDeliveryPeriod);

        $this->assertEquals(
            [
                'isEnabled' => $isEnabled,
                'dateRestrictions' => [
                    'weekdays' => $weekDays,
                    'nonDeliveryPeriods' => $nonDeliveryPeriods,
                    'minOrderDeliveryPeriod' => $minDeliveryPeriod
                ]
            ],
            $this->configProvider->getConfig()
        );
    }
}
