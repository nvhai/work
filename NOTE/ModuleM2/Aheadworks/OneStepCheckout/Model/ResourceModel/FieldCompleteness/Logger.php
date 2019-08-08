<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\FieldCompleteness;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Logger
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\FieldCompleteness
 */
class Logger
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $table = 'aw_osc_checkout_data_completeness';

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Log data into database
     *
     * @param int $cartId
     * @param array $data
     * @return void
     */
    public function log($cartId, array $data)
    {
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $tableName = $this->resource->getTableName($this->table);

        $connection->delete($tableName, ['quote_id = ?' => $cartId]);

        foreach ($data as &$item) {
            $item['quote_id'] = $cartId;
            if (!isset($item['scope'])) {
                $item['scope'] = null;
            }
        }

        $connection->insertMultiple($tableName, $data);
    }
}
