<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Gateway\Http\Client;

use IWD\BluePay\Api;
use IWD\BluePay\Gateway\Http\Client;
use IWD\BluePay\Model\Adapter\BluePayAdapterFactory;
use IWD\BluePaySubs\Api\RebillManagementInterface;
use IWD\BluePaySubs\Gateway\Request\RebillDataBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class TransactionSaleRebill
 */
class TransactionSaleRebill extends Client\TransactionSale
{
    const APPROVED_RESULT = 'Approved Sale';

    /**
     * @var RebillManagementInterface
     */
    protected $rebillManagement;

    /**
     * @inheritDoc
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        BluePayAdapterFactory $adapterFactory,
        ManagerInterface $eventManager,
        RebillManagementInterface $rebillManagement
    ) {
        parent::__construct($logger, $customLogger, $adapterFactory, $eventManager);
        $this->rebillManagement = $rebillManagement;
    }

    /**
     * @inheritDoc
     */
    protected function process(Api\AdapterInterface $adapter, array $data)
    {
        if (isset($data[RebillDataBuilder::REBILL])) {
            $rebillInfo = $data[RebillDataBuilder::REBILL];
            if (!empty($rebillInfo[RebillDataBuilder::LAST_TRANS_ID])) {
                $response = $this->rebillManagement->getRebillByTransaction(
                    $rebillInfo[RebillDataBuilder::LAST_TRANS_ID],
                    $rebillInfo[RebillDataBuilder::LAST_TRANS_DATE]
                );

                return $response;
            }
        }

        return parent::process($adapter, $data);
    }
}
