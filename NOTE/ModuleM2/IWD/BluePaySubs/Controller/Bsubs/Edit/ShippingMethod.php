<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Controller\Bsubs\Edit;

use Magento\Framework\Exception\LocalizedException;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;

/**
 * ShippingMethod Class
 */
class ShippingMethod extends \IWD\BluePaySubs\Controller\Bsubs
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
                throw new LocalizedException(__('Error, no shipping method specified'));
            }
            $this->updateShippingMethod($subscription, $params['shipping']);
            $this->updateRebill($subscription, 'Shipping method changed.');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

        }
        $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId()]);

        return $resultRedirect;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param array $shipping
     * @throws LocalizedException
     * @throws \Exception
     * @return $this
     */
    protected function updateShippingMethod(SubscriptionInterface $subscription, array $shipping)
    {
        if (empty($shipping['rate'])) {
            throw new LocalizedException(__('Error, no shipping rate specified.'));
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();
        $address = $quote->getShippingAddress();
        $oldAmount = $address->getShippingAmount();
        $address->setShippingMethod($shipping['rate']);
        $items = $quote->getAllVisibleItems();
        $item = array_shift($items);
        $newAmount = max(0, $subscription->getAmount() - $oldAmount) /$item->getQty();
        $subscription->calculateAmount($newAmount);
        $subscription->addRelatedObject($address, true);

        return $this;
    }
}
