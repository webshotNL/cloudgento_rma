<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\View\Result\PageFactory;
use Cloudgento\Rma\Model\OrderLocator;

class Confirm implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly PageFactory $pageFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly MessageManager $messageManager,
        private readonly OrderLocator $orderLocator
    ) {
    }

    public function execute()
    {
        $incrementId = trim((string) $this->request->getParam('increment_id'));
        $email = trim((string) $this->request->getParam('email'));
        $postcode = trim((string) $this->request->getParam('postcode'));
        $comment = trim((string) $this->request->getParam('comment'));

        if ($incrementId === '' || $email === '' || $postcode === '') {
            $this->messageManager->addErrorMessage(
                __('Please fill in all required fields.')
            );
            $redirect = $this->redirectFactory->create();
            $redirect->setPath('returns');
            return $redirect;
        }

        $order = $this->orderLocator->locate($incrementId, $email, $postcode);

        if ($order === null) {
            $this->messageManager->addErrorMessage(
                __('We could not find an order matching the details you entered. Please check and try again.')
            );
            $redirect = $this->redirectFactory->create();
            $redirect->setPath('returns');
            return $redirect;
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Confirm Withdrawal'));
        $page->getConfig()->setRobots('noindex,nofollow');

        $block = $page->getLayout()->getBlock('withdrawal.confirm');
        if ($block) {
            $block->setData('order', $order);
            $block->setData('comment', $comment);
        }

        return $page;
    }
}
