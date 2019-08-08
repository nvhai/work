<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Api;

/**
 * Interface SubsAdapterResponseInterface
 * @package IWD\BluePay\Api
 */
interface SubsAdapterResponseInterface extends \IWD\BluePay\Api\AdapterResponseInterface
{
    const REBILL_ID = 'rebill_id';

    const CYCLES = 'REB_CYCLES';

    const CYCLES_REMAIN = 'cycles_remain';

    const REBILLING_FIRST_DATE = 'REB_FIRST_DATE';

    const LAST_DATE = 'last_date';

    const NEXT_DATE = 'next_date';

    const STATUS = 'status';

    const AMOUNT = 'reb_amount';

    const SCHED_EXPR = 'sched_expr';

    const TEMPLATE_ID = 'template_id';

    /**
     * @return string
     */
    public function getRebillId();

    /**
     * @return string
     */
    public function getCycles();

    /**
     * @return string
     */
    public function getCyclesRemain();

    /**
     * @return string
     */
    public function getRebillingFirstDate();

    /**
     * @return string
     */
    public function getLastDate();

    /**
     * @return string
     */
    public function geNextDate();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getAmount();

    /**
     * @return string
     */
    public function getSchedExpr();

    /**
     * @return string
     */
    public function getTemplateId();
}
