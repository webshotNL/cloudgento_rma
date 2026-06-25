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
use Magento\Customer\Model\Session as CustomerSession;
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
        private readonly DateTime $dateTime,
        private readonly CustomerSession $customerSession
    ) {
    }

    public function execute()
    {
        $incrementId = trim((string) $this->request->getParam('increment_id'));
        $email = trim((string) $this->request->getParam('email'));
        $postcode = trim((string) $this->request->getParam('postcode'));
        $comment = trim((string) $this->request->getParam('comment'));

        $formData = [
            'increment_id' => $incrementId,
            'email' => $email,
            'postcode' => $postcode,
            'comment' => $comment,
        ];

        if ($incrementId === '' || $email === '' || $postcode === '') {
            $this->messageManager->addErrorMessage(__('Please fill in all required fields.'));
            $this->customerSession->setData('rma_form_data', $formData);
            $redirect = $this->redirectFactory->create();
            $redirect->setPath('returns');
            return $redirect;
        }

        $order = $this->orderLocator->locate($incrementId, $email, $postcode);

        if ($order === null) {
            $result = $this->orderLocator->getLastResult();

            $errorField = '';

            if ($result === OrderLocator::RESULT_POSTCODE_MISMATCH) {
                $errorField = 'postcode';
                $this->messageManager->addErrorMessage(
                    __('The postcode you entered does not match the billing or shipping address on this order. Please check and try again.')
                );
            } elseif ($result === OrderLocator::RESULT_EMAIL_MISMATCH) {
                $errorField = 'email';
                $this->messageManager->addErrorMessage(
                    __('The email address does not match the order. Please use the email address you used when placing the order.')
                );
            } else {
                $errorField = 'increment_id';
                $this->messageManager->addErrorMessage(
                    __('We could not find an order with this number. Please check your order number and try again.')
                );
            }

            $formData['error_field'] = $errorField;
            $this->customerSession->setData('rma_form_data', $formData);
            $redirect = $this->redirectFactory->create();
            $redirect->setPath('returns');
            return $redirect;
        }

        // Check if the 14-day withdrawal period has expired
        $orderDate = $order->getCreatedAt();
        if ($orderDate) {
            $orderTime = strtotime($orderDate);
            $deadline = $orderTime + (14 * 86400);
            if (time() > $deadline) {
                $this->messageManager->addErrorMessage(
                    __('The 14-day withdrawal period for this order has expired. You can only withdraw from a contract within 14 days of receiving your order. If you believe this is incorrect, please contact our customer service.')
                );
                $this->customerSession->setData('rma_form_data', $formData);
                $redirect = $this->redirectFactory->create();
                $redirect->setPath('returns');
                return $redirect;
            }
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
