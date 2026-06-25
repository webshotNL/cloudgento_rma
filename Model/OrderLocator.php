<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderLocator
{
    public const RESULT_OK = 'ok';
    public const RESULT_NOT_FOUND = 'not_found';
    public const RESULT_EMAIL_MISMATCH = 'email_mismatch';
    public const RESULT_POSTCODE_MISMATCH = 'postcode_mismatch';

    private string $lastResult = self::RESULT_NOT_FOUND;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function locate(string $incrementId, string $email, string $postcode): ?OrderInterface
    {
        $this->lastResult = self::RESULT_NOT_FOUND;

        $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orders = $this->orderRepository->getList($searchCriteria);

        if ($orders->getTotalCount() === 0) {
            return null;
        }

        foreach ($orders->getItems() as $order) {
            if (mb_strtolower($order->getCustomerEmail()) !== mb_strtolower($email)) {
                $this->lastResult = self::RESULT_EMAIL_MISMATCH;
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
                $this->lastResult = self::RESULT_OK;
                return $order;
            }

            $this->lastResult = self::RESULT_POSTCODE_MISMATCH;
        }

        return null;
    }

    public function getLastResult(): string
    {
        return $this->lastResult;
    }

    private function normalizePostcode(string $postcode): string
    {
        return mb_strtolower(preg_replace('/\s+/', '', $postcode));
    }
}
