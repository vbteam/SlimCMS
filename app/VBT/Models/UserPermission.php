<?php

namespace VBT\Models; 

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserPermission extends Eloquent
{
	public $timestamps = false;
	protected $table = "users_permissions";
	protected $fillable = [
		'is_admin'
	];

	public static $defaults = [
		'is_admin' => false
	];
}
