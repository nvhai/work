<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\CartItemOptionsManagementInterface;
use Aheadworks\OneStepCheckout\Api\Data\CartItemOptionsDetailsInterface;
use Aheadworks\OneStepCheckout\Api\Data\CartItemOptionsDetailsInterfaceFactory;
use Aheadworks\OneStepCheckout\Model\Cart\ImageProvider;
use Aheadworks\OneStepCheckout\Model\Cart\OptionsProvider as ItemOptionsProvider;
use Aheadworks\OneStepCheckout\Model\Cart\Validator;
use Aheadworks\OneStepCheckout\Model\Product\ConfigurationPool;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;

/**
 * Class CartItemOptionsManagement
 * @package Aheadworks\OneStepCheckout\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemOptionsManagement implements CartItemOptionsManagementInterface
{
    /**
     * @var CartItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Validator
     */
    private $quoteValidator;

    /**
     * @var PaymentInformationManagementInterface
     */
    private $paymentInformationManagement;

    /**
     * @var CartItemOptionsProcessor
     */
    private $itemOptionsProcessor;

    /**
     * @var ConfigurationPool
     */
    private $configurationPool;

    /**
     * @var ImageProvider
     */
    private $imageProvider;

    /**
     * @var ItemOptionsProvider
     */
    private $itemOptionsProvider;

    /**
     * @var CartItemOptionsDetailsInterfaceFactory
     */
    private $optionDetailsFactory;

    /**
     * @param CartItemRepositoryInterface $itemRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param Validator $quoteValidator
     * @param CartItemOptionsProcessor $itemOptionsProcessor
     * @param ConfigurationPool $configurationPool
     * @param ImageProvider $imageProvider
     * @param ItemOptionsProvider $itemOptionsProvider
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     * @param CartItemOptionsDetailsInterfaceFactory $optionDetailsFactory
     */
    public function __construct(
        CartItemRepositoryInterface $itemRepository,
        CartRepositoryInterface $quoteRepository,
        Validator $quoteValidator,
        CartItemOptionsProcessor $itemOptionsProcessor,
        ConfigurationPool $configurationPool,
        ImageProvider $imageProvider,
        ItemOptionsProvider $itemOptionsProvider,
        PaymentInformationManagementInterface $paymentInformationManagement,
        CartItemOptionsDetailsInterfaceFactory $optionDetailsFactory
    ) {
        $this->itemRepository = $itemRepository;
        $this->quoteRepository = $quoteRepository;
        $this->quoteValidator = $quoteValidator;
        $this->itemOptionsProcessor = $itemOptionsProcessor;
        $this->configurationPool = $configurationPool;
        $this->imageProvider = $imageProvider;
        $this->itemOptionsProvider = $itemOptionsProvider;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->optionDetailsFactory = $optionDetailsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function update($itemId, $cartId, $options)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('Cart item %1 doesn\'t exist.', $itemId)
            );
        }

        $productType = $quoteItem->getProduct()->getTypeId();
        $this->itemOptionsProcessor->addProductOptions($productType, $quoteItem);
        $this->configurationPool->getConfiguration($productType)
            ->setOptions($quoteItem, \Zend_Json::decode($options));

        $this->itemRepository->save($quoteItem);

        if (!$this->quoteValidator->isValid($quote)) {
            $messages = $this->quoteValidator->getMessages();
            throw new InputException(__($messages[0]));
        }

        /** @var CartItemOptionsDetailsInterface $optionDetails */
        $optionDetails = $this->optionDetailsFactory->create();
        $optionDetails
            ->setOptionsDetails(\Zend_Json::encode($this->itemOptionsProvider->getOptionsData($cartId)))
            ->setImageDetails(\Zend_Json::encode($this->imageProvider->getItemsImageData($cartId)))
            ->setPaymentDetails($this->paymentInformationManagement->getPaymentInformation($cartId));

        return $optionDetails;
    }
}
