<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\DataFieldCompletenessLoggerInterface;
use Aheadworks\OneStepCheckout\Api\Data\DataFieldCompletenessInterface;
use Aheadworks\OneStepCheckout\Model\ResourceModel\FieldCompleteness\Logger;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class DataFieldCompletenessLogger
 * @package Aheadworks\OneStepCheckout\Model
 */
class DataFieldCompletenessLogger implements DataFieldCompletenessLoggerInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param Logger $logger
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        Logger $logger,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->logger = $logger;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function log($cartId, array $fieldCompleteness)
    {
        $logData = [];
        foreach ($fieldCompleteness as $item) {
            $logData[] = $this->dataObjectProcessor->buildOutputDataArray(
                $item,
                DataFieldCompletenessInterface::class
            );
        }
        $this->logger->log($cartId, $logData);
    }
}
