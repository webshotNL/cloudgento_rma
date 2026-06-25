define([
    'jquery'
], function ($) {
    'use strict';

    return function (config) {
        var baseUrl = config.baseUrl,
            label = config.label || 'Retourneren';

        // Wait for DOM to be fully ready
        $(function () {
            $('table tbody tr').each(function () {
                var $row = $(this),
                    $cells = $row.find('td'),
                    $actionsCell = null,
                    orderId = '',
                    returnUrl, $link;

                // Find the order ID from the first cell that contains only a number
                $cells.each(function () {
                    var text = $.trim($(this).text());

                    if (/^\d{6,}$/.test(text) && !orderId) {
                        orderId = text;
                    }
                    if ($(this).hasClass('actions') || $(this).find('.action.view').length) {
                        $actionsCell = $(this);
                    }
                });

                if (!orderId || !$actionsCell || !$actionsCell.length) {
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
                    title: label
                }).css({
                    display: 'block',
                    marginTop: '5px'
                }).text(label);

                $actionsCell.append($link);
            });
        });
    };
});
