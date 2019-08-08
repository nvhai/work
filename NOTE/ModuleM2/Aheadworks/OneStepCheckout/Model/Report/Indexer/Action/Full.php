<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Report\Indexer\Action;

use Aheadworks\OneStepCheckout\Model\Report\Indexer\IndexerList;

/**
 * Class Full
 * @package Aheadworks\OneStepCheckout\Model\Report\Indexer\Action
 */
class Full
{
    /**
     * @var IndexerList
     */
    private $indexerList;

    /**
     * @param IndexerList $indexerList
     */
    public function __construct(IndexerList $indexerList)
    {
        $this->indexerList = $indexerList;
    }

    /**
     * Execute Full reindex
     *
     * @return void
     */
    public function execute()
    {
        foreach ($this->indexerList->getIndexers() as $indexer) {
            $indexer->reindexAll();
        }
    }
}
