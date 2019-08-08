<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Report;

use Aheadworks\OneStepCheckout\Model\Report\Indexer\Action\Full as FullAction;
use Aheadworks\OneStepCheckout\Model\Report\Indexer\Action\FullFactory as FullActionFactory;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

/**
 * Class Indexer
 * @package Aheadworks\OneStepCheckout\Model\Report
 */
class Indexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var FullActionFactory
     */
    private $fullActionFactory;

    /**
     * @param FullActionFactory $fullActionFactory
     */
    public function __construct(
        FullActionFactory $fullActionFactory
    ) {
        $this->fullActionFactory = $fullActionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        /** @var FullAction $action */
        $action = $this->fullActionFactory->create();
        $action->execute();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeList(array $ids)
    {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeRow($id)
    {
    }
}
