<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly RedirectFactory $redirectFactory,
        private readonly RedirectInterface $redirect
    ) {
    }

    public function execute()
    {
        if (!$this->scopeConfig->isSetFlag(
            'cloudgento_rma/general/enabled',
            ScopeInterface::SCOPE_STORE
        )) {
            $result = $this->redirectFactory->create();
            $result->setUrl($this->redirect->getRefererUrl());
            return $result;
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Return a Product'));
        $page->getConfig()->setRobots('index,follow');
        return $page;
    }
}
