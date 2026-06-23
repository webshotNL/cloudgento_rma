<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Model\ResourceModel\WithdrawalRequest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Cloudgento\Rma\Model\WithdrawalRequest as Model;
use Cloudgento\Rma\Model\ResourceModel\WithdrawalRequest as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
