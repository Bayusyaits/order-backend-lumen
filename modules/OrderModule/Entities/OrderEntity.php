<?php

namespace Modules\OrderModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderEntity extends Model
{
    use SoftDeletes;
    //
    protected $table      = 'order';
    protected $primaryKey = 'orderId';
    protected $dates      = ['orderDeletedDate'];
    protected $guarded    = [];
    protected $fillable = [
        'orderNumber'
    ];
    /**
     * (Override)
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'orderCreatedDate';

    /**
     * (Override)
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'orderUpdatedDate';
    const DELETED_AT = 'orderDeletedDate';
}
