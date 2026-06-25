<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class CustomerName extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $first = trim((string) ($item['customer_firstname'] ?? ''));
                $last = trim((string) ($item['customer_lastname'] ?? ''));
                $item[$this->getData('name')] = trim($first . ' ' . $last) ?: ($item['email'] ?? '');
            }
        }

        return $dataSource;
    }
}
