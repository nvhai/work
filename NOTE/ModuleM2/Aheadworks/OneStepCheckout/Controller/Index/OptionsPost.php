<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Controller\Index;

use Aheadworks\OneStepCheckout\Api\CartItemOptionsManagementInterface;
use Aheadworks\OneStepCheckout\Api\Data\CartItemOptionsDetailsInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class OptionsPost
 * @package Aheadworks\OneStepCheckout\Controller\Index
 */
class OptionsPost extends Action
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var CartItemOptionsManagementInterface
     */
    private $optionsManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param CheckoutSession $checkoutSession
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CartItemOptionsManagementInterface $optionsManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        CheckoutSession $checkoutSession,
        DataObjectProcessor $dataObjectProcessor,
        CartItemOptionsManagementInterface $optionsManagement,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->checkoutSession = $checkoutSession;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->optionsManagement = $optionsManagement;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        /** @var RequestInterface|HttpRequest $request */
        $request = $this->getRequest();
        $resultData = [];

        $isFromKeyValid = $this->formKeyValidator->validate($request);
        if ($isFromKeyValid && $request->isPost()) {
            $postData = $request->getPostValue();
            $quoteId = $this->checkoutSession->getQuoteId();
            if (isset($postData['itemId'])
                && isset($postData['options'])
                && $quoteId
            ) {
                try {
                    $optionsDetails = $this->optionsManagement->update(
                        $postData['itemId'],
                        $quoteId,
                        $postData['options']
                    );
                    $resultData = [
                        'success' => true,
                        'optionDetails' => $this->dataObjectProcessor->buildOutputDataArray(
                            $optionsDetails,
                            CartItemOptionsDetailsInterface::class
                        )
                    ];
                } catch (LocalizedException $e) {
                    $resultData = [
                        'success' => false,
                        'errorMessage' => $e->getMessage()
                    ];
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $resultData = [
                        'success' => false,
                        'errorMessage' => $e->getMessage()
                    ];
                }
            }
        }

        return $resultJson->setData($resultData);
    }
}
