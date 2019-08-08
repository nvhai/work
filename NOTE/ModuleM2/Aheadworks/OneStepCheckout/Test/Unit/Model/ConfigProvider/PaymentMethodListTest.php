<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\ConfigProvider;

use Aheadworks\OneStepCheckout\Model\ConfigProvider\PaymentMethodList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\ConfigProvider\PaymentMethodList
 */
class PaymentMethodListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodList
     */
    private $configProvider;

    /**
     * @var PaymentMethodManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodManagementMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->paymentMethodManagementMock = $this->getMockForAbstractClass(PaymentMethodManagementInterface::class);
        $this->configProvider = $objectManager->getObject(
            PaymentMethodList::class,
            ['paymentMethodManagement' => $this->paymentMethodManagementMock]
        );
    }

    public function testGetPaymentMethods()
    {
        $cartId = 1;
        $methodCode = 'checkmo';
        $methodTitle = 'Check / Money order';

        $paymentMethodMock = $this->getMockForAbstractClass(PaymentMethodInterface::class);

        $this->paymentMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);
        $paymentMethodMock->expects($this->once())
            ->method('getCode')
            ->willReturn($methodCode);
        $paymentMethodMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($methodTitle);

        $this->assertEquals(
            [
                ['code' => $methodCode, 'title' => $methodTitle]
            ],
            $this->configProvider->getPaymentMethods($cartId)
        );
    }
}
