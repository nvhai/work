<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Model\Config\Initial;

use Aheadworks\OneStepCheckout\Model\Serialize\PhpSerialize;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\App\Config\Initial\Converter;
use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Exploder
 * @package Aheadworks\OneStepCheckout\Model\Config\Initial
 */
class Exploder
{
    /**
     * Cache Id prefix
     */
    const CACHE_ID_PREFIX = 'aw_osc_initial_config_exploder';

    /**
     * File name
     */
    const FILE_NAME = 'config.xml';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var FileResolverInterface
     */
    private $fileListResolver;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ValidationStateInterface
     */
    private $validationState;

    /**
     * @var PhpSerialize
     */
    private $serializer;

    /**
     * @param CacheInterface $cache
     * @param FileResolverInterface $fileListResolver
     * @param Converter $converter
     * @param ValidationStateInterface $validationState
     * @param PhpSerialize $serializer
     */
    public function __construct(
        CacheInterface $cache,
        FileResolverInterface $fileListResolver,
        Converter $converter,
        ValidationStateInterface $validationState,
        PhpSerialize $serializer
    ) {
        $this->cache = $cache;
        $this->fileListResolver = $fileListResolver;
        $this->converter = $converter;
        $this->validationState = $validationState;
        $this->serializer = $serializer;
    }

    /**
     * Retrieve config value by path and explode its value bypassing core merge logic
     *
     * @param string $path
     * @return array
     * @throws LocalizedException
     */
    public function explodeByPath($path)
    {
        $cacheId = self::CACHE_ID_PREFIX . '-' . $path;
        $data = $this->cache->load($cacheId);
        if ($data === false) {
            $data = $this->read($path);
            $this->cache->save($this->serializer->serialize($data), $cacheId);
        } else {
            $data = $this->serializer->unserialize($data);
        }
        return $data;
    }

    /**
     * Read data
     *
     * @param string $path
     * @return array
     * @throws LocalizedException
     */
    private function read($path)
    {
        $result = [];
        $pathParts = explode('/', $path);
        $fileList = $this->fileListResolver->get(self::FILE_NAME, null);
        foreach ($fileList as $key => $content) {
            try {
                $dom = new Dom($content, $this->validationState);
            } catch (ValidationException $e) {
                throw new LocalizedException(__('Invalid XML in file %1:\n%2', $key, $e->getMessage()));
            }

            $output = $this->converter->convert($dom->getDom());
            if (isset($output['data']['default'])) {
                $data = $this->getDataByPathParts($output['data']['default'], $pathParts);
                if ($data !== null) {
                    $result[] = $data;
                }
            }
        }
        return $result;
    }

    /**
     * Get data by path parts
     *
     * @param array $data
     * @param array $pathParts
     * @return mixed|null
     */
    private function getDataByPathParts($data, $pathParts)
    {
        foreach ($pathParts as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof DataObject) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }
        return $data;
    }
}
