<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
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
        'nickname',
        'bio',
        'github_id',
        'github_token',
        'github_refresh_token',
        'wechat_id',
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
    ];

    /**
     * Determine if the current API token has a given scope.
     *
     * @return bool
     */
    public function tokenCan(string $ability)
    {
        return $this->isSuperAdmin() || ($this->accessToken && $this->accessToken->can($ability));
    }

    protected function isSuperAdmin()
    {
        return $this->id === 1; // 或者是 SuperAdmin 角色
    }

    public function canAccessFilament(): bool
    {
        // https://filamentphp.com/tricks/admin-403-in-production
        return $this->isSuperAdmin();
    }
}
