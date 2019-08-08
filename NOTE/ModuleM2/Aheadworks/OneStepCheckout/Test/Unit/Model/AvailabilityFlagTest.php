<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model;

use Aheadworks\OneStepCheckout\Model\AvailabilityFlag;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\AvailabilityFlag
 */
class AvailabilityFlagTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AvailabilityFlag
     */
    private $availabilityFlag;

    /**
     * @var CheckoutSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var CheckoutHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutHelperMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->checkoutSessionMock = $this->getMockBuilder(CheckoutSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote'])
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoggedIn'])
            ->getMock();
        $this->checkoutHelperMock = $this->getMockBuilder(CheckoutHelper::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isAllowedGuestCheckout',
                'canOnepageCheckout'
            ])
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'hasItems',
                '__call',
                'validateMinimumAmount'
            ])
            ->getMock();
        $this->availabilityFlag = $objectManager->getObject(
            AvailabilityFlag::class,
            [
                'checkoutSession' => $this->checkoutSessionMock,
                'customerSession' => $this->customerSessionMock,
                'checkoutHelper' => $this->checkoutHelperMock
            ]
        );
    }

    public function testIsAvailable()
    {
        $this->checkoutHelperMock->expects($this->once())
            ->method('canOnepageCheckout')
            ->willReturn(true);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(true);
        $this->quoteMock->expects($this->once())
            ->method('__call')
            ->with('getHasError')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('validateMinimumAmount')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $this->assertTrue($this->availabilityFlag->isAvailable());
    }

    public function testIsAvailableNoItems()
    {
        $this->checkoutHelperMock->expects($this->once())
            ->method('canOnepageCheckout')
            ->willReturn(true);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(false);

        $this->assertFalse($this->availabilityFlag->isAvailable());
    }

    public function testIsAvailableHasError()
    {
        $this->checkoutHelperMock->expects($this->once())
            ->method('canOnepageCheckout')
            ->willReturn(true);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(true);
        $this->quoteMock->expects($this->once())
            ->method('__call')
            ->with('getHasError')
            ->willReturn(true);

        $this->assertFalse($this->availabilityFlag->isAvailable());
    }

    public function testIsAvailableInvalidOrderAmount()
    {
        $this->checkoutHelperMock->expects($this->once())
            ->method('canOnepageCheckout')
            ->willReturn(true);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(true);
        $this->quoteMock->expects($this->once())
            ->method('__call')
            ->with('getHasError')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('validateMinimumAmount')
            ->willReturn(false);

        $this->assertFalse($this->availabilityFlag->isAvailable());
    }

    public function testIsAvailableGuestAllowed()
    {
        $this->checkoutHelperMock->expects($this->once())
            ->method('canOnepageCheckout')
            ->willReturn(true);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(true);
        $this->quoteMock->expects($this->once())
            ->method('__call')
            ->with('getHasError')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('validateMinimumAmount')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->checkoutHelperMock->expects($this->once())
            ->method('isAllowedGuestCheckout')
            ->with($this->quoteMock)
            ->willReturn(true);

        $this->assertTrue($this->availabilityFlag->isAvailable());
    }

    public function testIsAvailableGuestDisallowed()
    {
        $this->checkoutHelperMock->expects($this->once())
            ->method('canOnepageCheckout')
            ->willReturn(true);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(true);
        $this->quoteMock->expects($this->once())
            ->method('__call')
            ->with('getHasError')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('validateMinimumAmount')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->checkoutHelperMock->expects($this->once())
            ->method('isAllowedGuestCheckout')
            ->with($this->quoteMock)
            ->willReturn(false);

        $this->assertFalse($this->availabilityFlag->isAvailable());
    }

    public function testIsAvailableCheckoutDisabled()
    {
        $this->checkoutHelperMock->expects($this->once())
            ->method('canOnepageCheckout')
            ->willReturn(false);

        $this->assertFalse($this->availabilityFlag->isAvailable());
    }
}
