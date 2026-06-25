<?php

declare(strict_types=1);

namespace Cloudgento\Rma\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Cloudgento\Rma\Model\UrlResolver;

class OrderReturnButton implements ArgumentInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlResolver $urlResolver
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cloudgento_rma/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getReturnUrl(string $incrementId): string
    {
        return $this->urlResolver->getPrefilledUrl($incrementId);
    }

    public function getBaseReturnUrl(): string
    {
        return $this->urlResolver->getBaseUrl();
    }

    public function getButtonLabel(): string
    {
        $label = $this->scopeConfig->getValue(
            'cloudgento_rma/general/footer_label',
            ScopeInterface::SCOPE_STORE
        );

        return $label ?: (string) __('Retourneren');
    }
}
