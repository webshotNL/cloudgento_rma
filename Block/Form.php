<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cloudgento\Rma\Model\UrlResolver;

class Form extends Template
{
    public function __construct(
        Context $context,
        private readonly UrlResolver $urlResolver,
        private readonly RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getFormAction(): string
    {
        return $this->urlResolver->getActionUrl('confirm');
    }

    public function getPrefilledOrderNumber(): string
    {
        return trim((string) $this->request->getParam('order'));
    }
}
