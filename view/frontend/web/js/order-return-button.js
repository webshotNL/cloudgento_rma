define([
    'jquery'
], function ($) {
    'use strict';

    return function (config) {
        var baseUrl = config.baseUrl,
            label = config.label || 'Retourneren';

        // Find all order rows in the history table
        $('#my-orders-table tbody tr').each(function () {
            var $row = $(this),
                $orderLink = $row.find('td.col.id'),
                orderId = $.trim($orderLink.text()),
                $actionsCell = $row.find('td.col.actions'),
                returnUrl;

            if (!orderId || !$actionsCell.length) {
                return;
            }

            returnUrl = baseUrl + (baseUrl.indexOf('?') === -1 ? '?' : '&') + 'order=' + encodeURIComponent(orderId);

            $actionsCell.find('.action-links, span').first().append(
                ' <a href="' + returnUrl + '" class="action return" title="' + label + '">' +
                '<span>' + label + '</span></a>'
            );
        });
    };
});
