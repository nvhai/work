<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Plugin\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use IWD\BluePaySubs\Observer\ProductDeleteAfter as SubsDeleteHelper;

/**
 * Class MassDelete
 * @package IWD\BluePaySubs\Plugin\Catalog\Controller\Adminhtml\Product
 */
class MassDelete
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * MassDelete constructor.
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param SubsDeleteHelper $subsDeleteHelper
     * @param ProductRepositoryInterface|null $productRepository
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        SubsDeleteHelper $subsDeleteHelper,
        ProductRepositoryInterface $productRepository = null
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->subsDeleteHelper = $subsDeleteHelper;
        $this->productRepository = $productRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute($subject, callable $proceed)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productDeleted = 0;
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection->getItems() as $product) {
            if(!empty($subsIds = $this->subsDeleteHelper->getProductSubscriptionIds($product))) {
                $exceptionMessage = __(
                    'Sorry, some of your subscription in use (IDs %1). Please, stop them before delete product (ID #%2)',
                    implode(', ', $subsIds),
                    $product->getId()
                );
                $this->messageManager->addErrorMessage($exceptionMessage);
                continue;
            }
            $this->productRepository->delete($product);
            $productDeleted++;
        }
        if($productDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $productDeleted)
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }
}