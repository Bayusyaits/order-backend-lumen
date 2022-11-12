<?php

namespace Modules\UserModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserClientEntity extends Model
{
    use SoftDeletes;
    //
    protected $table      = 'userClient';
    protected $primaryKey = 'userClientId';
    protected $dates      = ['userClientDeletedDate'];
    protected $fillable = [
        'userClientStatus','userClientDeletedDate'
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
    const CREATED_AT = 'userClientCreatedDate';

    /**
     * (Override)
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'userClientUpdatedDate';
    const DELETED_AT = 'userClientDeletedDate';
}
