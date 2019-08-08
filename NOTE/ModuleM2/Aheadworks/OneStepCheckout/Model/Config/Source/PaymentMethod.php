<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Method\Factory as MethodFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class PaymentMethod
 * @package Aheadworks\OneStepCheckout\Model\Config\Source
 */
class PaymentMethod implements OptionSourceInterface
{
    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MethodFactory
     */
    private $paymentMethodFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param PaymentConfig $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param MethodFactory $paymentMethodFactory
     * @param RequestInterface $request
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        ScopeConfigInterface $scopeConfig,
        MethodFactory $paymentMethodFactory,
        RequestInterface $request
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $scopeConfig;
        $this->paymentMethodFactory = $paymentMethodFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [['value' => '', 'label' => ' ']];

        $methods = [];
        $groupRelations = [];
        foreach ($this->getPaymentMethods() as $code => $method) {
            $methods[$code] = $method->getConfigData('title', null);
            $group = $method->getConfigData('group', null);
            if ($group) {
                $groupRelations[$code] = $group;
            }
        }

        $groups = $this->paymentConfig->getGroups();
        foreach ($groups as $code => $title) {
            if (in_array($code, $groupRelations)) {
                $methods[$code] = $title;
            }
        }
        foreach ($methods as $code => $title) {
            $result[$code] = [];
        }
        foreach ($methods as $code => $title) {
            if (isset($groups[$code])) {
                $result[$code]['label'] = $title;
            } elseif (isset($groupRelations[$code])) {
                unset($result[$code]);
                $result[$groupRelations[$code]]['value'][$code] = ['value' => $code, 'label' => $title];
            } else {
                $result[$code] = ['value' => $code, 'label' => $title];
            }
        }

        return $result;
    }

    /**
     * Get payment methods
     *
     * @return MethodInterface[]
     * @throws LocalizedException
     */
    private function getPaymentMethods()
    {
        $result = [];
        $methodsData = $this->getPaymentMethodsConfigData();
        foreach ($methodsData as $code => $data) {
            if (isset($data['active'], $data['model']) && (bool)$data['active']) {
                $paymentMethod = $this->paymentMethodFactory->create($data['model']);
                $paymentMethod->setStore(null);
                $result[$code] = $paymentMethod;
            }
        }
        return $result;
    }

    /**
     * Get payment methods configuration data
     *
     * @return array
     */
    private function getPaymentMethodsConfigData()
    {
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeCode = null;

        $storeId = $this->request->getParam('store');
        $websiteId = $this->request->getParam('website');

        if ($websiteId) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $websiteId;
        } elseif ($storeId) {
            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeCode = $storeId;
        }

        return $this->scopeConfig->getValue('payment', $scopeType, $scopeCode);
    }
}
