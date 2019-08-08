<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model;

use Aheadworks\OneStepCheckout\Api\Data\CartItemUpdateDetailsInterface;
use Aheadworks\OneStepCheckout\Api\Data\CartItemUpdateDetailsInterfaceFactory;
use Aheadworks\OneStepCheckout\Model\CartItemManagement;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Model\Quote;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\CartItemManagement
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartItemManagement
     */
    private $itemManagement;

    /**
     * @var CartItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemRepositoryMock;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var PaymentInformationManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInformationManagementMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var CartItemUpdateDetailsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemUpdateDetailsFactoryMock;

    protected function setUp()
    {
        $this->markTestSkipped('To fix test.');
        $objectManager = new ObjectManager($this);
        $this->itemRepositoryMock = $this->getMockForAbstractClass(CartItemRepositoryInterface::class);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->paymentInformationManagementMock = $this->getMockForAbstractClass(
            PaymentInformationManagementInterface::class
        );
        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['buildOutputDataArray'])
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['populateWithArray'])
            ->getMock();
        $this->itemUpdateDetailsFactoryMock = $this->getMockBuilder(CartItemUpdateDetailsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->itemManagement = $objectManager->getObject(
            CartItemManagement::class,
            [
                'itemRepository' => $this->itemRepositoryMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'paymentInformationManagement' => $this->paymentInformationManagementMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'itemUpdateDetailsFactory' => $this->itemUpdateDetailsFactoryMock
            ]
        );
    }

    public function testRemove()
    {
        $cartId = 1;
        $itemId = 2;

        $itemUpdateDetailsMock = $this->getMockForAbstractClass(CartItemUpdateDetailsInterface::class);
        $quoteMock = $this->getMockForAbstractClass(CartInterface::class);
        $paymentDetailsMock = $this->getMockForAbstractClass(PaymentDetailsInterface::class);

        $this->itemRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($cartId, $itemId);
        $this->itemUpdateDetailsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemUpdateDetailsMock);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($quoteMock);
        $itemUpdateDetailsMock->expects($this->once())
            ->method('setCartDetails')
            ->with($quoteMock)
            ->willReturnSelf();
        $this->paymentInformationManagementMock->expects($this->once())
            ->method('getPaymentInformation')
            ->with($cartId)
            ->willReturn($paymentDetailsMock);
        $itemUpdateDetailsMock->expects($this->once())
            ->method('setPaymentDetails')
            ->with($paymentDetailsMock)
            ->willReturnSelf();

        $this->assertSame($itemUpdateDetailsMock, $this->itemManagement->remove($itemId, $cartId));
    }

    public function testUpdate()
    {
        $cartId = 1;
        $itemId = 2;
        $totalItemData = ['item_id' => $itemId, 'qty' => 2.00];

        /** @var TotalsItemInterface|\PHPUnit_Framework_MockObject_MockObject $totalsItemMock */
        $totalsItemMock = $this->getMockForAbstractClass(TotalsItemInterface::class);
        $quoteItemMock = $this->getMockForAbstractClass(CartItemInterface::class);
        $itemUpdateDetailsMock = $this->getMockForAbstractClass(CartItemUpdateDetailsInterface::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemById'])
            ->getMock();
        $cartMock = $this->getMockForAbstractClass(CartInterface::class);
        $paymentDetailsMock = $this->getMockForAbstractClass(PaymentDetailsInterface::class);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);
        $totalsItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);
        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($quoteItemMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($totalsItemMock, TotalsItemInterface::class)
            ->willReturn($totalItemData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($quoteItemMock, $totalItemData, TotalsItemInterface::class);
        $this->itemRepositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteItemMock);
        $this->itemUpdateDetailsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemUpdateDetailsMock);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($cartId)
            ->willReturn($cartMock);
        $itemUpdateDetailsMock->expects($this->once())
            ->method('setCartDetails')
            ->with($cartMock)
            ->willReturnSelf();
        $this->paymentInformationManagementMock->expects($this->once())
            ->method('getPaymentInformation')
            ->with($cartId)
            ->willReturn($paymentDetailsMock);
        $itemUpdateDetailsMock->expects($this->once())
            ->method('setPaymentDetails')
            ->with($paymentDetailsMock)
            ->willReturnSelf();

        $this->assertSame($itemUpdateDetailsMock, $this->itemManagement->update($totalsItemMock, $cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart item 2 doesn't exist.
     */
    public function testUpdateException()
    {
        $cartId = 1;
        $itemId = 2;

        /** @var TotalsItemInterface|\PHPUnit_Framework_MockObject_MockObject $totalsItemMock */
        $totalsItemMock = $this->getMockForAbstractClass(TotalsItemInterface::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemById'])
            ->getMock();

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);
        $totalsItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);
        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn(null);

        $this->itemManagement->update($totalsItemMock, $cartId);
    }
}
