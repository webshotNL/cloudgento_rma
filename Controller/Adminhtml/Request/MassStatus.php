<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Cloudgento\Rma\Model\ResourceModel\WithdrawalRequest\CollectionFactory;
use Cloudgento\Rma\Model\ResourceModel\WithdrawalRequest as WithdrawalResource;

class MassStatus extends Action
{
    public const ADMIN_RESOURCE = 'Cloudgento_Rma::withdrawal_manage';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly WithdrawalResource $withdrawalResource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $status = $this->getRequest()->getParam('status');
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count = 0;

        foreach ($collection as $item) {
            $item->setData('status', $status);
            $this->withdrawalResource->save($item);
            $count++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 request(s) have been updated.', $count)
        );

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('returns/request/index');
        return $resultRedirect;
    }
}
