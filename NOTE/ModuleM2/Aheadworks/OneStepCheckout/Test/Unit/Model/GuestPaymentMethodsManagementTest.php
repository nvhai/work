<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model;

use Aheadworks\OneStepCheckout\Api\PaymentMethodsManagementInterface;
use Aheadworks\OneStepCheckout\Model\GuestPaymentMethodsManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\GuestPaymentMethodsManagement
 */
class GuestPaymentMethodsManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestPaymentMethodsManagement
     */
    private $guestPaymentMethodsManagement;

    /**
     * @var QuoteIdMaskFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteIdMaskFactoryMock;

    /**
     * @var PaymentMethodsManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodsManagementMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->quoteIdMaskFactoryMock = $this->getMockBuilder(QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->paymentMethodsManagementMock = $this->getMockForAbstractClass(
            PaymentMethodsManagementInterface::class
        );
        $this->guestPaymentMethodsManagement = $objectManager->getObject(
            GuestPaymentMethodsManagement::class,
            [
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'paymentMethodsManagement' => $this->paymentMethodsManagementMock
            ]
        );
    }

    public function testRemove()
    {
        $cartIdMasked = 'masked_id_value';
        $cartId = 1;

        /** @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject $shippingAddressMock */
        $shippingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        /** @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject $billingAddressMock */
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                '__call'
            ])
            ->getMock();
        $paymentMethodMock = $this->getMockForAbstractClass(PaymentMethodInterface::class);

        $this->quoteIdMaskFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($cartIdMasked, 'masked_id')
            ->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())
            ->method('__call')
            ->with('getQuoteId')
            ->willReturn($cartId);
        $this->paymentMethodsManagementMock->expects($this->once())
            ->method('getPaymentMethods')
            ->with($cartId, $shippingAddressMock, $billingAddressMock)
            ->willReturn([$paymentMethodMock]);

        $this->assertSame(
            [$paymentMethodMock],
            $this->guestPaymentMethodsManagement->getPaymentMethods(
                $cartIdMasked,
                $shippingAddressMock,
                $billingAddressMock
            )
        );
    }
}
