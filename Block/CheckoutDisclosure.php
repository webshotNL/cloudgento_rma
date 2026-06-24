<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Cloudgento\Rma\Model\UrlResolver;

class CheckoutDisclosure extends Template
{
    public function __construct(
        Context $context,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlResolver $urlResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getDisclosureText(): string
    {
        return (string) $this->scopeConfig->getValue(
            'cloudgento_rma/general/checkout_disclosure_text',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getWithdrawalUrl(): string
    {
        return $this->urlResolver->getBaseUrl();
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'cloudgento_rma/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}
