<?php

namespace WebDEV\Meli\Databases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeliAppToken extends Model {
    use SoftDeletes;

    protected $table = 'meli_app_tokens';
    protected $guarded = [];
}
