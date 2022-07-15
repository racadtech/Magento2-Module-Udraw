<?php

namespace Racadtech\Udraw\Plugin\Quote\Item;

use Psr\Log\LoggerInterface;
use Racadtech\Udraw\Helper\Udraw;

class AbstractItem
{
    protected LoggerInterface $logger;
    protected Udraw $udrawHelper;

    public function __construct(LoggerInterface $_logger, Udraw $_udrawHelper)
    {
        $this->logger = $_logger;
        $this->udrawHelper = $_udrawHelper;
    }

    public function afterCalcRowTotal($item, $closure)
    {
        $itemQty = $item->getQty();
        $udrawData = $this->udrawHelper->getUdrawDataFromCartQuoteItem($item);

        if ($udrawData != null) {
            if (key_exists('udraw_pricematrix_additional_price', $udrawData)) {
                $additionalPrice = floatval($udrawData['udraw_pricematrix_additional_price']);
                $qtyBasePrice = $this->udrawHelper->calculateQtyPriceFromQuoteItem($item, $itemQty);
                $totalBasePrice = ($qtyBasePrice * $itemQty) + $additionalPrice;
                $unitPrice = $totalBasePrice / $itemQty;

                $item->setCustomPrice($unitPrice);
                $item->setOriginalCustomPrice($unitPrice);
                $this->logger->debug("[Udraw] Price Update: (" . $qtyBasePrice . " * " . $itemQty . ") + " . $additionalPrice);
            }
        }
    }
}
