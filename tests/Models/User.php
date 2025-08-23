<?php

namespace Turahe\Core\Tests\Models;

use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Turahe\Core\Concerns\HasOrganization;
use Turahe\Core\Concerns\HasSettings;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasConfigurablePrimaryKey;
    use HasOrganization;
    use HasSettings;

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
