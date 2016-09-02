<?php

namespace Amari\Admin;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

	protected $table = 'roles';

	protected $fillable = [
		'code',
		'title',
	];

	public $timestamps = false;

	public function admins(){
		return $this->belongsToMany(User::class, 'admin_roles', 'role_id', 'admin_id');
	}
}
