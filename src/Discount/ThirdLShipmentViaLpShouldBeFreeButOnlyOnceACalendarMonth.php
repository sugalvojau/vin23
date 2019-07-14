<?php

namespace Discount;

use Carrier\CarrierFrance;
use DataMatrix\DiscountAmountMatrix;
use DiscountSet\DiscountSetInterface;
use DiscountSetContainer\DiscountSetContainerInterface;
use Input\InputItem;
use Math\Math;
use Package\Package;
use Price\PriceInterface;

/**
 * Rule#2: Third L shipment via LP should be free, but only once a calendar month.
 */
final class ThirdLShipmentViaLpShouldBeFreeButOnlyOnceACalendarMonth implements DiscountInterface
{
    private const FREE_ITEM_NUMBER_FOR_L_VIA_LP_IN_CALENDAR_MONTH = 3;

    public function apply(
        DiscountAmountMatrix $discountAmountMatrix,
        PriceInterface $shipmentPriceService,
        DiscountSetContainerInterface $discountSetContainerService,
        DiscountSetInterface $discountSetService,
        InputItem $inputItem,
        float $priceBeforeAnyDiscountsOnItem,
        float $priceAfterDiscountsAppliedOnDiscountSetPastItems
    ): float
    {
        if ($inputItem->getPackageSizeCode() !== Package::ITEMS['L']['code']) {
            return $priceAfterDiscountsAppliedOnDiscountSetPastItems;
        }

        if ($inputItem->getCarrierCode() !== CarrierFrance::ITEMS['LP']['code']) {
            return $priceAfterDiscountsAppliedOnDiscountSetPastItems;
        }

        $lShipmentViaLpOnInputItemMonth = $inputItem->getTransactionsCountMatrix()->countItemsOfSizeOfCarrierInMonth(
            $inputItem->getPackageSizeCode(),
            $inputItem->getCarrierCode(),
            $inputItem->getDateTime()
        );

        if ($lShipmentViaLpOnInputItemMonth !== self::FREE_ITEM_NUMBER_FOR_L_VIA_LP_IN_CALENDAR_MONTH) {
            return $priceAfterDiscountsAppliedOnDiscountSetPastItems;
        }

        return Math::getNumber(0.0); // Yep, this is it! Apply the discount.
    }
}
