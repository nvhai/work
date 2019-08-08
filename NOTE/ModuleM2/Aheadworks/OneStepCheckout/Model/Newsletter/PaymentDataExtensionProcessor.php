<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Newsletter;

use Aheadworks\OneStepCheckout\Model\Config;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class PaymentDataExtensionProcessor
 * @package Aheadworks\OneStepCheckout\Model\Newsletter
 */
class PaymentDataExtensionProcessor
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param SubscriberFactory $subscriberFactory
     * @param Config $config
     * @param CustomerSession $customerSession
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        Config $config,
        CustomerSession $customerSession
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->config = $config;
        $this->customerSession = $customerSession;
    }

    /**
     * Process subscriber extension attributes of payment data
     *
     * @param PaymentInterface $paymentData
     * @return void
     */
    public function process(PaymentInterface $paymentData)
    {
        if ($this->config->isNewsletterSubscribeOptionEnabled()) {
            $isSubscribeFlag = $paymentData->getExtensionAttributes() === null
                ? false
                : $paymentData->getExtensionAttributes()->getIsSubscribeForNewsletter();

            if ($isSubscribeFlag) {
                /** @var Subscriber $subscriber */
                $subscriber = $this->subscriberFactory->create();
                if ($this->customerSession->isLoggedIn()) {
                    $customerId = $this->customerSession->getCustomerId();
                    if (!$this->isSubscribedByCustomerId($customerId)) {
                        $subscriber->subscribeCustomerById($customerId);
                    }
                } else {
                    $email = $paymentData->getExtensionAttributes() === null
                        ? false
                        : $paymentData->getExtensionAttributes()->getSubscriberEmail();
                    if ($email && !$this->isSubscribedByEmail($email)) {
                        $subscriber->subscribe($email);
                    }
                }
            }
        }
    }

    /**
     * Check if subscribed by customer ID
     *
     * @param int $customerId
     * @return bool
     */
    private function isSubscribedByCustomerId($customerId)
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        return $subscriber
            ->loadByCustomerId($customerId)
            ->isSubscribed();
    }

    /**
     * Check if subscribed by email
     *
     * @param string $email
     * @return bool
     */
    private function isSubscribedByEmail($email)
    {
        /** @var Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        return $subscriber
            ->loadByEmail($email)
            ->isSubscribed();
    }
}
