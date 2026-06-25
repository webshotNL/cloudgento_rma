<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Psr\Log\LoggerInterface;

class OrderItems extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger,
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
                    try {
                        $order = $this->orderRepository->get((int) $item['order_id']);
                        $names = [];
                        foreach ($order->getAllVisibleItems() as $orderItem) {
                            $names[] = $orderItem->getName() . ' x' . (int) $orderItem->getQtyOrdered();
                        }
                        $item[$this->getData('name')] = implode(', ', $names);
                    } catch (\Exception $e) {
                        $this->logger->debug('RMA grid: could not load order ' . $item['order_id']);
                        $item[$this->getData('name')] = '';
                    }
                }
            }
        }

        return $dataSource;
    }
}
