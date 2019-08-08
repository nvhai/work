<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Service;

/**
 * EmailManager Class
 */
class EmailManager
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Directory\Model\Currency[]
     */
    protected $currencies = [];

    /**
     * EmailSender constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Send billing failure email to admin
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param string $message
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendBillingFailedEmail(
        \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription,
        $message
    ) {
        $active = $this->scopeConfig->getValue(
            'iwd_subs/billing_failed/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );

        if ($active != 1) {
            return $this;
        }

        if ($subscription->getStatus() == \IWD\BluePaySubs\Model\Source\Status::STATUS_PAYMENT_FAILED) {
            $paymentFailed = true;
        } else {
            $paymentFailed = false;
        }

        $paymentFailedActive = $this->scopeConfig->getValue(
            'iwd_subs/payment_failed/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );

        $this->inlineTranslation->suspend();

        $template = $this->scopeConfig->getValue(
            'iwd_subs/billing_failed/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );

        $copyTo = $this->getEmails('iwd_subs/billing_failed/copy_to', $subscription->getStoreId());
        $copyMethod = $this->scopeConfig->getValue(
            'iwd_subs/billing_failed/copy_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );
        $bcc = [];
        if ($copyTo && $copyMethod == 'bcc') {
            $bcc = $copyTo;
        }

        $_receiver = $this->scopeConfig->getValue(
            'iwd_subs/billing_failed/receiver',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );
        $sendTo = [
            [
                'email' => $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $subscription->getStoreId()
                ),
                'name' => $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $subscription->getStoreId()
                ),
            ],
        ];

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = ['email' => $email, 'name' => null];
            }
        }

        foreach ($sendTo as $recipient) {
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $subscription->getStoreId(),
                ]
            )->setTemplateVars(
                [
                    'subscription' => $subscription,
                    'amount' => $this->getFormattedSubtotal($subscription),
                    'reason' => $message,
                    'dateAndTime' => $this->localeDate->formatDateTime(
                        new \DateTime(),
                        \IntlDateFormatter::MEDIUM,
                        \IntlDateFormatter::MEDIUM
                    ),
                    'paymentFailure' => $paymentFailed === true && $paymentFailedActive == 1 ? true : false,
                ]
            )->setFrom(
                $this->scopeConfig->getValue(
                    'iwd_subs/billing_failed/identity',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $subscription->getStoreId()
                )
            )->addTo(
                $recipient['email'],
                $recipient['name']
            )->addBcc(
                $bcc
            )->getTransport();

            $transport->sendMessage();
        }

        $this->inlineTranslation->resume();

        return $this;
    }

    public function sendOrderGenerationFailedEmail(
        \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription,
        $message
    ) {
        $active = $this->scopeConfig->getValue(
            'iwd_subs/generation_failed/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );

        if ($active != 1) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $template = $this->scopeConfig->getValue(
            'iwd_subs/generation_failed/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );

        $copyTo = $this->getEmails('iwd_subs/generation_failed/copy_to', $subscription->getStoreId());
        $copyMethod = $this->scopeConfig->getValue(
            'iwd_subs/generation_failed/copy_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );
        $bcc = [];
        if ($copyTo && $copyMethod == 'bcc') {
            $bcc = $copyTo;
        }

        $_receiver = $this->scopeConfig->getValue(
            'iwd_subs/generation_failed/receiver',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );
        $sendTo = [
            [
                'email' => $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $subscription->getStoreId()
                ),
                'name' => $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $subscription->getStoreId()
                ),
            ],
        ];

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = ['email' => $email, 'name' => null];
            }
        }

        foreach ($sendTo as $recipient) {
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $subscription->getStoreId(),
                ]
            )->setTemplateVars(
                [
                    'subscription' => $subscription,
                    'amount' => $this->getFormattedSubtotal($subscription),
                    'reason' => $message,
                    'dateAndTime' => $this->localeDate->formatDateTime(
                        new \DateTime(),
                        \IntlDateFormatter::MEDIUM,
                        \IntlDateFormatter::MEDIUM
                    ),
                ]
            )->setFrom(
                $this->scopeConfig->getValue(
                    'iwd_subs/generation_failed/identity',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $subscription->getStoreId()
                )
            )->addTo(
                $recipient['email'],
                $recipient['name']
            )->addBcc(
                $bcc
            )->getTransport();

            $transport->sendMessage();
        }

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Send payment failure email to customer
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @param string $message
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendPaymentFailedEmail(
        \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription,
        $message
    ) {
        $active = $this->scopeConfig->getValue(
            'iwd_subs/payment_failed/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );

        if ($active != 1) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $template = $this->scopeConfig->getValue(
            'iwd_subs/payment_failed/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );

        $copyTo = $this->getEmails('iwd_subs/payment_failed/copy_to', $subscription->getStoreId());
        $copyMethod = $this->scopeConfig->getValue(
            'iwd_subs/payment_failed/copy_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $subscription->getStoreId()
        );
        $bcc = [];
        if ($copyTo && $copyMethod == 'bcc') {
            $bcc = $copyTo;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $subscription->getQuote();

        $sendTo = [
            [
                'email' => $quote->getCustomerEmail(),
                'name' => $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname(),
            ],
        ];

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = ['email' => $email, 'name' => null];
            }
        }

        foreach ($sendTo as $recipient) {
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $subscription->getStoreId(),
                ]
            )->setTemplateVars(
                [
                    'subscription' => $subscription,
                    'amount' => $this->getFormattedSubtotal($subscription),
                    'reason' => $message,
                    'dateAndTime' => $this->localeDate->formatDateTime(
                        new \DateTime(),
                        \IntlDateFormatter::MEDIUM,
                        \IntlDateFormatter::MEDIUM
                    ),
                ]
            )->setFrom(
                $this->scopeConfig->getValue(
                    'iwd_subs/payment_failed/identity',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $subscription->getStoreId()
                )
            )->addTo(
                $recipient['email'],
                $recipient['name']
            )->addBcc(
                $bcc
            )->getTransport();

            $transport->sendMessage();
        }

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Get email addresses from the given config path.
     *
     * @param string $configPath
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId
     * @return array|false
     */
    protected function getEmails($configPath, $storeId)
    {
        $data = $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!empty($data)) {
            return explode(',', $data);
        }

        return false;
    }

    /**
     * Get the formatted subscription subtotal.
     *
     * @param \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription
     * @return string
     */
    public function getFormattedSubtotal(\IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription)
    {
        /** @var \IWD\BluePaySubs\Model\Subscription $subscription */
        $currency = $subscription->getData('quote_currency_code');

        if (!isset($this->currencies[$currency])) {
            $this->currencies[$currency] = $this->currencyFactory->create();
            $this->currencies[$currency]->load($currency);
        }

        return $this->currencies[$currency]->formatTxt($subscription->getAmount());
    }
}
