<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Test\Unit\Model\Newsletter;

use Aheadworks\OneStepCheckout\Model\Newsletter\ConfigProvider;
use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\OneStepCheckout\Model\Newsletter\ConfigProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var SubscriberFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriberFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isNewsletterSubscribeOptionEnabled',
                'isNewsletterSubscribeOptionCheckedByDefault'
            ])
            ->getMock();
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isLoggedIn',
                'getCustomerId'
            ])
            ->getMock();
        $this->subscriberFactoryMock = $this->getMockBuilder(SubscriberFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->configProvider = $objectManager->getObject(
            ConfigProvider::class,
            [
                'config' => $this->configMock,
                'scopeConfig' => $this->scopeConfigMock,
                'customerSession' => $this->customerSessionMock,
                'subscriberFactory' => $this->subscriberFactoryMock
            ]
        );
    }

    /**
     * Set up base mocks for getConfig() method
     *
     * @param bool $isEnabled
     * @param bool $isChecked
     * @param bool $isGuestSubscriptionsAllowed
     * @return void
     */
    private function setUpGetConfig($isEnabled, $isChecked, $isGuestSubscriptionsAllowed)
    {
        $this->configMock->expects($this->once())
            ->method('isNewsletterSubscribeOptionEnabled')
            ->willReturn($isEnabled);
        $this->configMock->expects($this->once())
            ->method('isNewsletterSubscribeOptionCheckedByDefault')
            ->willReturn($isChecked);
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($isGuestSubscriptionsAllowed);
    }

    public function testGetConfigNotLoggedIn()
    {
        $this->setUpGetConfig(true, true, false);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->assertEquals(
            [
                'isEnabled' => true,
                'isChecked' => true,
                'isSubscribed' => false,
                'isGuestSubscriptionsAllowed' => false
            ],
            $this->configProvider->getConfig()
        );
    }

    /**
     * @param bool $isSubscribed
     * @dataProvider boolDataProvider
     */
    public function testGetConfigLoggedIn($isSubscribed)
    {
        $customerId = 1;

        $subscriberMock = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'loadByCustomerId',
                'isSubscribed'
            ])
            ->getMock();

        $this->setUpGetConfig(true, true, false);
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->subscriberFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($subscriberMock);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $subscriberMock->expects($this->once())
            ->method('loadByCustomerId')
            ->with($customerId)
            ->willReturnSelf();
        $subscriberMock->expects($this->once())
            ->method('isSubscribed')
            ->willReturn($isSubscribed);

        $this->assertEquals(
            [
                'isEnabled' => true,
                'isChecked' => true,
                'isSubscribed' => $isSubscribed,
                'isGuestSubscriptionsAllowed' => false
            ],
            $this->configProvider->getConfig()
        );
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
