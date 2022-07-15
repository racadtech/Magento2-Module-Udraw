<?php

namespace Racadtech\Udraw\Model\Attribute\Source;

use Racadtech\Udraw\Helper\Udraw;

class Pricematrix extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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

            $priceMatrixCollection = $this->udrawHelper->getPricematrixCollection();
            foreach ($priceMatrixCollection as $priceMatrix) {
                array_push(
                    $this->_options,
                    ['label' => $priceMatrix->getName(), 'value' =>$priceMatrix->getAccessKey()]
                );
            }
        }
        return $this->_options;
    }
}
