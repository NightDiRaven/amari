<?php

namespace Amari\Admin;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();
        static::saving(function (User $user) {
            $attrs = $user->getDirty();
            if (isset($attrs['password'])) {
                $user->attributes['password'] = bcrypt($user->attributes['password']);
            }
        });
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_roles', 'admin_id', 'role_id');
    }

    public static function removeByName($name)
    {
        $entry = static::whereName($name);

        if ($entry) return $entry->delete();
        return false;
    }
}
