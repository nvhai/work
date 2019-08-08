/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function ($) {
    'use strict';

    // todo: consider remove after sidebar implemented
    $.widget('mage.awOscSidebar', {
        options: {
            offsetTop: 5 // todo: get this from margin-top property
        },

        /**
         * Initialize widget
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            // todo
        },

        /**
         * Adjust element position
         */
        _adjust: function () {
            if ($(window).scrollTop() > this.options.offsetTop) {
                this.element.css({
                    'position': 'fixed',
                    'top': this.options.offsetTop
                });
            } else {
                this.element.css('position', 'static');
            }
        }
    });

    return $.mage.awOscSidebar;
});
