<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Plugin\Payment\Model\Method;

use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;

class Adapter
{
    const CAN_INITIALIZE_PAYMENT = 'can_initialize';

    /**
     * @inheritdoc
     */
    public function afterIsInitializeNeeded(MethodInterface $subject, $result)
    {
        return $result ?: $this->canInitialize($subject);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundInitialize(MethodInterface $subject, callable $proceed, $paymentAction, $stateObject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $subject->getInfoInstance();
        $transId = $payment->getAdditionalInformation('last_trans_id');
        if (empty($transId) || !$this->canInitialize($subject)) {
            return $proceed($paymentAction, $stateObject);
        }

        $order = $payment->getOrder();
        $orderState = Order::STATE_PROCESSING;

        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
        $payment->capture(null);

        $orderState = $order->getState() ? $order->getState() : $orderState;

        $stateObject->setData('state', $orderState);
        $stateObject->setData('status', $order->getStatus());

        return $subject;
    }

    /**
     * Custom verification initialize payment
     *
     * @param MethodInterface $method
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function canInitialize(MethodInterface $method)
    {
        try {
            $result = (bool)(int)$method->getInfoInstance()
                ->getAdditionalInformation(self::CAN_INITIALIZE_PAYMENT);
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }
}