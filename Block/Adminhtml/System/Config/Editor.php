<?php

declare(strict_types=1);

namespace Cloudgento\Rma\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Editor extends Field
{
    public function __construct(
        Context $context,
        private readonly WysiwygConfig $wysiwygConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setWysiwyg(true);
        $element->setConfig(
            $this->wysiwygConfig->getConfig([
                'add_variables' => false,
                'add_widgets' => false,
                'height' => '300px',
            ])
        );

        return parent::_getElementHtml($element);
    }
}
