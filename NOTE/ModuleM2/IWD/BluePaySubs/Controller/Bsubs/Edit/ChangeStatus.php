<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Bsubs\Edit;

use IWD\BluePaySubs\Model\Source\Agent;

/**
 * ChangeStatus Class
 */
class ChangeStatus extends \IWD\BluePaySubs\Controller\Bsubs
{
    /**
     * Subscription status-change action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $initialized = $this->_init();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($initialized !== true) {
            $resultRedirect->setPath('*/bsubs/index');
            return $resultRedirect;
        }
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->registry->registry('current_subs');;

        try {
            $newStatus = $this->getRequest()->getParam('status');
            if($subscription->getStatus() == $newStatus) {
                $message = __("Subscription already changed");
                $this->messageManager->addNoticeMessage($message);
                $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId(), '_current' => true]);
                return $resultRedirect;
            }

            if ($rebillStatus = $this->rebillManagement->updateRebillStatus($subscription, $newStatus)) {
                $message = __(
                    'Subscription status changed to "%1".',
                    $newStatus
                );
                $subscription->setStatus($rebillStatus->getStatus())->addLog($message, ['agent_id' => Agent::AGENT_CUSTOMER]);
                $this->subscriptionRepository->save($subscription);
                $this->messageManager->addSuccessMessage($message);
            }
            else {
                throw new \Exception(
                    __("Subscription not updated")
                );
            }

            $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId(), '_current' => true]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('ERROR: %1', $e->getMessage()));
            $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId(), '_current' => true]);
        }

        return $resultRedirect;
    }
}
