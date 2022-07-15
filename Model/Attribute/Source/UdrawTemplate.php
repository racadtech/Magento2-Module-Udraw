<?php

namespace Racadtech\Udraw\Model\Attribute\Source;

use Racadtech\Udraw\Helper\Udraw;

class UdrawTemplate extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected Udraw $udrawHelper;

    public function __construct(Udraw $udrawHelper)
    {
        $this->udrawHelper = $udrawHelper;
    }

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions(): array
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('None'), 'value' => '']
            ];

            $templatesCollection = $this->udrawHelper->getTemplatesCollection();
            foreach ($templatesCollection as $template) {
                array_push(
                    $this->_options,
                    [
                        'label' => $template->getName() . ' ( ' . $template->getDesignWidth() . ' x ' . $template->getDesignHeight() . ' )' ,
                        'value' =>$template->getAccessKey()
                    ]
                );
            }
        }
        return $this->_options;
    }
}
