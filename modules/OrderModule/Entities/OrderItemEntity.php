<?php

namespace Modules\OrderModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderItemEntity extends Model
{
    use SoftDeletes;
    //
    protected $table      = 'orderItem';
    protected $primaryKey = 'orderItemId';
    protected $dates      = ['orderItemDeletedDate'];
    protected $fillable = [
        'orderItemDeletedDate', 'orderItemProductCode', 'orderItemOrderId'
    ];
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * (Override)
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'orderItemCreatedDate';

    /**
     * (Override)
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'orderItemUpdatedDate';
    const DELETED_AT = 'orderItemDeletedDate';
}
