/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    ['underscore'],
    function (_) {
        'use strict';

        return function (abstractTotal) {

            /**
             * @inheritdoc
             */
            return _.extend(abstractTotal, {
                isFullMode: function () {
                    return !!this.getTotals();
                }
            });
        }
    }
);
