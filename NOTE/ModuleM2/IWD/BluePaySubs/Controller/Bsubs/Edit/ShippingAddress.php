<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Controller\Bsubs\Edit;

use IWD\BluePaySubs\Model\Source\Agent;
use Magento\Framework\Exception\LocalizedException;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;

/**
 * ShippingAddress Class
 */
class ShippingAddress extends BillingAddress
{
    /**
     * Subscriptions edit page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $initialized = $this->_init();
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($initialized !== true) {
            $resultRedirect->setPath('*/bsubs/index');
            return $resultRedirect;
        }
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->registry->registry('current_subs');

        try {
            if (!isset($params['shipping'])) {
                throw new LocalizedException(__('Error, shipping address not specified'));
            }
            $this->updateSubscriptionAddress($subscription, $params['shipping'], 'Shipping address');
            $this->updateRebill($subscription);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

        }
        $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId()]);

        return $resultRedirect;
    }

    /**
     * @inheritdoc
     */
    protected function changeAddress(SubscriptionInterface $subscription, array $address)
    {
        return $this->subscriptionService->changeShippingAddress($subscription, $address, Agent::AGENT_CUSTOMER);
    }
}
