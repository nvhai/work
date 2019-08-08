<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Controller\Adminhtml\Subscription;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassSync
 */
class MassSync extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $redirectUrl = '*/*/';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \IWD\BluePaySubs\Model\Service\Subscription
     */
    protected $serviceSubscription;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param \IWD\BluePaySubs\Model\Service\Subscription $subscriptionService
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory,
        \IWD\BluePaySubs\Model\Service\Subscription $serviceSubscription,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);

        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->serviceSubscription = $serviceSubscription;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Return component referer url, or something
     *
     * @return null|string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl()?: 'bsubs/subscription/index/';
    }

    /**
     * Sync selected subscriptions
     *
     * @param AbstractDb $collection
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    protected function massAction(AbstractDb $collection)
    {
        /** @var \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription */
        foreach ($collection->getItems() as $subscription) {
            try {
                $this->serviceSubscription->synchronize($subscription);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__(
                    "Subscription #%1 synchronize error '%2'",
                    $subscription->getId(),
                    $e->getMessage()
                ));
            }
        }
        $this->messageManager->addSuccessMessage(__('Subscription(s) synchronized.'));

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
