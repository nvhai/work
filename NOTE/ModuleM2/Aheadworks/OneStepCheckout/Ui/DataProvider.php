<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\OneStepCheckout\Ui;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiDataProvider;

/**
 * Class DataProvider
 * @package Aheadworks\OneStepCheckout\Ui
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends UiDataProvider
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ScopeCurrency
     */
    private $scopeCurrency;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeCurrency $scopeCurrency
     * @param FormatInterface $localeFormat
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        PriceCurrencyInterface $priceCurrency,
        ScopeCurrency $scopeCurrency,
        FormatInterface $localeFormat,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->priceCurrency = $priceCurrency;
        $this->scopeCurrency = $scopeCurrency;
        $this->localeFormat = $localeFormat;
    }

    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = parent::searchResultToOutput($searchResult);
        $arrItems['totals'] = $this->prepareTotals($searchResult->getTotalsItems());
        $arrItems['conversion_chart']['rows'] = $this->prepareConversionChartRows(
            $searchResult->getChartRows(['conversion'])
        );
        $arrItems['statistics_chart']['rows'] = $this->prepareStatisticsChartRows(
            $searchResult->getChartRows(
                [
                    'abandoned_checkouts_count',
                    'abandoned_checkouts_revenue',
                    'completed_checkouts_count',
                    'completed_checkouts_revenue'
                ]
            )
        );
        $arrItems['priceFormat'] = $this->localeFormat->getPriceFormat(
            null,
            $this->scopeCurrency->getCurrencyCode()
        );
        return $arrItems;
    }

    /**
     * Prepare totals data
     *
     * @param array $totals
     * @return array
     */
    private function prepareTotals($totals)
    {
        $result = [];
        foreach ($totals as $totalRow) {
            $totalRow['abandoned_checkouts_revenue'] = $this->convertPrice($totalRow['abandoned_checkouts_revenue']);
            $totalRow['completed_checkouts_revenue'] = $this->convertPrice($totalRow['completed_checkouts_revenue']);
            $result[] = $totalRow;
        }
        return $result;
    }

    /**
     * Prepare conversion chart rows
     *
     * @param array $rows
     * @return array
     */
    private function prepareConversionChartRows($rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $row['period_from'] = $this->formatDateRange($row['period_from']);
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Prepare statistics chart rows
     *
     * @param array $rows
     * @return array
     */
    private function prepareStatisticsChartRows($rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $row['period_from'] = $this->formatDateRange($row['period_from']);
            $row['abandoned_checkouts_revenue'] = $this->convertPrice($row['abandoned_checkouts_revenue']);
            $row['completed_checkouts_revenue'] = $this->convertPrice($row['completed_checkouts_revenue']);
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Format date range
     *
     * @param string $dateRange
     * @return string
     */
    private function formatDateRange($dateRange)
    {
        $separator = ' - ';
        $formattedParts = [];
        $parts = explode($separator, $dateRange);
        foreach ($parts as $part) {
            $date = new \DateTime($part);
            $formattedParts[] = $date->format('M d, Y');
        }
        return implode($separator, $formattedParts);
    }

    /**
     * Convert price amount into scope currency
     *
     * @param float $amount
     * @return float
     */
    private function convertPrice($amount)
    {
        return $this->priceCurrency->convert(
            $amount,
            null,
            $this->scopeCurrency->getCurrencyCode()
        );
    }
}
