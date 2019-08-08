<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Block\Adminhtml\Subscription\Edit\Tab;

use IWD\BluePaySubs\Helper;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Payment tab
 */
class Payment extends Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $url;

    /**
     * @var Helper\Vault
     */
    protected $vaultHelper;

    /**
     * @var \IWD\BluePaySubs\Helper\Address
     */
    protected $addressHelper;

    /**
     * Payment tab constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Url $url
     * @param Helper\Vault $vaultHelper
     * @param \IWD\BluePaySubs\Helper\Address $addressHelper
     * @param array $data
     * @internal param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Url $url,
        Helper\Vault $vaultHelper,
        \IWD\BluePaySubs\Helper\Address $addressHelper,
        array $data
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->url = $url;
        $this->vaultHelper = $vaultHelper;
        $this->addressHelper = $addressHelper;
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
        return __('Payment');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Payment');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $subscription = $this->_coreRegistry->registry('current_bsubs');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('payment_');

        $fieldset = $form->addFieldset('fieldset_payment', ['legend' => __('Payment Information')]);

        $options = [];

        try {
            $activeToken = $this->vaultHelper->getQuoteToken($quote);

            if (isset($activeToken) && $activeToken instanceof PaymentTokenInterface) {
                $form->setValues(
                    [
                        'public_hash' => $activeToken->getPublicHash(),
                    ]
                );

                // Ensure selected token always displays, even if not visible.
                $options[$activeToken->getPublicHash()] = $this->vaultHelper->getTokenLabel($activeToken);
            } else {
                throw new \Exception('This subscription has no assigned payment account. Please choose a payment account '
                    . 'below and save to prevent interruption.');
            }
        } catch (\Exception $e) {
            $fieldset->addField(
                'payment_error',
                'note',
                [
                    'name' => 'payment_error',
                    'label' => __(''),
                    'text' => __(
                        'This subscription has no assigned payment account. Please choose a payment account '
                        . 'below and save to prevent interruption.'
                    ),
                    'before_element_html' => '<div class="message message-error">',
                    'after_element_html' => '</div>',
                ]
            );

            $options[] = '';
        }

        try {
            if ($subscription->getCustomerId()) {
                /** @var PaymentTokenInterface[] $tokens */
                $tokens = $this->vaultHelper->searchTokens(['customer_id' => $subscription->getCustomerId()]);
                foreach ($tokens as $token) {
                    if (isset($activeToken) && $activeToken instanceof PaymentTokenInterface
                        && $activeToken->getEntityId() === $token->getEntityId()) {
                        continue;
                    }
                    $options[$token->getPublicHash()] = $this->vaultHelper->getTokenLabel($token);
                }
            }

            $isActiveTokenExists = isset($activeToken) && $activeToken instanceof PaymentTokenInterface;
            $fieldset->addField(
                'public_hash',
                'select',
                [
                    'name' => 'public_hash',
                    'label' => __('Payment Account'),
                    'title' => __('Payment Account'),
                    'options' => $options,
                    'note' => __(
                        'Here you can choose stored account for future payments.<br />'
                        . 'Currently address corresponding to %1.<br/>'
                        . '<b>Any changes will take effect on the next billing. '
                        . '<br/>Also admin can\'t add new payment account.</b>',
                        $isActiveTokenExists ? $this->vaultHelper->getTokenLabel($activeToken) : ''
                    ),
                    'required' => true,
                ]
            );
            $address = $this->getFormattedAddress($quote->getBillingAddress()->getDataModel());

            $fieldset->addField(
                'billing_address',
                'note',
                [
                    'name' => 'billing_address',
                    'label' => __('Billing Address'),
                    'text' => $address,
                    'note' => __(
                        'For editing billing address, please, go to the '
                        . '<a href="%1" target="_blank">customer profile</a>.',
                        $this->escapeUrl(
                            $this->url->getUrl('customer/index/edit', ['id' => $subscription->getCustomerId()])
                        )
                    ),
                ]
            );
        } catch (\Exception $e) {
            $e->getMessage();
        }

        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_subscription_view_tab_payment_prepare_form', ['form' => $form]);

        parent::_prepareForm();

        return $this;
    }

    /**
     * Get formatted card address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @param string $format
     * @return string
     * @see \Magento\Customer\Model\Address\AbstractAddress::format()
     */
    public
    function getFormattedAddress(\Magento\Customer\Api\Data\AddressInterface $address, $format = 'html')
    {
        return $this->escapeHtml($this->addressHelper->getFormattedAddress($address, $format));
    }
}
