<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LdapSettings extends Model
{
  //protected $primaryKey = 'server';
  protected $table = "settings";
  public $timestamps = false;
}
