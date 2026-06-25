<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Cloudgento\Rma\Model\OrderLocator;
use Cloudgento\Rma\Model\WithdrawalRequestFactory;
use Cloudgento\Rma\Model\ResourceModel\WithdrawalRequest as WithdrawalResource;

class Submit implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly PageFactory $pageFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly MessageManager $messageManager,
        private readonly OrderLocator $orderLocator,
        private readonly WithdrawalRequestFactory $withdrawalRequestFactory,
        private readonly WithdrawalResource $withdrawalResource,
        private readonly TransportBuilder $transportBuilder,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly DateTime $dateTime
    ) {
    }

    public function execute()
    {
        $incrementId = trim((string) $this->request->getParam('increment_id'));
        $email = trim((string) $this->request->getParam('email'));
        $postcode = trim((string) $this->request->getParam('postcode'));
        $comment = trim((string) $this->request->getParam('comment'));

        if ($incrementId === '' || $email === '' || $postcode === '') {
            $this->messageManager->addErrorMessage(__('Please fill in all required fields.'));
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

        $storeId = (int) $this->storeManager->getStore()->getId();
        $requestedAt = $this->dateTime->gmtDate();

        $withdrawalRequest = $this->withdrawalRequestFactory->create();
        $withdrawalRequest->setData([
            'order_id' => $order->getEntityId(),
            'increment_id' => $incrementId,
            'email' => $email,
            'comment' => $comment !== '' ? $comment : null,
            'status' => 'received',
            'requested_at' => $requestedAt,
            'store_id' => $storeId,
        ]);
        $this->withdrawalResource->save($withdrawalRequest);

        $this->sendConfirmationEmail($order, $email, $requestedAt, $storeId);

        if ($this->scopeConfig->isSetFlag(
            'cloudgento_rma/notifications/merchant_notify',
            ScopeInterface::SCOPE_STORE
        )) {
            $this->sendMerchantNotification($order, $email, $requestedAt, $comment, $storeId);
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Withdrawal Confirmed'));
        $page->getConfig()->setRobots('noindex,nofollow');

        $block = $page->getLayout()->getBlock('withdrawal.success');
        if ($block) {
            $block->setData('order', $order);
            $block->setData('requested_at', $requestedAt);
        }

        return $page;
    }

    private function sendConfirmationEmail($order, string $email, string $requestedAt, int $storeId): void
    {
        $store = $this->storeManager->getStore($storeId);

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('cloudgento_rma_withdrawal_confirmation')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars([
                'order' => $order,
                'increment_id' => $order->getIncrementId(),
                'requested_at' => $requestedAt,
                'store' => $store,
            ])
            ->setFromByScope(
                $this->scopeConfig->getValue(
                    'trans_email/ident_sales/email',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
                    ? 'sales'
                    : 'general',
                $storeId
            )
            ->addTo($email)
            ->getTransport();

        $transport->sendMessage();
    }

    private function sendMerchantNotification($order, string $email, string $requestedAt, string $comment, int $storeId): void
    {
        $merchantEmail = $this->scopeConfig->getValue(
            'cloudgento_rma/notifications/merchant_email',
            ScopeInterface::SCOPE_STORE
        );

        if (!$merchantEmail) {
            return;
        }

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('cloudgento_rma_merchant_notification')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars([
                'order' => $order,
                'increment_id' => $order->getIncrementId(),
                'customer_email' => $email,
                'requested_at' => $requestedAt,
                'comment' => $comment,
            ])
            ->setFromByScope('general', $storeId)
            ->addTo($merchantEmail)
            ->getTransport();

        $transport->sendMessage();
    }
}
