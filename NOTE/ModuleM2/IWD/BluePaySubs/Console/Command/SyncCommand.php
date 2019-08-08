<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Console\Command;

use IWD\BluePaySubs\Model\Source\Status;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    /**
     * Input argument types
     */
    const INPUT_KEY_IDS = 'ids';

    /**
     * @var \IWD\BluePaySubs\Controller\Adminhtml\Subscription\MassSync
     */
    protected $command;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \IWD\BluePaySubs\Model\Service\Subscription
     */
    protected $serviceSubscription;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * SyncCommand constructor.
     * @param \Magento\Framework\App\State $appState
     * @param \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param \IWD\BluePaySubs\Model\Service\Subscription $serviceSubscription
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \IWD\BluePaySubs\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory,
        \IWD\BluePaySubs\Model\Service\Subscription $serviceSubscription,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct();

        $this->appState = $appState;
        $this->collectionFactory = $collectionFactory;
        $this->serviceSubscription = $serviceSubscription;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_KEY_IDS,
            InputArgument::IS_ARRAY,
            'Space-separated list of subscription ids.'
        );
        $this->setName('subs:sync')
            ->setDescription('BluePay synchronize command');

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);

        $output->writeln((string)__('Starting synchronization.'));
        $ids = $input->getArgument(self::INPUT_KEY_IDS);
        foreach ($ids as $k => $id) {
            if(!intval($id)) {
                throw new \InvalidArgumentException(
                    "The following requested id are not supported: '" . $id
                    . "'." . PHP_EOL . 'Supported only space-separated list of subscription ids.'
                );
            }
            $ids[$k] = intval($id);
        }
        $startTime = microtime(true);

        $this->massAction($output, $ids);

        $output->writeln((string)__('Total runtime: %1 sec.', (microtime(true) - $startTime)));
    }

    /**
     * Run subscriptions billing (entry point for cron, with active check).
     *
     * @return $this
     */
    public function executeCron()
    {
        $syncActive = (int)$this->scopeConfig->getValue(
            'iwd_subs/general/cron_sync_active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($syncActive) {
            $this->logger->info('Subscription cron generation start');
            $this->massAction();
            $this->logger->info('Subscription cron generation end');
        }

        return $this;
    }

    /**
     * Sync subscriptions
     *
     * @param OutputInterface $output
     * @param array $ids
     * @return $this
     */
    protected function massAction(OutputInterface $output = null, $ids = [])
    {
        try {
            $collection = $this->collectionFactory->create();
            /** @var \IWD\BluePaySubs\Api\Data\SubscriptionInterface $subscription */
            foreach ($collection->getItems() as $subscription) {
                try {
                    if(!empty($ids) && !in_array($subscription->getId(), $ids)) {
                        continue;
                    }
                    if($subscription->getStatus() == Status::STATUS_STOPPED) {
                        continue;
                    }
                    $this->serviceSubscription->synchronize($subscription);
                    $message = (string) __('Subscription #%1 synced success.', $subscription->getId());
                    $output ? $output->writeln($message) : $this->logger->info($message);

                } catch (\Exception $e) {
                    $message = (string) __('Subscription #%1 error: ', $subscription->getId()) . $e->getMessage();
                    $output ? $output->writeln($message) : $this->logger->error($message);
                }
            }
        } catch (\Exception $e) {
            $message = 'Error: '. $e->getMessage();
            $output ? $output->writeln($message) : $this->logger->error($message);
            return $this;
        }

        return $this;
    }
}