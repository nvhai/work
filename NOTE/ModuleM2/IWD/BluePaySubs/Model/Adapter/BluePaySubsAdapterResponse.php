<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Adapter;

use IWD\BluePaySubs\Api\SubsAdapterResponseInterface;
use IWD\BluePay\Model\Adapter\BluePayAdapterResponse;

class BluePaySubsAdapterResponse extends BluePayAdapterResponse implements SubsAdapterResponseInterface
{
    /**
     * @inheritdoc
     */
    public function getRebillId()
    {
        return $this->getData(self::REBILL_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCycles()
    {
        return $this->getData(self::CYCLES);
    }

    /**
     * @inheritdoc
     */
    public function getCyclesRemain()
    {
        return $this->getData(self::CYCLES_REMAIN);
    }

    /**
     * @inheritdoc
     */
    public function getRebillingFirstDate()
    {
        return $this->getData(self::REBILLING_FIRST_DATE);
    }

    /**
     * @inheritDoc
     */
    public function getLastDate()
    {
        return $this->getData(self::LAST_DATE);
    }

    /**
     * @inheritDoc
     */
    public function geNextDate()
    {
        return $this->getData(self::NEXT_DATE);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function getSchedExpr()
    {
        return $this->getData(self::SCHED_EXPR);
    }

    /**
     * @inheritDoc
     */
    public function getTemplateId()
    {
        return $this->getData(self::TEMPLATE_ID);
    }

    /**
     * @inheritdoc
     */
    public function isSuccess()
    {
        return !empty($this->getRebillId());
    }
}