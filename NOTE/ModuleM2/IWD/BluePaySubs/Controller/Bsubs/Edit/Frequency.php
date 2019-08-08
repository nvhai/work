<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Controller\Bsubs\Edit;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use IWD\BluePaySubs\Api\Data\SubscriptionInterface;
use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Helper\Data as Helper;

/**
 * Class Frequency
 * @package IWD\BluePaySubs\Controller\Bsubs\Edit
 */
class Frequency extends \IWD\BluePaySubs\Controller\Bsubs
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Frequency constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param RebillManagementInterface $rebillManagement
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \IWD\BluePaySubs\Api\SubscriptionRepositoryInterface $subscriptionRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        RebillManagementInterface $rebillManagement,
        Helper $helper
    ) {
        parent::__construct($context, $registry, $subscriptionRepository, $currentCustomer, $rebillManagement);

        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     * |\Magento\Framework\Controller\ResultInterface
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
            if (!isset($params['frequency'])) {
                throw new LocalizedException(__('Error, frequency not specified'));
            }
            $data = $this->helper->getFrequencyById($subscription, $params['frequency']);
            /**
             * Update subscription details
             */
            $subscription->setPeriodInterval($data['period_interval']);
            $subscription->setPeriod($data['period']);
            $subscription->setCycles($data['cycles']);
            $subscription->calculateAmount($data['price']);
            $subscription->calculateNextRun();
            $this->updateRebill($subscription, 'Frequency changed.');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

        }
        $resultRedirect->setPath('*/bsubs/edit', ['id' => $subscription->getId()]);

        return $resultRedirect;
    }
}