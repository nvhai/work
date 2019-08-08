<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute\PrefixSuffix;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute\PrefixSuffix
 */
class PrefixSuffixTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test attribute code
     */
    const ATTRIBUTE_CODE = 'prefix';

    /**
     * @var PrefixSuffix
     */
    private $modifier;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();
        $this->modifier = $objectManager->getObject(
            PrefixSuffix::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'escaper' => $this->escaperMock,
                'attributeCode' => self::ATTRIBUTE_CODE
            ]
        );
    }

    public function testModify()
    {
        $optionValue = 'Mr.;Ms.';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'customer/address/' . self::ATTRIBUTE_CODE . '_options',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($optionValue);
        $this->escaperMock->expects($this->exactly(2))
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->assertEquals(
            [
                'dataType' => 'select',
                'formElement' => 'select',
                'options' => [
                    ['value' => 'Mr.', 'label' => 'Mr.'],
                    ['value' => 'Ms.', 'label' => 'Ms.']
                ]
            ],
            $this->modifier->modify(['dataType' => 'text', 'formElement' => 'input'], 'shipping')
        );
    }

    public function testModifyNoOptions()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'customer/address/' . self::ATTRIBUTE_CODE . '_options',
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn('');
        $this->assertEquals(
            ['dataType' => 'text', 'formElement' => 'input'],
            $this->modifier->modify(['dataType' => 'text', 'formElement' => 'input'], 'shipping')
        );
    }
}
