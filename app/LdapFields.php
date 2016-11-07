<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LdapFields extends Model
{
  //protected $primaryKey = 'server';
  protected $table = "fields";
  public $timestamps = false;
}
