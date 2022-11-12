<?php

namespace Modules\OrderModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class OrderChildrenEntity extends Model
{
    use SoftDeletes;

    //
    protected static $elq = __CLASS__;
    protected $table      = 'order';
    protected $primaryKey = 'orderId';
    protected $dates      = ['orderDeletedDate'];

    protected $casts = [
        'orderId'    => 'int'
    ];
    protected $fillable = [
        'orderNumber'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public $timestamps = false;
    const CREATED_AT = 'orderCreatedDate';
    const UPDATED_AT = 'orderUpdatedDate';
    const DELETED_AT = 'orderDeletedDate';

    public function __construct()
    {
        parent::__construct();
    }

    public function withItem()
    {
        return $this->hasMany(OrderItemEntity::class, 'orderItemOrderId', 'orderId');
    }

    public function item()
    {
        return $this->withItem()->selectRaw('
            orderItemOrderId,
            orderItemId as id,
            orderItemProductCode as productCode,
            orderItemTotalWeight as totalWeight,
            orderItemTotalQty as totalQty,
            orderItemOrderId as orderId,
            orderItemPaymentMethod as paymentMethod,
            orderItemState as state,orderItemTotal as total
        ');
    }
}
