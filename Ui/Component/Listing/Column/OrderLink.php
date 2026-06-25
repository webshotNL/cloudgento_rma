<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Backend\Model\UrlInterface;

class OrderLink extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $backendUrl,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item['order_id'])) {
                    $url = $this->backendUrl->getUrl(
                        'sales/order/view',
                        ['order_id' => $item['order_id']]
                    );
                    $item[$this->getData('name')] = '<a href="' . $url . '">' . ($item['increment_id'] ?? '') . '</a>';
                }
            }
        }

        return $dataSource;
    }
}
