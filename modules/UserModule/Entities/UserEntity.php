<?php

namespace Modules\UserModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserEntity extends Model
{
    use SoftDeletes;
    //
    protected $table      = 'user';
    protected $primaryKey = 'userId';
    protected $dates      = ['userDeletedDate'];
    protected $fillable = [
        'userDeletedDate'
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
    const CREATED_AT = 'userCreatedDate';

    /**
     * (Override)
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'userUpdatedDate';
    const DELETED_AT = 'userDeletedDate';
}
