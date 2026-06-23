<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Model;

use Magento\Framework\Model\AbstractModel;
use Cloudgento\Rma\Model\ResourceModel\WithdrawalRequest as ResourceModel;

class WithdrawalRequest extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }
}
