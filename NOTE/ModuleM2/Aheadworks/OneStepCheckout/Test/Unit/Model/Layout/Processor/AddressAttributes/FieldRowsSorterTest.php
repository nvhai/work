<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Layout\Processor\AddressAttributes;

use Aheadworks\OneStepCheckout\Model\Address\Form\FieldRow\DefaultSortOrder;
use Aheadworks\OneStepCheckout\Model\Config;
use Aheadworks\OneStepCheckout\Model\Layout\Processor\AddressAttributes\FieldRowsSorter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Layout\Processor\AddressAttributes\FieldRowsSorter
 */
class FieldRowsSorterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FieldRowsSorter
     */
    private $sorter;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var DefaultSortOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultSortOrderMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAddressFormConfig'])
            ->getMock();
        $this->defaultSortOrderMock = $this->getMockBuilder(DefaultSortOrder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSortOrder'])
            ->getMock();
        $this->sorter = $objectManager->getObject(
            FieldRowsSorter::class,
            [
                'config' => $this->configMock,
                'defaultSortOrder' => $this->defaultSortOrderMock
            ]
        );
    }

    public function testSortDefaultValues()
    {
        $rowId = 'row1';
        $addressType = 'shipping';
        $sortOrder = 10;

        $this->configMock->expects($this->once())
            ->method('getAddressFormConfig')
            ->with($addressType)
            ->willReturn([]);
        $this->defaultSortOrderMock->expects($this->once())
            ->method('getSortOrder')
            ->with($rowId)
            ->willReturn($sortOrder);

        $this->assertEquals(
            [
                $rowId => [
                    'component' => 'uiComponent',
                    'config' => [],
                    'children' => [],
                    'sortOrder' => $sortOrder
                ]
            ],
            $this->sorter->sort(
                [
                    $rowId => [
                        'component' => 'uiComponent',
                        'config' => [],
                        'children' => [],
                        'sortOrder' => 2
                    ]
                ],
                $addressType
            )
        );
    }

    public function testSortConfigValues()
    {
        $rowId = 'row1';
        $addressType = 'shipping';
        $sortOrder = 10;

        $this->configMock->expects($this->once())
            ->method('getAddressFormConfig')
            ->with($addressType)
            ->willReturn(
                [
                    'rows' => [
                        $rowId => ['sort_order' => $sortOrder]
                    ]
                ]
            );
        $this->defaultSortOrderMock->expects($this->never())
            ->method('getSortOrder');

        $this->assertEquals(
            [
                $rowId => [
                    'component' => 'uiComponent',
                    'config' => [],
                    'children' => [],
                    'sortOrder' => $sortOrder
                ]
            ],
            $this->sorter->sort(
                [
                    $rowId => [
                        'component' => 'uiComponent',
                        'config' => [],
                        'children' => [],
                        'sortOrder' => 2
                    ]
                ],
                $addressType
            )
        );
    }
}
