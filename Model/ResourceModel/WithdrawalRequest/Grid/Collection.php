<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Model\ResourceModel\WithdrawalRequest\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _initSelect(): static
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['so' => $this->getTable('sales_order')],
            'main_table.order_id = so.entity_id',
            [
                'customer_firstname' => 'so.customer_firstname',
                'customer_lastname' => 'so.customer_lastname',
                'grand_total' => 'so.grand_total',
                'order_currency_code' => 'so.order_currency_code',
                'order_created_at' => 'so.created_at',
            ]
        );

        return $this;
    }
}
