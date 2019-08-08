/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    ['ko'],
    function (ko) {
        'use strict';

        return {
            code: ko.observable(''),
            isApplied: ko.observable(false)
        }
    }
);
