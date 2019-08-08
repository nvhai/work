<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute\Firstname;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Address\Form\AttributeMeta\Modifier\Attribute\Firstname
 */
class FirstnameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Firstname
     */
    private $modifier;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isLoggedIn',
                'getCustomerId'
            ])
            ->getMock();
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->modifier = $objectManager->getObject(
            Firstname::class,
            [
                'customerSession' => $this->customerSessionMock,
                'customerRepository' => $this->customerRepositoryMock
            ]
        );
    }

    public function testModify()
    {
        $customerId = 1;
        $customerFirstName = 'John';

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($customerMock);
        $customerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($customerFirstName);

        $this->assertEquals(
            [
                'label' => 'First Name',
                'default' => $customerFirstName
            ],
            $this->modifier->modify(['label' => 'First Name'], 'shipping')
        );
    }

    public function testModifyNotLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEquals(
            ['label' => 'First Name'],
            $this->modifier->modify(['label' => 'First Name'], 'shipping')
        );
    }
}
