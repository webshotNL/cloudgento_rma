<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderLocator
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function locate(string $incrementId, string $email, string $postcode): ?OrderInterface
    {
        $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orders = $this->orderRepository->getList($searchCriteria);

        if ($orders->getTotalCount() === 0) {
            return null;
        }

        foreach ($orders->getItems() as $order) {
            if (mb_strtolower($order->getCustomerEmail()) !== mb_strtolower($email)) {
                continue;
            }

            $billingPostcode = $order->getBillingAddress()
                ? $this->normalizePostcode($order->getBillingAddress()->getPostcode())
                : '';
            $shippingPostcode = $order->getShippingAddress()
                ? $this->normalizePostcode($order->getShippingAddress()->getPostcode())
                : '';
            $inputPostcode = $this->normalizePostcode($postcode);

            if ($inputPostcode === $billingPostcode || $inputPostcode === $shippingPostcode) {
                return $order;
            }
        }

        return null;
    }

    private function normalizePostcode(string $postcode): string
    {
        return mb_strtolower(preg_replace('/\s+/', '', $postcode));
    }
}
