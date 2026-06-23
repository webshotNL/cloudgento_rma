<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Block;

use Magento\Framework\View\Element\Template;

class Form extends Template
{
    public function getFormAction(): string
    {
        return $this->getUrl('withdrawal/index/confirm');
    }
}
