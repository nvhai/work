/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko'
    ],
    function(ko) {
        'use strict';

        var isShownFlag = ko.observable(false);

        return {
            isShown: isShownFlag
        };
    }
);
