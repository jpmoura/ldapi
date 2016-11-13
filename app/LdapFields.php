<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LdapFields extends Model
{
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $table = "fields";
    public $timestamps = false;

    protected $fillable = [
        'name', 'alias'
    ];
}
