<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class UrlResolver
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    public function getBaseUrl(): string
    {
        $customSlug = $this->getCustomSlug();

        if ($customSlug !== '') {
            return $this->urlBuilder->getDirectUrl($customSlug);
        }

        return $this->urlBuilder->getUrl('returns');
    }

    public function getPrefilledUrl(string $incrementId): string
    {
        $customSlug = $this->getCustomSlug();

        if ($customSlug !== '') {
            return $this->urlBuilder->getDirectUrl($customSlug, ['_query' => ['order' => $incrementId]]);
        }

        return $this->urlBuilder->getUrl('returns', ['_query' => ['order' => $incrementId]]);
    }

    public function getActionUrl(string $action): string
    {
        $customSlug = $this->getCustomSlug();

        if ($customSlug !== '') {
            return $this->urlBuilder->getDirectUrl($customSlug . '/' . $action);
        }

        return $this->urlBuilder->getUrl('returns/index/' . $action);
    }

    private function getCustomSlug(): string
    {
        return trim((string) $this->scopeConfig->getValue(
            'cloudgento_rma/general/custom_url',
            ScopeInterface::SCOPE_STORE
        ));
    }
}
