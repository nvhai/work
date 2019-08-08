<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model;

use Aheadworks\OneStepCheckout\Api\DataFieldCompletenessLoggerInterface;
use Aheadworks\OneStepCheckout\Api\GuestDataFieldCompletenessLoggerInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestDataFieldCompletenessLogger
 * @package Aheadworks\OneStepCheckout\Model
 */
class GuestDataFieldCompletenessLogger implements GuestDataFieldCompletenessLoggerInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var DataFieldCompletenessLoggerInterface
     */
    private $completenessLogger;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param DataFieldCompletenessLoggerInterface $completenessLogger
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        DataFieldCompletenessLoggerInterface $completenessLogger
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->completenessLogger = $completenessLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function log($cartId, array $fieldCompleteness)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');
        $this->completenessLogger->log($quoteIdMask->getQuoteId(), $fieldCompleteness);
    }
}
