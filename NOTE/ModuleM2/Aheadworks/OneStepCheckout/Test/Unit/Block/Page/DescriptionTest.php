<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Block\Page;

use Aheadworks\OneStepCheckout\Block\Page\Description;
use Aheadworks\OneStepCheckout\Model\Config;
use Aheadworks\OneStepCheckout\Model\Template\FilterProvider;
use Magento\Cms\Model\Template\Filter as TemplateFilter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\OneStepCheckout\Block\Page\Description
 */
class DescriptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Description
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var FilterProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterProviderMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCheckoutDescription'])
            ->getMock();
        $this->filterProviderMock = $this->getMockBuilder(FilterProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilter'])
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            ['storeManager' => $this->storeManagerMock]
        );
        $this->block = $objectManager->getObject(
            Description::class,
            [
                'context' => $context,
                'config' => $this->configMock,
                'filterProvider' => $this->filterProviderMock
            ]
        );
    }

    public function testGetDescriptionHtml()
    {
        $storeId = 1;
        $checkoutDescription = 'Checkout description {{var data}}';
        $filteredCheckoutDescription = 'Checkout description data_value';

        $filterMock = $this->getMockBuilder(TemplateFilter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->filterProviderMock->expects($this->once())
            ->method('getFilter')
            ->willReturn($filterMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $filterMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->configMock->expects($this->once())
            ->method('getCheckoutDescription')
            ->willReturn($checkoutDescription);
        $filterMock->expects($this->once())
            ->method('filter')
            ->with($checkoutDescription)
            ->willReturn($filteredCheckoutDescription);

        $this->assertEquals($filteredCheckoutDescription, $this->block->getDescriptionHtml());
    }
}
