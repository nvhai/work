<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model;

use Aheadworks\OneStepCheckout\Api\CartItemManagementInterface;
use Aheadworks\OneStepCheckout\Model\GuestCartItemManagement;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\GuestCartItemManagement
 */
class GuestCartItemManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestCartItemManagement
     */
    private $guestItemManagement;

    /**
     * @var QuoteIdMaskFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteIdMaskFactoryMock;

    /**
     * @var CartItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartItemManagementMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->quoteIdMaskFactoryMock = $this->getMockBuilder(QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->cartItemManagementMock = $this->getMockForAbstractClass(CartItemManagementInterface::class);
        $this->guestItemManagement = $objectManager->getObject(
            GuestCartItemManagement::class,
            [
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'cartItemManagement' => $this->cartItemManagementMock
            ]
        );
    }

    public function testRemove()
    {
        $cartIdMasked = 'masked_id_value';
        $cartId = 1;
        $itemId = 2;

        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                '__call'
            ])
            ->getMock();
        $paymentDetailsMock = $this->getMockForAbstractClass(PaymentDetailsInterface::class);

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
        $this->cartItemManagementMock->expects($this->once())
            ->method('remove')
            ->with($itemId, $cartId)
            ->willReturn($paymentDetailsMock);

        $this->assertSame($paymentDetailsMock, $this->guestItemManagement->remove($itemId, $cartIdMasked));
    }

    public function testUpdate()
    {
        $cartIdMasked = 'masked_id_value';
        $cartId = 1;

        /** @var TotalsItemInterface|\PHPUnit_Framework_MockObject_MockObject $totalsItemMock */
        $totalsItemMock = $this->getMockForAbstractClass(TotalsItemInterface::class);
        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                '__call'
            ])
            ->getMock();
        $paymentDetailsMock = $this->getMockForAbstractClass(PaymentDetailsInterface::class);

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
        $this->cartItemManagementMock->expects($this->once())
            ->method('update')
            ->with($totalsItemMock, $cartId)
            ->willReturn($paymentDetailsMock);

        $this->assertSame($paymentDetailsMock, $this->guestItemManagement->update($totalsItemMock, $cartIdMasked));
    }
}
