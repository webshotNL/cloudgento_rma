<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Router;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Store\Model\ScopeInterface;

class CustomUrl implements RouterInterface
{
    public function __construct(
        private readonly ActionFactory $actionFactory,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function match(RequestInterface $request): ?\Magento\Framework\App\ActionInterface
    {
        if (!$this->scopeConfig->isSetFlag(
            'cloudgento_rma/general/enabled',
            ScopeInterface::SCOPE_STORE
        )) {
            return null;
        }

        $customSlug = trim((string) $this->scopeConfig->getValue(
            'cloudgento_rma/general/custom_url',
            ScopeInterface::SCOPE_STORE
        ));

        if ($customSlug === '') {
            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $parts = explode('/', $identifier);
        $slug = $parts[0] ?? '';

        if ($slug !== $customSlug) {
            return null;
        }

        // Map: /custom-slug → returns/index/index
        //      /custom-slug/confirm → returns/index/confirm  (not used directly but kept for consistency)
        $action = $parts[1] ?? 'index';

        $request->setModuleName('returns');
        $request->setControllerName('index');
        $request->setActionName($action);
        $request->setAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
            $identifier
        );

        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
    }
}
