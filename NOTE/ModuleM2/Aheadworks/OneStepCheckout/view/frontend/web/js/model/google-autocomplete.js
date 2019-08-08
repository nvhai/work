/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'Magento_Ui/js/lib/view/utils/async',
        'underscore',
        'mageUtils',
        'uiRegistry',
        'Aheadworks_OneStepCheckout/js/view/form/element/validation-enabled-flag',
        'https://maps.googleapis.com/maps/api/js?key='
            + window.checkoutConfig.googleAutocomplete.apiKey + '&libraries=places'
    ],
    function ($, _, utils, registry, validationEnabledFlag) {
        'use strict';

        var dataMap = {
            'street_number': [
                {
                    source: 'long_name',
                    dest: 'street',
                    index: 'street_num'
                }
            ],
            'route': [
                {
                    source: 'long_name',
                    dest: 'street',
                    index: 'street'
                }
            ],
            'locality': [
                {
                    source: 'long_name',
                    dest: 'city'
                }
            ],
            'administrative_area_level_1': [
                {
                    source: 'long_name',
                    dest: 'region'
                },
                {
                    source: 'short_name',
                    dest: 'region_id'
                }
            ],
            'country': [
                {
                    source: 'short_name',
                    dest: 'country_id'
                }
            ],
            'postal_code': [
                {
                    source: 'long_name',
                    dest: 'postcode'
                }
            ]
        },
            regionMap = [];

        /**
         * On place change event handler
         *
         * @param {string} id
         * @param {Object} autoCompleteObject
         */
        function onPlaceChanged(id, autoCompleteObject) {
            var place = autoCompleteObject.getPlace(),
                addressData = collectAddressData(place.address_components),
                fieldsToReset = getFieldsToReset(),
                element = $('#' + id),
                scopeId = element.data('autocomplete-scope'),
                checkoutProvider = registry.get('checkoutProvider'),
                regionMapItem,

                /**
                 * Set address data
                 *
                 * @param {string} key
                 * @param {string} index
                 */
                setAddressData = function (key, index) {
                    var path = scopeId + '.' + key + (index ? '.' + index : '');

                    if (_.has(addressData, key)) {
                        checkoutProvider.set(path, addressData[key]);
                        addressData = _.omit(addressData, key);
                    }
                };

            validationEnabledFlag(false);
            $.each(fieldsToReset, function () {
                var path = scopeId + '.' + this;

                if (this == 'street') {
                    path = path + '.0';
                }
                checkoutProvider.set(path, '');
            });
            validationEnabledFlag(true);

            prepareStreet(addressData, place);

            if (_.has(addressData, 'region_id')) {
                if (_.has(addressData, 'country_id')) {
                    regionMapItem = _.find(regionMap, function (mapItem) {
                        return mapItem['code'] == addressData['region_id']
                            && mapItem['countryId'] == addressData['country_id'];
                    });
                }
                if (!_.isUndefined(regionMapItem)) {
                    addressData['region_id'] = regionMapItem.id;
                } else {
                    addressData = _.omit(addressData, 'region_id');
                }
            }

            setAddressData('country_id');
            setAddressData('street', '0');

            $.each(addressData, function (index) {
                setAddressData(index);
            });

            $(':mage-awOscFloatLabel input').trigger('awOscForceRefresh');
        }

        /**
         * Collect address data
         *
         * @param {Array} addressComponents
         * @returns {Object}
         */
        function collectAddressData(addressComponents) {
            var result = {};

            $.each(addressComponents, function () {
                var componentData = this,
                    componentType = componentData.types[0];

                if (_.has(dataMap, componentType)) {
                    $.each(dataMap[componentType], function () {
                        if (_.has(this, 'index')) {
                            if (!_.has(result, this.dest)) {
                                result[this.dest] = {};
                            }
                            result[this.dest][this.index] = componentData[this.source];
                        } else {
                            result[this.dest] = componentData[this.source];
                        }
                    });
                }
            });

            return result;
        }

        /**
         * Get fields to reset
         *
         * @returns {Array}
         */
        function getFieldsToReset() {
            var result = [];

            $.each(dataMap, function () {
                $.each(this, function () {
                    if (_.indexOf(result, this.dest) == -1) {
                        result.push(this.dest);
                    }
                });
            });

            return result;
        }

        /**
         * Prepare street string
         *
         * @param {Object} addressData
         * @param {Object} place
         */
        function prepareStreet(addressData, place) {
            var template;

            if (place.types && place.types[0] == 'street_address') {
                addressData['street'] = place.name;
            } else if (_.has(addressData, 'street')) {
                template = evaluateStreetAddressFormat(addressData['street'], place.formatted_address);
                addressData['street'] = template(addressData['street']);
            } else {
                addressData['street'] = '';
            }
        }

        /**
         * Evaluate street address format (raw logic)
         *
         * @param {Array} streetData
         * @param {string} formattedAddress
         * @returns {Function|null}
         */
        function evaluateStreetAddressFormat(streetData, formattedAddress) {
            var templateStrParts = {},
                templateStr = '',
                regExp,
                isStreetNumFirst = false;

            if (_.has(streetData, 'street_num')) {
                templateStrParts.streetNum = '<%= street_num %>';
                regExp = new RegExp('^' + streetData['street_num']);
                if (formattedAddress.match(regExp)) {
                    isStreetNumFirst = true;
                    templateStr = templateStr + templateStrParts.streetNum;
                }
            }
            if (_.has(streetData, 'street')) {
                templateStrParts.street = '<%= street %>';
                if (isStreetNumFirst) {
                    templateStr = templateStr + ' ' + templateStrParts.street;
                } else {
                    templateStr = templateStr + templateStrParts.street;
                }
            }
            if (_.has(templateStrParts, 'streetNum') && !isStreetNumFirst) {
                templateStr = templateStr + ' ' + templateStrParts.streetNum;
            }

            return _.template(templateStr);
        }

        return {

            /**
             * Init autocomplete
             *
             * @param {string} selector
             */
            init: function (selector) {
                $.async(
                    selector,
                    function (element) {
                        var id = $(element).attr('id') || utils.uniqueid(),
                            autoCompleteObject;

                        if (!$(element).attr('id')) {
                            $(element).attr('id', id);
                        }
                        autoCompleteObject = new google.maps.places.Autocomplete(
                            element,
                            {types: ['address']}
                        );
                        autoCompleteObject.addListener('place_changed', function () {
                            onPlaceChanged(id, autoCompleteObject);
                        });
                        $(element).attr('placeholder', '');
                    }
                );
            },

            /**
             * Set region map
             *
             * @param {Array} map
             */
            setRegionMap: function (map) {
                regionMap = map;
            }
        };
    }
);
