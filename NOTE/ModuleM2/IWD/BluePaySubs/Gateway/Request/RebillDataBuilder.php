<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Gateway\Request;

use IWD\BluePay\Gateway\Config\Config;
use IWD\BluePay\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class RebillDataBuilder implements BuilderInterface
{
    /**
     * Rebill info block
     */
    const REBILL = 'rebill_info';

    /**
     * LAST TRANS ID
     */
    const LAST_TRANS_ID = 'last_trans_id';

    const LAST_TRANS_DATE = 'last_trans_date';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        if ($transId = $payment->getAdditionalInformation(self::LAST_TRANS_ID)) {
            $result = [
                self::REBILL => [
                    self::LAST_TRANS_ID => $transId,
                    self::LAST_TRANS_DATE => $payment->getAdditionalInformation(self::LAST_TRANS_DATE)
                ],
            ];
        }

        return $result;
    }
}
