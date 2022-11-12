<?php

namespace Modules\ProductModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductEntity extends Model
{
    use SoftDeletes;
    //
    protected $table      = 'product';
    protected $primaryKey = 'productId';
    protected $dates      = ['productDeletedDate'];
    protected $guarded    = [];

    /**
     * (Override)
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'productCreatedDate';

    /**
     * (Override)
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'productUpdatedDate';
    const DELETED_AT = 'productDeletedDate';
}
