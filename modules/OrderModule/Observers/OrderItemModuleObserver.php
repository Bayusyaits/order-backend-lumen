<?php

namespace Modules\OrderModule\Observers;

use Modules\OrderModule\Entities\OrderItemEntity as OrderItem;
use Modules\ProductModule\Entities\ProductEntity;
use Illuminate\Support\Facades\DB;

class OrderItemModuleObserver
{
    /**
     * @return void
     */

    private static function setQty(OrderItem $item)
    {
        if (isset($item->orderItemProductCode) &&
        !empty($item->orderItemProductCode)) {
            $p = ProductEntity::where('productCode', '=', $item->orderItemProductCode)->
            first();
            if ($p) {
                $qty = $p->productStock - $item->orderItemTotalQty;
                $p->productStock = abs($qty);
                $p->save();
            }
        }
    }

    public function created(OrderItem $item)
    {
        //
        self::setQty($item);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(OrderItem $item)
    {
        //
    }

    public function deleting(OrderItem $item)
    {
    }
    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(OrderItem $item)
    {
        //
    }

    /**
     * Handle the User "forceDeleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(OrderItem $item)
    {
        //
    }
}
