<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use IWD\BluePaySubs\Api\SubscriptionRepositoryInterface;

/**
 * Class Subscription
 * @package IWD\BluePaySubs\Controller\Adminhtml
 */
abstract class Subscription extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SubscriptionRepositoryInterface
     */
    protected $subscriptionRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \IWD\BluePaySubs\Helper\Data
     */
    protected $helper;

    /**
     * Index constructor.
     *
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \IWD\BluePaySubs\Helper\Data $helper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        SubscriptionRepositoryInterface $subscriptionRepository,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \IWD\BluePaySubs\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->customerRepository = $customerRepository;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * Determine if authorized to perform these actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('IWD_BluePaySubs::bsubs');
    }

    /**
     * Initialize subscription/customer models for the current request.
     *
     * @return bool Successful or not
     * @throws LocalizedException | \Exception
     */
    protected function _init()
    {
        /**
         * Load subscription by ID.
         */
        $id = (int)$this->getRequest()->getParam('entity_id');

        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->subscriptionRepository->getById($id);

        /**
         * If it doesn't exist, fail (redirect to grid).
         */
        if (!$subscription->getId()) {
            throw new \Exception(__('Could not load the requested subscription.'));
        }

        $this->registry->register('current_bsubs', $subscription);

        /**
         * Load and set customer (if any) for TokenBase.
         */
        if ($subscription->getCustomerId() > 0) {
            $customer = $this->customerRepository->getById($subscription->getCustomerId());

            if ($customer->getId() == $subscription->getCustomerId()) {
                $this->registry->register('current_customer', $customer);
            }
        }

        return true;
    }
}