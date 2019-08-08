<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Block\Adminhtml\Subscription\Edit\Tab;

use IWD\BluePaySubs\Helper\Data as Helper;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * General tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var \IWD\BluePaySubs\Model\Source\Status
     */
    protected $statusModel;

    /**
     * @var \IWD\BluePaySubs\Model\Source\Period
     */
    protected $periodModel;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * General constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \IWD\BluePaySubs\Model\Source\Status $statusModel
     * @param \IWD\BluePaySubs\Model\Source\Period $periodModel
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \IWD\BluePaySubs\Model\Source\Status $statusModel,
        \IWD\BluePaySubs\Model\Source\Period $periodModel,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        Helper $helper,
        array $data
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->statusModel = $statusModel;
        $this->periodModel = $periodModel;
        $this->customerRepository = $customerRepository;
        $this->currencyFactory = $currencyFactory;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('General Information');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->_coreRegistry->registry('current_bsubs');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('subscription_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($subscription->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();
        $products = '';

        try {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                $products .= sprintf('%s (SKU: %s)<br />', $item->getName(), $item->getSku());
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $products .= '<div class="message message-error">' . __('Product could not be loaded') . '</div>';
        }

        $fieldset->addField(
            'product_label',
            'note',
            [
                'name' => 'product',
                'label' => __('Product'),
                'text' => $products,
            ]
        );

        $fieldset->addField(
            'description',
            'text',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
            ]
        );

        $this->addSubtotalField($subscription, $fieldset);

        $fieldset->addField(
            'status_label',
            'note',
            [
                'name' => 'status',
                'label' => __('Status'),
                'text' => $this->statusModel->getOptionText($subscription->getStatus()),
            ]
        );

        $fieldset->addField(
            'created_at_formatted',
            'note',
            [
                'name' => 'created_at',
                'label' => __('Created At'),
                'text' => (new \DateTime($subscription->getCreatedAt()))->format('M d, Y')
            ]
        );

        if (!empty($subscription->getLastDate())) {
            $fieldset->addField(
                'last_date_formatted',
                'note',
                [
                    'name' => 'last_date',
                    'label' => __('Last Run Date'),
                    'text' => (new \DateTime($subscription->getLastDate()))->format('M d, Y')
                ]
            );
        }

        $subscription->setData(
            'next_date_formatted',
            $subscription->getNextDate()
        );

        $fieldset->addField(
            'next_date_formatted',
            'note',
            [
                'name' => 'next_date',
                'label' => __('Next Run Date'),
                'text' => (new \DateTime($subscription->getNextDate()))->format('M d, Y')
            ]
        );

        $fieldset->addField(
            'frequency',
            'note',
            [
                'name' => 'frequency',
                'label' => __('Frequency'),
                'text' => $this->helper->generateOptionTitle($subscription->getData(), true)
            ]
        );

        $fieldset->addField(
            'cycles_run_count',
            'label',
            [
                'name' => 'cycles_run_count',
                'label' => __('Cycles Run Count'),
            ]
        );

        if ($subscription->getPaymentFailedRunCount()) {
            $fieldset->addField(
                'payment_failed_run_count',
                'label',
                [
                    'name' => 'payment_failed_run_count',
                    'label' => __('Payment Failed Run Count'),
                ]
            );
        }

        if ($subscription->getCustomerId() > 0) {
            try {
                $customer = $this->customerRepository->getById($subscription->getCustomerId());

                $fieldset->addField(
                    'customer',
                    'note',
                    [
                        'name' => 'customer',
                        'label' => __('Customer'),
                        'text' => __(
                            '<a href="%1">%2 %3</a> (%4)',
                            $this->escapeUrl(
                                $this->getUrl('customer/index/edit', ['id' => $subscription->getCustomerId()])
                            ),
                            $this->escapeHtml($customer->getFirstname()),
                            $this->escapeHtml($customer->getLastname()),
                            $this->escapeHtml($customer->getEmail())
                        )
                    ]
                );
            } catch (\Exception $e) {
                // Do nothing on exception.
            }
        }

        $this->addStoreField($subscription, $fieldset);
        $subscription->setFrequency($this->helper->getCurrentFrequencyId($subscription));
        $form->setValues($subscription->getData());
        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_subscription_view_tab_main_prepare_form', ['form' => $form]);

        parent::_prepareForm();

        return $this;
    }

    /**
     * Add store name field to the fieldset.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return void
     */
    protected function addStoreField(
        \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset
    )
    {
        if ($this->_storeManager->isSingleStoreMode() === false) {
            try {
                $store = $this->_storeManager->getStore($subscription->getStoreId());

                $fieldset->addField(
                    'store_id',
                    'note',
                    [
                        'name' => 'store_id',
                        'label' => __('Purchase Point'),
                        'text' => $store->getName(),
                    ]
                );
            } catch (\Exception $e) {
                // Do nothing on exception.
            }
        }
    }

    /**
     * Add subtotal field to the fieldset.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return void
     */
    protected function addSubtotalField(
        \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset
    )
    {
        try {
            /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $subscription->getQuote();
            $currency = $quote->getQuoteCurrencyCode();

            $currencyModel = $this->currencyFactory->create();
            $currencyModel->load($currency);

            $fieldset->addField(
                'amount',
                'note',
                [
                    'name' => 'amount',
                    'label' => __('Amount'),
                    'text' => $currencyModel->formatTxt($subscription->getAmount()),
                    'note' => __(
                        'Note: base grand total including shipping amount price'
                    ),
                ]
            );
        } catch (\Exception $e) {
            // Do nothing on exception.
        }
    }
}
