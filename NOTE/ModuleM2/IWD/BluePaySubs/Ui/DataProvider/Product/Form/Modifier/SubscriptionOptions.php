<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Ui\DataProvider\Product\Form\Modifier;

use IWD\BluePaySubs\Setup\UpgradeData;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use IWD\BluePay\Helper\Json;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;

/**
 * SubscriptionOptions Class
 */
class SubscriptionOptions extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    const SUBS_OPTIONS_INFO = 'container_subs_options_info';
    const SUBS_OPTIONS_GRID = 'container_subs_options_grid';

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \IWD\BluePaySubs\Model\Source\Period
     */
    protected $periodSource;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * SubscriptionIntervals constructor.
     *
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     * @param \IWD\BluePaySubs\Model\Source\Period $periodSource
     */
    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        \IWD\BluePaySubs\Model\Source\Period $periodSource,
        Json $serializer
    ) {
        $this->locator      = $locator;
        $this->arrayManager = $arrayManager;
        $this->periodSource = $periodSource;
        $this->serializer = $serializer;
    }

    /**
     * Modify produt data for form.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        if ($this->locator->getProduct()->getId() < 1) {
            return $data;
        }

        $this->unsetSubscriptionCustomOption($data);
        $intervalsData = $this->getIntervalsData();

        return array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        self::SUBS_OPTIONS_GRID => $intervalsData,
                    ],
                ],
            ]
        );
    }

    /**
     * Unset subscription custom product option
     *
     * @param array $data
     * @return $this
     */
    protected function unsetSubscriptionCustomOption(array & $data)
    {
        $product = $this->locator->getProduct();
        if(empty($data[$product->getId()][static::DATA_SOURCE_DEFAULT][CustomOptions::GRID_OPTIONS_NAME])) {
            return $this;
        }

        $options = & $data[$product->getId()][static::DATA_SOURCE_DEFAULT][CustomOptions::GRID_OPTIONS_NAME];
        foreach ($options as $k => $option) {
            if(!empty($option['option_id']) &&
                $option['option_id'] == $product->getData(UpgradeData::SUBS_PRODUCT_OPTION_ID)) {
                unset($options[$k]);
            }
        }

        return $this;
    }

    /**
     * Get intervals data for grid.
     *
     * @return array
     */
    public function getIntervalsData()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->locator->getProduct();
        $optionsData = [];
        if (!empty($product->getData('subs_options'))) {
            $optionsData = $this->serializer->unserialize($product->getData('subs_options'));
            $optionsData = is_array($optionsData) ? $optionsData : [];
        }

        /**
         * Check defaults -- distill values among interval nulls.
         */
        foreach ($optionsData as $k => $intervalData) {
            if (isset($optionsData[ $k ]['price'])) {
                $optionsData[ $k ]['price'] = $this->coercePrecision(
                    $optionsData[ $k ]['price']
                );
            }
        }

        return $optionsData;
    }

    /**
     * Modify product form for subscription management. Assumes subscription attributes are assigned to attr set.
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $attributePath = $this->arrayManager->findPath(
            'container_subs_options',
            $meta,
            null,
            'children'
        );
        if ($attributePath === null) {
            return $meta;
        }
        $containerPath = substr(
            $attributePath,
            0,
            strrpos($attributePath, ArrayManager::DEFAULT_PATH_DELIMITER)
        );
        $container = $this->arrayManager->get($containerPath, $meta);
        $customField = [
            self::SUBS_OPTIONS_INFO => $this->getOptionsInfo(),
            self::SUBS_OPTIONS_GRID => $this->getOptionsGrid(),
        ];
        $container += $customField;

        $meta = $this->arrayManager->replace(
            $containerPath,
            $meta,
            $container
        );

        /**
         * Remove original attribute to avoid confusion
         */
        $meta = $this->removeTextOption($meta);

        return $meta;
    }

    /**
     * Get the intervals input grid definition.
     *
     * @param int $sortOrder
     * @return array
     */
    public function getOptionsGrid($sortOrder = 1001)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Option'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => 'is_delete',
                        'deleteValue' => true,
                        'defaultRecord' => false,
                        'sortOrder' => $sortOrder,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => $this->getIntervalColumns(),
                ],
            ],
        ];
    }

    /**
     * Get currency symbol for price fields. We assume it'll always be before the price, same as core.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->locator->getStore();

        return $store->getBaseCurrency()->getCurrencySymbol();
    }

    /**
     * Get intervals grid documentation blurb (explain fields/usage).
     *
     * @param int $sortOrder
     * @return array
     */
    public function getOptionsInfo($sortOrder = 1000)
    {
        $hint = '<b style="color: #e22626">*</b> ';
        $content[] = $hint .__('<b>Period Interval</b> greater than zero (required)');
        $content[] = $hint . __('<b>Cycles Count</b> might be empty (endlessly use) & not include first time purchase');
        $content[] = $hint . __('Empty <b>Price</b> means to use standard product pricing.');

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                    ],
                ],
            ],
            'children' => [
                'subscription_options_info' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Subscription Options'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'template' => 'ui/form/components/complex',
                                'content' => implode('<br />', $content),
                                'sortOrder' => $sortOrder,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the interval columns.
     *
     * @return array
     */
    public function getIntervalColumns()
    {
        return [
            'period_interval' => $this->getPeriodInterval(),
            'period' => $this->getPeriod(),
            'cycles' => $this->getCycles(),
            'price' => $this->getPrice(),
            'actionDelete' => $this->getDeleteAction(),
        ];
    }

    /**
     * Get period intervals
     *
     * @param int $sortOrder
     * @return array
     */
    public function getPeriodInterval($sortOrder = 10)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Period Interval'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => 'period_interval',
                        'dataType' => Number::NAME,
                        'addbefore' => __('Every'),
                        'placeholder' => '',
                        'validation' => [
                            'validate-no-empty' => true,
                            'validate-digits-range' => true,
                            'validate-integer' => true,
                            'validate-greater-than-zero' => true,
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get period
     *
     * @param int $sortOrder
     * @return array
     */
    public function getPeriod($sortOrder = 20)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => ' ',
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataType' => Text::NAME,
                        'dataScope' => 'period',
                        'options' => $this->periodSource->toOptionArrayPlural(),
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get cycles
     *
     * @param int $sortOrder
     * @return array
     */
    public function getCycles($sortOrder = 30)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Cycles Count'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => 'cycles',
                        'dataType' => Number::NAME,
                        'placeholder' => '∞',
                        'addafter' => 'times',
                        'validation' => [
                            'validate-greater-than-zero' => true,
                            'validate-digits-range' => true,
                            'validate-integer' => true,
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the 'installment price' column definition.
     *
     * @param int $sortOrder
     * @return array
     */
    public function getPrice($sortOrder = 40)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataType' => Price::NAME,
                        'label' => __('Price'),
                        'enableLabel' => true,
                        'dataScope' => 'price',
                        'addbefore' => __(
                            '%1',
                            $this->getCurrencySymbol()
                        ),
                        'placeholder' => '',
                        'validation' => [
                            'validate-number' => true,
                        ],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the 'delete' column definition.
     *
     * @param int $sortOrder
     * @return array
     */
    public function getDeleteAction($sortOrder = 60)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'actionDelete',
                        'dataType' => Text::NAME,
                        'label' => ' ',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     *
     * @param array $meta
     * @param string $attr
     * @return array
     */
    public function removeTextOption(array $meta, $attr = 'subs_options')
    {
        $meta = $this->arrayManager->remove(
            $this->arrayManager->findPath(
                'container_' . $attr,
                $meta,
                null,
                'children'
            ),
            $meta
        );

        return $meta;
    }

    /**
     * Coerce prices to 2 or 4 decimals depending on the precision actually needed.
     *
     * This is to avoid prices always showing as 0.0000 when there's no need.
     *
     * @param int|float $value
     * @return string
     */
    protected function coercePrecision($value)
    {
        $decimal = (float)($value - floor($value));

        if (strlen($decimal) > 4) {
            return sprintf('%0.4f', $value);
        }

        return sprintf('%0.2f', $value);
    }
}
