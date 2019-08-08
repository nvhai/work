<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Adapter;

use IWD\BluePaySubs\Api\SubsAdapterResponseInterface;
use IWD\BluePaySubs\Api\SubsAdapterInterface;

class BluePaySubsAdapter extends \IWD\BluePay\Model\Adapter\BluePayAdapter implements SubsAdapterInterface
{

    private $responseMap = [
        BluePaySubsAdapterResponse::REBILL_ID => 'REBILLING_ID'
    ];

    /**
     * @return SubsAdapterResponseInterface | SubsAdapterResponseInterface[]
     */
    public function getResponse()
    {
        // Custom mapping for transaction daily report
        if ($this->api == 'bpdailyreport2') {
            // list($headers, $response) = explode("\r\n\r\n", $this->response, 2);
            $responseValues = explode("\r\n", $this->response);
            $keys = explode('","', trim(array_shift($responseValues), "\""));
            $values = [];
            foreach ($responseValues as $value) {
                if (!empty($value)) {
                    $value = explode(',', $value);
                    $values[] = new BluePaySubsAdapterResponse(array_combine($keys, $value));
                }
            }

            return $values;
        }

        parse_str($this->response, $response);

        // Custom mapping for single transaction query
        if ($this->api == 'stq') {
            if (isset($response['id'])) {
                $response[SubsAdapterResponseInterface::TRANSACTION_ID] = $response['id'];
            }
            $success = SubsAdapterResponseInterface::RESPONSE_CODE_APPROVED;
            if (isset($response['message']) && strpos(strtoupper($response['message']), $success) !== false) {
                $response['Result'] = $success;
            }
            else {
                $response['Result'] = SubsAdapterResponseInterface::RESPONSE_CODE_DECLINED;
            }
            foreach ($response as $key => $val) {
                $response[strtoupper($key)] = $val;
            }
        }

        foreach ($this->responseMap as $map => $field) {
            if (isset($response[$field])) {
                $response[$map] = $response[$field];
            } elseif (isset($response[strtolower($field)])) {
                $response[$map] = $response[strtolower($field)];
            }
        }

        return new BluePaySubsAdapterResponse($response);
    }

    /**
     * @inheritdoc
     */
    public function getRebillStatus($rebillId)
    {
        parent::getRebillStatus($rebillId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRebillingInformation(array $params)
    {
        $this->doRebill = '1';
        $this->rebillFirstDate = $params['rebillFirstDate'];
        $this->rebillExpr = $params['rebillExpression'];
        $this->rebillAmount = $params['rebillAmount'];
        if (isset($params['rebillCycles'])) {
            $this->rebillCycles = $params['rebillCycles'];
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRebillByTransaction(array $params)
    {
        $this->getSingleTransQuery($params);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRebillDailyReport(array $params)
    {
        $this->api = "bpdailyreport2";
        $this->queryBySettlement = '0';
        $this->reportStartDate = $params['reportStart'];
        $this->reportEndDate = $params['reportEnd'];
        $this->subaccountsSearched = $params['subaccountsSearched'];
        if(isset($params["doNotEscape"])) {
            $this->doNotEscape = $params["doNotEscape"];
        }
        if(isset($params["errors"])) {
            $this->excludeErrors = $params["errors"];
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function updateRebill(array $params)
    {
        parent::updateRebill($params);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function updateRebillStatus($rebillId, $status)
    {
        $this->api = "bp20rebadmin";
        $this->transType = "SET";
        $this->rebillStatus = $status;
        $this->rebillID = $rebillId;

        return $this;
    }
}