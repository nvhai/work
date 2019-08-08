<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\Product\Option;

use IWD\BluePaySubs\Setup\InstallData;
use IWD\BluePaySubs\Setup\UpgradeData;
use IWD\BluePaySubs\Ui\DataProvider\Product\Form\Modifier\SubscriptionOptions;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction;
use Magento\Framework\Indexer\ActionInterface;
use IWD\BluePaySubs\Helper\Data as Helper;
use Magento\Framework\App\ProductMetadataInterface;

class Indexer extends AbstractAction implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\Indexer\BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator
     */
    private $batchSizeCalculator;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulator;

    /**
     * @var string
     */
    private $attributeTableName = 'eav_attribute';

    /**
     * BluePay attribute source table
     *
     * @var string
     */
    private $sourceTableName = 'catalog_product_entity_text';

    /**
     * @var string
     */
    private $idFieldName = 'value_id';

    /**
     * @var
     */
    public $productMetadata;

    /**
     * Indexer constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory
     * @param SaveHandler $saveHandler
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     * @param Indexer\BatchProvider|null $batchProvider
     * @param Indexer\BatchSizeCalculator|null $batchSizeCalculator
     * @param Helper|null $helper
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @param \Magento\Store\Model\App\Emulation|null $emulator
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory,
        Indexer\BatchProvider $batchProvider,
        Indexer\BatchSizeCalculator $batchSizeCalculator,
        ProductMetadataInterface $productMetadata,
        SaveHandler $saveHandler = null,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        Helper $helper = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null,
        \Magento\Store\Model\App\Emulation $emulator = null
    )
    {
        parent::__construct($eavDecimalFactory, $eavSourceFactory);
        $this->saveHandler = $saveHandler ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            SaveHandler::class
        );
        $this->productRepository = $productRepository ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $this->metadataPool = $metadataPool ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\EntityManager\MetadataPool::class
        );
        $this->batchProvider = $batchProvider;
        $this->batchSizeCalculator = $batchSizeCalculator;
        $this->productMetadata = $productMetadata;
        $this->helper = $helper ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Helper::class);
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->emulator = $emulator ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Store\Model\App\Emulation::class
        );
    }

    /**
     * @param array|int $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        try {
            $indexerName = 'source';
            $indexer = $this->getIndexer($indexerName);
            $connection = $indexer->getConnection();
            $batches = $this->batchProvider->getBatches(
                $connection,
                $this->sourceTableName,
                $this->idFieldName,
                $this->batchSizeCalculator->estimateBatchSize($connection, $indexerName)
            );

            $attributeId = $this->getAttributeId($connection);
            foreach ($batches as $batch) {
                /** @var \Magento\Framework\DB\Select $select */
                $select = clone $connection->select();
                $select->distinct(true);
                $select->from(['e' => $this->sourceTableName], [$this->getIdColumnName(), 'store_id']);
                $select->where('attribute_id = ?', $attributeId)->order('store_id asc');
                $entities = $this->getBatchIds($connection, $select, $batch);
                if (!empty($entities)) {
                    $this->reindexEntities($entities);
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeFull()
    {
        $this->execute([]);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }

    /**
     * @param array $entities
     * @return $this
     */
    public function reindexEntities(array $entities)
    {
        $moduleActive = $this->helper->moduleIsActive();
        $moduleTitle = $this->helper->getSubscriptionLabel();
        foreach ($entities as $entity) {
            try {
                $product = $this->productRepository->getById($entity[$this->getIdColumnName()], false, $entity['store_id']);
                // If we are not in the correct scope, emulate it to ensure everything comes out correct.
                $emulate = ($this->storeManager->getStore()->getStoreId() !== $entity['store_id']);
                if ($emulate === true) {
                    $this->emulator->startEnvironmentEmulation($entity['store_id']);
                }

                $optionActive = $this->getActiveOption($product);
                $optionExists = !empty($optionActive);
                /**
                 * If subscription module enabled & option not exist (and vise versa)
                 * or subscription module title & option title have different values
                 * trigger beforeBeforeSave plugin @see \IWD\BluePaySubs\Plugin\Catalog\Model\Product
                 */
                if (
                    $optionExists != $moduleActive || ($optionExists && $optionActive->getTitle() != $moduleTitle)
                ) {
                    $productGrid = $this->helper->unserialize(
                        $product->getData(InstallData::SUBS_OPTIONS)
                    );
                    $product->setData(SubscriptionOptions::SUBS_OPTIONS_GRID, $productGrid);
                    // bug https://github.com/magento/magento2/issues/10687
                    $product->setData('media_gallery', null);
                    $this->productRepository->save($product);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($emulate === true) {
            $this->emulator->stopEnvironmentEmulation();
        }

        return $this;
    }

    /**
     * Get subscription option active in product
     *
     * @param ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface|null
     */
    public function getActiveOption(ProductInterface $product)
    {
        $options = $product->getOptions();
        if (!empty($options)) {
            foreach ($options as $k => $option) {
                if ($option->getId() == $product->getData(UpgradeData::SUBS_PRODUCT_OPTION_ID) &&
                    $product->getData(InstallData::SUBS_ACTIVE)) {
                    return $option;
                }
            }
        }

        return null;
    }

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return string
     */
    protected function getAttributeId(\Magento\Framework\DB\Adapter\AdapterInterface $connection)
    {
        $select = clone $connection->select();
        $select->from(['e' => $this->attributeTableName], 'attribute_id')
            ->where('attribute_code = ?', InstallData::SUBS_OPTIONS);

        return $connection->fetchOne($select);
    }

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\DB\Select $select
     * @param array $batch
     * @return array
     */
    protected function getBatchIds(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        \Magento\Framework\DB\Select $select,
        array $batch
    )
    {
        $betweenCondition = sprintf(
            '(%s BETWEEN %s AND %s)',
            $this->idFieldName,
            $connection->quote($batch['from']),
            $connection->quote($batch['to'])
        );

        return $connection->fetchAll($select->where($betweenCondition));
    }

    /**
     * Get Id Column Name based on Magento version
     *
     * @return string
     */
    protected function getIdColumnName()
    {
        $edition = $this->productMetadata->getEdition();
        $idColumnName = 'entity_id';
        if ($edition === 'Enterprise' || $edition === 'B2B') {
            $idColumnName = 'row_id';
        }

        return $idColumnName;
    }
}