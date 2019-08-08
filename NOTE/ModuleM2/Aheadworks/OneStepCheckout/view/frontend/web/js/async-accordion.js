/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'Magento_Ui/js/lib/view/utils/async',
    'collapsible'
], function ($, collapsible) {
    'use strict';

    $.widget('mage.awOscAsyncAccordion', {
        options: {
            active: 0,
            disabled: [],
            openOnFocus: true,
            collapsible: false,
            collapsibleElement: '[data-role=aw-async-collapsible]',
            header: '[data-role=title]',
            content: '[data-role=content]',
            closedState: null,
            openedState: null,
            disabledState: null,
            ajaxUrlElement: '[data-ajax=true]',
            ajaxContent: false,
            loadingClass: null,
            saveState: false,
            animate: false,
            icons: {
                activeHeader: null,
                header: null
            }
        },
        collapsibles: [],

        /**
         * Initialize widget
         */
        _create: function () {
            $.async(
                this.options.collapsibleElement,
                $.proxy(this._initCollapsible, this)
            );
        },

        /**
         * Destroys the widget
         */
        _destroy: function () {
            $.each(this.collapsibles, function () {
                $(this).collapsible('destroy');
            });
        },

        /**
         * Init collapsible
         *
         * @param {HTMLElement} element
         */
        _initCollapsible: function (element) {
            var self = this;

            $(element).collapsible($.extend(
                {},
                this.options,
                {
                    collapsible: true,
                    disabled: false
                }
            ));
            $(element).on('beforeOpen', function () {
                self._closeOthers(this);
            });
            this.collapsibles.push($(element));
        },

        /**
         * Close other collapsible elements
         *
         * @param {HTMLElement} element
         */
        _closeOthers: function (element) {
            $.each(this.collapsibles, function () {
                if (element !== this[0]) {
                    this.collapsible('forceDeactivate');
                }
            });
        }
    });

    return $.mage.awOscAsyncAccordion;
});
