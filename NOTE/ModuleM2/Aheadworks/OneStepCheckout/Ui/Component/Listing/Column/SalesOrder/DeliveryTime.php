<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui\Component\Listing\Column\SalesOrder;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class DeliveryTime
 * @package Aheadworks\OneStepCheckout\Ui\Component\Listing\Column\SalesOrder
 */
class DeliveryTime extends Column
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->timezone = $timezone;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $deliveryDateFrom = $item['aw_delivery_date_from'];
                $deliveryDateTo = $item['aw_delivery_date_to'];

                if ($deliveryDateFrom && $deliveryDateTo) {
                    $fromTimeFormatted = $this->timezone->formatDateTime(
                        $deliveryDateFrom,
                        \IntlDateFormatter::NONE,
                        \IntlDateFormatter::SHORT
                    );
                    $toTimeFormatted = $this->timezone->formatDateTime(
                        $deliveryDateTo,
                        \IntlDateFormatter::NONE,
                        \IntlDateFormatter::SHORT
                    );
                    $item[$this->getName()] = $fromTimeFormatted . ' - ' . $toTimeFormatted;
                }
            }
        }
        return $dataSource;
    }
}
