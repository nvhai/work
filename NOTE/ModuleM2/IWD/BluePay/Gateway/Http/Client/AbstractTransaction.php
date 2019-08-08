<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Gateway\Http\Client;

use IWD\BluePay\Api\AdapterInterface;
use IWD\BluePay\Model\Adapter\BluePayAdapterFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractTransaction
 */
abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $customLogger;

    /**
     * @var BluePayAdapterFactory
     */
    protected $adapterFactory;

    /**
     * System event manager
     *
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     * @param BluePayAdapterFactory $adapterFactory
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        BluePayAdapterFactory $adapterFactory,
        ManagerInterface $eventManager
    ) {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapterFactory = $adapterFactory;
        $this->_eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $adapter = $this->adapterFactory->create();
            $this->_eventManager->dispatch(
                'iwd_bluepay_transaction_process_before',
                ['adapter' => $adapter, 'data' => $data]
            );

            $response['object'] = $this->process($adapter, $data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            $this->logger->critical($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array) $response['object'];
            $this->customLogger->debug($log);
        }

        return $response;
    }

    /**
     * @param AdapterInterface $adapter
     * @param array $data
     * @return \IWD\BluePay\Api\AdapterResponseInterface
     */
    protected function process(AdapterInterface $adapter, array $data)
    {
        $this->prepare($adapter, $data);

        return $adapter->process()->getResponse();
    }

    /**
     * Prepare http request
     *
     * @param \IWD\BluePay\Api\AdapterInterface $adapter
     * @param array $data
     * @return $this
     */
    abstract protected function prepare(\IWD\BluePay\Api\AdapterInterface $adapter, array $data);
}
