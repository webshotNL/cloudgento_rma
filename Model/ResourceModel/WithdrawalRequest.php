<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class WithdrawalRequest extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('cloudgento_rma_request', 'entity_id');
    }
}
