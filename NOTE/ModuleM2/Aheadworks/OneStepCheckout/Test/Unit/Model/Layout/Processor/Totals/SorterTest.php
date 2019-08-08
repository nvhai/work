<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Layout\Processor\Totals;

use Aheadworks\OneStepCheckout\Model\Layout\Processor\Totals\Sorter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Layout\Processor\Totals\Sorter
 */
class SorterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Sorter
     */
    private $sorter;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->sorter = $objectManager->getObject(
            Sorter::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    public function testSort()
    {
        $totalCode = 'total';
        $sortOrder = 5;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/totals_sort')
            ->willReturn([$totalCode => $sortOrder]);

        $this->assertEquals(
            [
                $totalCode => [
                    'component' => 'TotalComponent',
                    'config' => [],
                    'sortOrder' => $sortOrder
                ]
            ],
            $this->sorter->sort(
                [
                    $totalCode => [
                        'component' => 'TotalComponent',
                        'config' => []
                    ]
                ]
            )
        );
    }
}
