/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'underscore',
    'uiCollection',
    'mageUtils',
    'mage/template',
    'googleapi'
], function ($, _, Collection, utils, mageTemplate) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Aheadworks_OneStepCheckout/ui/grid/chart',
            columnsProvider: 'ns = ${ $.ns }, componentType = columns',
            visible: {},
            statefull: {
                visible: true
            },
            imports: {
                totalColumnsCount: '${ $.columnsProvider }:initChildCount',
                addColumns: '${ $.columnsProvider }:elems',
                rows: '${ $.provider }:data.chart.rows',
                compareEnabled: '${ $.provider }:data.compareEnabled'
            },
            listens: {
                elems: 'drawChart',
                rows: 'drawChart',
                visible: 'updateVisible'
            },
            chartData: '',
            chart: '',
            chartContainerId: '',
            chartContainerIdPrefix: 'aw-report__data_grid-chart-',
            chartTooltipId: '',
            chartTooltipIdPrefix: 'aw-chart-tooltip-template-'
        },

        /**
         * Initializes Listing component
         *
         * @returns {Chart} Chainable
         */
        initialize: function () {
            _.bindAll(this, 'drawChart');

            this._super();

            google.charts.load('current', {'packages': ['corechart']});
            $(window).on('resize', this.drawChart);
            return this;
        },

        /**
         * @inheritdoc
         */
        initConfig: function () {
            this._super();

            this.chartContainerId = this.chartContainerIdPrefix + this.index;
            this.chartTooltipId = this.chartTooltipIdPrefix + this.index;

            return this;
        },

        /**
         * Update displayOnChartAfterLoad for column from current bookmark
         *
         * @returns {Columns} Chainable
         */
        updateVisible: function () {
            if (this.visible == undefined) {
                this.visible = {};
            }
            this.elems().forEach(function (column) {
                if (this.visible[column.index] == undefined) {
                    this.visible[column.index] = column.displayOnChartAfterLoad
                        ? column.displayOnChartAfterLoad
                        : false;
                }
                column.displayOnChartAfterLoad = this.visible[column.index];
            }, this);
            this.drawChart();

            return this;
        },

        /**
         * Adds columns whose visibility can be controlled to the component
         *
         * @param {Array} columns - Elements array that will be added to component
         * @returns {Columns} Chainable
         */
        addColumns: function (columns) {
            if (columns.length == this.totalColumnsCount) {
                var data = utils.copy(this.visible);

                columns = _.filter(columns, function (column) {
                    return column.visibleOnChart && _.indexOf(column.chartIndexes, this.index) != -1;
                }, this);
                this.insertChild(columns);

                this.elems().forEach(function (column) {
                    column.on('visible', this.drawChart);
                    if (this.visible[column.index] == undefined) {
                        data[column.index] = column.displayOnChartAfterLoad ? column.displayOnChartAfterLoad : false;
                    }
                    column.displayOnChartAfterLoad = data[column.index];
                }, this);
                this.set('visible', utils.copy(data));
                this.drawChart();
            }

            return this;
        },

        /**
         * Is draw chart
         *
         * @returns {Boolean}
         */
        isDrawChart: function () {
            if ((this.rows && this.rows.length > 1) && (this.elems() && this.elems().length > 1)) {
                return true;
            }
            return false;
        },

        /**
         * Initialization of chart
         *
         * @returns {Void}
         */
        initChart: function () {
            var self = this,
                chartType = this.source.chartType ? this.source.chartType : 'LineChart';

            this.chartData = new google.visualization.DataTable();
            this.chart = new google.visualization.ChartWrapper({
                chartType: chartType,
                containerId: this.chartContainerId,
                dataTable: this.chartData,
                options: this.getChartOptions(chartType)
            });

            google.visualization.events.addListener(this.chart, 'select', function () {
                self.clickOnChartSeries(self.chart.getChart().getSelection());
            });
        },

        /**
         * Retrieve chart options
         *
         * @param {String} chartType - Сhart type for which you want to receive options
         * @returns {Object}
         */
        getChartOptions: function (chartType) {
            var chartOptions = {
                height: 400,
                pointSize: 10,
                lineWidth: 3,
                vAxis:{
                    minValue: 0, maxValue: 5, gridlines: {count: 9},
                    textStyle: {
                        fontSize: 12
                    }
                },
                hAxis: {
                    textStyle: {
                        fontSize: 11
                    }
                },
                tooltip: {
                    textStyle: {
                        fontSize: 12
                    },
                    isHtml: true
                },
                legend: {
                    textStyle: {
                        fontSize: 13
                    }
                },
                colors: this.getColumnColors()
            };

            if (this.source.chartTitle) {
                chartOptions.title = this.source.chartTitle;
            }

            return chartOptions;
        },

        /**
         * Draw chart
         *
         * @returns {Void}
         */
        drawChart: function () {
            var yPos = window.scrollY,
                xPos = window.scrollX;

            $('#' + this.chartContainerId).html('');
            if (!this.isDrawChart()) {
                return;
            }

            var self = this,
                chartView = {};

            this.initChart();
            this.elems().each(function (column, index) {
                // Visible/hide serie in legend
                column.googleSerie = {};
                if (index > 0) {
                    self.elems()[index - 1].googleSerie.visibleInLegend = column.visible;
                }
                if ((!column.displayOnChartAfterLoad || (column.displayOnChartAfterLoad && !column.visible))
                    && index > 0
                ) {
                    // Hide column
                    column.googleColumn = ({
                        label: column.label,
                        type: column.chartType,
                        calc: function () {
                            return null;
                        }
                    });
                    // Coloring serie to gray and visible/hide in legend
                    self.elems()[index - 1].googleSerie.color = '#cccccc';
                } else {
                    column.googleColumn = index;
                }
                if (self.compareEnabled && index > 0) {
                    // compare col, tooltip col
                    self.chartData.addColumn({'label': column.label, 'type': column.chartType});
                    self.chartData.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}})
                }
                // base col
                self.chartData.addColumn({'label': column.label, 'type': column.chartType});
                if (index > 0) {
                    // tooltip col
                    self.chartData.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}})
                }
            });

            chartView.columns = this.getColumnsForGoogleChart();
            this.chart.setOption('series', this.getSeriesForGoogleChart());
            this.chartData.addRows(this.getChartRows());
            this.chart.setView(chartView);
            this.chart.draw();
            window.scrollTo(xPos, yPos);
        },

        /**
         * Retrieve columns for google chart
         *
         * @returns {Array}
         */
        getColumnsForGoogleChart: function() {
            var self = this,
                columns = [];

            this.elems().forEach(function (column, index) {
                if (self.compareEnabled && index > 0) {
                    if (typeof column.googleColumn == 'number') {
                        var columnIndex = column.googleColumn * 4 - 3;
                        // compare col, tooltip col, base col, tooltip col
                        columns.push(columnIndex);
                        columns.push(columnIndex + 1);
                        columns.push(columnIndex + 2);
                        columns.push(columnIndex + 3);
                    } else {
                        // compare col, tooltip col, base col, tooltip col
                        columns.push(column.googleColumn);
                        columns.push(index * 4 - 2); // tooltip
                        columns.push(column.googleColumn);
                        columns.push(index * 4); // tooltip
                    }
                } else {
                    if (typeof column.googleColumn == 'number' && index > 0) {
                        // base col
                        columns.push(column.googleColumn * 2 - 1);
                    } else {
                        // index col (X)
                        columns.push(column.googleColumn);
                    }
                    if (index > 0) {
                        // tooltip
                        columns.push(index * 2);
                    }
                }
            });
            return columns;
        },

        /**
         * Retrieve series for google chart
         *
         * @returns {Array}
         */
        getSeriesForGoogleChart: function() {
            var self = this,
                series = [];

            this.elems().forEach(function (column, index) {
                if (self.compareEnabled) {
                    // compare serie
                    var compareSerie = utils.copy(column.googleSerie);
                    compareSerie.visibleInLegend = false;
                    compareSerie.lineDashStyle = [2, 2];
                    series.push(compareSerie);
                }
                series.push(column.googleSerie);
            });
            return series;
        },

        /**
         * Retrieve column colors
         *
         * @returns {Array}
         */
        getColumnColors: function() {
            var self = this,
                colors = [];

            this.elems().each(function (column, index) {
                if (index > 0) {
                    if (self.compareEnabled) {
                        colors.push(column.color);
                    }
                    colors.push(column.color);
                }
            });
            return colors;
        },

        /**
         * Retrieve chart rows
         *
         * @returns {Array}
         */
        getChartRows: function() {
            var self = this,
                chartRows = [],
                newRow = [];

            this.rows.forEach(function (row) {
                newRow = [];
                self.elems().forEach(function (column) {
                    if (column.chartType == 'number') {
                        if (self.compareEnabled) {
                            var cIndex = 'c_' + column.index;
                            // compare value
                            newRow.push({
                                'v': parseFloat(row[cIndex]),
                                'f': column.getLabel(row, cIndex)
                            });
                            // tooltip value
                            newRow.push(self._getTooltipContent(
                                    row['c_' + self._getFirstElementIndex()],
                                    column.label,
                                    column.getLabel(row, cIndex)
                            ));
                        }
                        // base value
                        newRow.push({'v': parseFloat(row[column.index]), 'f': column.getLabel(row)});
                        // tooltip value
                        newRow.push(self._getTooltipContent(
                            row[self._getFirstElementIndex()],
                            column.label,
                            column.getLabel(row)
                        ));
                    } else {
                        newRow.push(column.getLabel(row));
                    }
                });
                chartRows.push(newRow);
            });
            return chartRows;
        },

        /**
         * Get first element index name (x-axis)
         *
         * @returns {String}
         * @private
         */
        _getFirstElementIndex: function() {
            return this.elems()[0].index;
        },

        /**
         * Get tooltip content
         *
         * @param {String} period
         * @param {String} description
         * @param {String} value
         * @returns {String}
         * @private
         */
        _getTooltipContent: function(period, description, value) {
            if (typeof period != 'string') {
                period = '';
            }
            if (typeof description != 'string') {
                description = '';
            }
            if (typeof value != 'string') {
                value = '';
            }

            var style = '';
            if (period.length > 22 || (description.length + value.length) > 22) {
                style = '-wide';
            } else if (period.length <= 12 && (description.length + value.length) <= 12) {
                style = '-small';
            }

            var tooltipTmpl = mageTemplate('#' + this.chartTooltipId);
            return tooltipTmpl({
                data: {
                    style: style,
                    period: period,
                    description: description,
                    value: value
                }
            });
        },

        /**
         * Click on chart series
         *
         * @private
         * @returns {void}
         */
        clickOnChartSeries: function(sel) {
            if (sel.length > 0) {
                // If row is null, then clicked on the legend
                if (sel[0].row === null) {
                    var index = sel[0].column,
                        data = utils.copy(this.visible);

                    if (this.compareEnabled && index > 1) {
                        // Two lines with two tooltips (4 columns)
                        index = Math.ceil(index / 4);
                    } else {
                        // One line with tooltip (2 columns)
                        index = Math.ceil(index / 2);
                    }

                    // If hide column
                    if (this.elems()[index].googleColumn == index) {
                        this.elems()[index].displayOnChartAfterLoad = false;
                    } else {
                        this.elems()[index].displayOnChartAfterLoad = true;
                    }

                    data[this.elems()[index].index] = this.elems()[index].displayOnChartAfterLoad;
                    this.set('visible', utils.copy(data));
                }
            }
        }
    });
});
