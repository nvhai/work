<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Plugin\Checkout\Controller\Index;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Forward;
use Magento\Checkout\Controller\Index\Index as CheckoutIndex;
use Magento\Framework\Module\Manager;

/**
 * Class Index
 * @package Aheadworks\OneStepCheckout\Plugin\Checkout\Controller\Index
 */
class Index
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param ResultFactory $resultFactory
     * @param Manager $moduleManager
     */
    public function __construct(ResultFactory $resultFactory, Manager $moduleManager)
    {
        $this->resultFactory = $resultFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Perform forward to one step checkout action if needed
     *
     * @param CheckoutIndex $subject
     * @param \Closure $proceed
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(CheckoutIndex $subject, \Closure $proceed)
    {
        if ($this->isNeedToPerformForwardToOneStepCheckout()) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $result = $resultForward
                ->setModule('onestepcheckout')
                ->setController('index')
                ->forward('index');
        } else {
            $result = $proceed();
        }
        return $result;
    }

    /**
     * Check if need to perform forward to one step checkout
     *
     * @return bool
     */
    private function isNeedToPerformForwardToOneStepCheckout()
    {
        return $this->moduleManager->isOutputEnabled('Aheadworks_OneStepCheckout');
    }
}
