<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LdapSettings extends Model
{
    protected $primaryKey = 'server';
    public $incrementing = false;
    protected $table = "settings";
    public $timestamps = false;
}
