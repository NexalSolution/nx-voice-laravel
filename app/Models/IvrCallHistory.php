<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IvrCallHistory extends Model
{
    protected $table = 'ivr_call_history';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
