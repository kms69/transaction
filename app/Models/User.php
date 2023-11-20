<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function assignRole($role)
    {
        return $this->roles()->sync($role, false);
    }
    public function isAdmin(): bool
    {
        return $this->hasAnyRole('admin');
    }

    public function isUser(): bool
    {
        return $this->hasAnyRole('user');
    }
    public function hasAnyRole(...$roles): bool
    {
        foreach ($roles as $role) {
            // Check if the user has the specified role
            if ($this->roles->contains('name', $role)) {
                return true;
            }
        }


        return false;
    }
    public function hasRole($role): bool
    {
        return $this->roles->contains('name', $role);
    }


    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

}
