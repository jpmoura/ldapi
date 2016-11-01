<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
  protected $primaryKey = 'server';
  protected $table = "oauth";
  public $timestamps = false;
}
