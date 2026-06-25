<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Cloudgento_Rma::withdrawal_view';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Cloudgento_Rma::withdrawal');
        $page->getConfig()->getTitle()->prepend(__('Return Requests'));
        return $page;
    }
}
