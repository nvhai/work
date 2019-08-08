<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Block;

use Aheadworks\OneStepCheckout\Block\Checkout;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Block\Checkout
 */
class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Checkout
     */
    private $block;

    /**
     * @var FormKey|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyMock;

    /**
     * @var CompositeConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configProviderMock;

    /**
     * @var LayoutProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutProcessorMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->formKeyMock = $this->getMockBuilder(FormKey::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFormKey'])
            ->getMock();
        $this->configProviderMock = $this->getMockBuilder(CompositeConfigProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfig'])
            ->getMock();
        $this->layoutProcessorMock = $this->getMockForAbstractClass(LayoutProcessorInterface::class);
        $this->block = $objectManager->getObject(
            Checkout::class,
            [
                'formKey' => $this->formKeyMock,
                'configProvider' => $this->configProviderMock,
                'layoutProcessors' => [$this->layoutProcessorMock]
            ]
        );
    }

    public function testGetJsLayout()
    {
        $jsLayout = [
            'components' => [
                'checkout' => ['children' => ['component1' => []]]
            ]
        ];
        $modifiedJsLayout = [
            'components' => [
                'checkout' => ['children' => ['component1' => [], 'component2' => []]]
            ]
        ];

        $class = new \ReflectionClass($this->block);

        $jsLayoutProperty = $class->getProperty('jsLayout');
        $jsLayoutProperty->setAccessible(true);
        $jsLayoutProperty->setValue($this->block, $jsLayout);

        $this->layoutProcessorMock->expects($this->once())
            ->method('process')
            ->with($jsLayout)
            ->willReturn($modifiedJsLayout);

        $this->assertEquals(\Zend_Json::encode($modifiedJsLayout), $this->block->getJsLayout());
    }

    public function testGetFormKey()
    {
        $formKey = 'form_key_value';
        $this->formKeyMock->expects($this->once())
            ->method('getFormKey')
            ->willReturn($formKey);
        $this->assertEquals($formKey, $this->block->getFormKey());
    }

    public function testGetCheckoutConfig()
    {
        $config = ['configField' => 'configValue'];
        $this->configProviderMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);
        $this->assertEquals($config, $this->block->getCheckoutConfig());
    }
}
