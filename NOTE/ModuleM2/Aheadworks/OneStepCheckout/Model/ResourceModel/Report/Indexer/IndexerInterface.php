<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Indexer;

/**
 * Interface IndexerInterface
 * @package Aheadworks\OneStepCheckout\Model\ResourceModel\Report\Indexer
 */
interface IndexerInterface
{
    /**
     * Reindex all
     *
     * @return $this
     */
    public function reindexAll();
}
