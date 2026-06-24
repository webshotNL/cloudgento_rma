<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Block;

use Magento\Framework\View\Element\Template;

class Confirm extends Template
{
    public function getSubmitAction(): string
    {
        return $this->getUrl('returns/index/submit');
    }
}
