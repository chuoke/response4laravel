<?php

namespace Chuoke\Response4Laravel\Tests;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'name'];
}
