<?php

namespace App\Translatable\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{

    protected $fillable = ['id', 'code', 'filename', 'url'];
}
