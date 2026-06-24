<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cloudgento\Rma\Model\UrlResolver;

class Confirm extends Template
{
    public function __construct(
        Context $context,
        private readonly UrlResolver $urlResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getSubmitAction(): string
    {
        return $this->urlResolver->getActionUrl('submit');
    }

    public function getCancelUrl(): string
    {
        return $this->urlResolver->getBaseUrl();
    }
}
