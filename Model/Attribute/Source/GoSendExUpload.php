<?php

namespace Racadtech\Udraw\Model\Attribute\Source;

class GoSendExUpload extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions(): ?array
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Disabled'), 'value' => 'Disabled'],
                ['label' => __('Enabled'), 'value' => 'Enabled']
            ];
        }
        return $this->_options;
    }
}
