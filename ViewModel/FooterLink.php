<?php

declare(strict_types=1);

namespace Cloudgento\Rma\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Cloudgento\Rma\Model\UrlResolver;

class FooterLink implements ArgumentInterface
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

    public function getLabel(): string
    {
        $label = $this->scopeConfig->getValue(
            'cloudgento_rma/general/footer_label',
            ScopeInterface::SCOPE_STORE
        );

        return $label ?: (string) __('Return');
    }

    public function getUrl(): string
    {
        return $this->urlResolver->getBaseUrl();
    }
}
