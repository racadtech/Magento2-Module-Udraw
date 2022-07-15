<?php

namespace Racadtech\Udraw\Plugin\Directory;

class PriceCurrency
{

    /**
     * Overrides default round method which uses precision at '2'.
     * This method will use precision set to '4' to give a more accurate price.
     *
     * @param \Magento\Directory\Model\PriceCurrency $subject
     * @param $closure
     * @param $price
     * @return float
     */
    public function aroundRound(\Magento\Directory\Model\PriceCurrency $subject, $closure, $price): float
    {
        return round((float) $price, 4);
    }
}
