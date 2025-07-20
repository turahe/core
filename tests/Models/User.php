<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Turahe\Core\Concerns\HasSettings;
use Turahe\Core\Concerns\HasOrganization;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasSettings;
    use HasUlids;
    use HasOrganization;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * @var string[]
     */
    protected $defaultSettings = [
        'language',
        'timezone',
        'datetime',
    ];

    // settings rules
    /**
     * @var array|string[]
     */
    public array $settingsRules = [
        'datetime' => 'string',
        'language' => 'string',
        'timezone' => 'timezone:all',
    ];
}
