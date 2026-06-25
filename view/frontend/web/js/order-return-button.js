define([
    'jquery'
], function ($) {
    'use strict';

    return function (config) {
        var baseUrl = config.baseUrl,
            label = config.label || 'Retourneren';

        $(function () {
            $('table tbody tr').each(function () {
                var $row = $(this),
                    $cells = $row.find('td'),
                    $actionsCell = null,
                    orderId = '',
                    returnUrl;

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

                if ($actionsCell.find('.action.return').length) {
                    return;
                }

                returnUrl = baseUrl + (baseUrl.indexOf('?') === -1 ? '?' : '&') + 'order=' + encodeURIComponent(orderId);

                $actionsCell.append(
                    '<a href="' + returnUrl + '" class="action return" ' +
                    'title="' + label + '" ' +
                    'style="margin-left:10px;padding-left:10px;border-left:1px solid #ccc;">' +
                    label + '</a>'
                );
            });
        });
    };
});
