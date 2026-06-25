<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Block;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Cloudgento\Rma\Model\UrlResolver;

class Form extends Template
{
    public function __construct(
        Context $context,
        private readonly UrlResolver $urlResolver,
        private readonly RequestInterface $request,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getFormAction(): string
    {
        return $this->urlResolver->getActionUrl('submit');
    }

    public function getPrefilledOrderNumber(): string
    {
        return trim((string) $this->request->getParam('order'));
    }

    public function getIntroContent(): string
    {
        $content = (string) $this->scopeConfig->getValue(
            'cloudgento_rma/form_content/intro_content',
            ScopeInterface::SCOPE_STORE
        );

        if ($content === '') {
            return '';
        }

        return $this->filterProvider->getBlockFilter()->filter($content);
    }
}
