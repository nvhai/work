<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model;

use Aheadworks\OneStepCheckout\Model\PaymentMethodsManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\PaymentMethodsManagement
 */
class PaymentMethodsManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodsManagement
     */
    private $paymentMethodsManagement;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var PaymentMethodManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodManagementMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->paymentMethodManagementMock = $this->getMockForAbstractClass(PaymentMethodManagementInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->paymentMethodsManagement = $objectManager->getObject(
            PaymentMethodsManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testGetPaymentMethods()
    {
        $cartId = 1;

        /** @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject $shippingAddressMock */
        $shippingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setShippingAddress',
                '__call'
            ])
            ->getMock();
        $paymentMethodMock = $this->getMockForAbstractClass(PaymentMethodInterface::class);

        $shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(null);
        $shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(2);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('__call')
            ->with('setIsMultiShipping', [false])
            ->willReturnSelf();
        $quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($shippingAddressMock)
            ->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock);
        $this->paymentMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);

        $this->assertEquals(
            [$paymentMethodMock],
            $this->paymentMethodsManagement->getPaymentMethods($cartId, $shippingAddressMock)
        );
    }

    public function testGetPaymentMethodsWithBillingAddress()
    {
        $cartId = 1;

        /** @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject $shippingAddressMock */
        $shippingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        /** @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject $billingAddressMock */
        $billingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setShippingAddress',
                'setBillingAddress',
                '__call'
            ])
            ->getMock();
        $paymentMethodMock = $this->getMockForAbstractClass(PaymentMethodInterface::class);

        $shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(null);
        $billingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(null);
        $shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(2);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('__call')
            ->with('setIsMultiShipping', [false])
            ->willReturnSelf();
        $quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($shippingAddressMock)
            ->willReturnSelf();
        $quoteMock->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddressMock)
            ->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock);
        $this->paymentMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($cartId)
            ->willReturn([$paymentMethodMock]);

        $this->assertEquals(
            [$paymentMethodMock],
            $this->paymentMethodsManagement->getPaymentMethods(
                $cartId,
                $shippingAddressMock,
                $billingAddressMock
            )
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Unable to retrieve payment methods. Please check input data.
     */
    public function testGetPaymentMethodsException()
    {
        $cartId = 1;

        /** @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject $shippingAddressMock */
        $shippingAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setShippingAddress',
                '__call'
            ])
            ->getMock();
        $exception = new \Exception('Exception message.');

        $shippingAddressMock->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn(null);
        $shippingAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(2);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('__call')
            ->with('setIsMultiShipping', [false])
            ->willReturnSelf();
        $quoteMock->expects($this->once())
            ->method('setShippingAddress')
            ->with($shippingAddressMock)
            ->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteMock)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->paymentMethodsManagement->getPaymentMethods($cartId, $shippingAddressMock);
    }
}
