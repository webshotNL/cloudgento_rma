define([
    'jquery'
], function ($) {
    'use strict';

    return function (config) {
        var baseUrl = config.baseUrl,
            label = config.label || 'Retourneren';

        $('table tbody tr').each(function () {
            var $row = $(this),
                $orderLink = $row.find('td.col.id'),
                orderId = $.trim($orderLink.text()),
                $actionsCell = $row.find('td.col.actions'),
                returnUrl, $link;

            if (!orderId || !$actionsCell.length) {
                return;
            }

            // Skip if already added
            if ($actionsCell.find('.action.return').length) {
                return;
            }

            returnUrl = baseUrl + (baseUrl.indexOf('?') === -1 ? '?' : '&') + 'order=' + encodeURIComponent(orderId);

            $link = $('<a/>', {
                href: returnUrl,
                'class': 'action return',
                title: label,
                css: { display: 'block', marginTop: '5px' }
            }).text(label);

            // Append directly to the td, outside any existing wrapper spans
            $actionsCell[0].appendChild($link[0]);
        });
    };
});
