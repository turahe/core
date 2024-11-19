<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Turahe\Core\Concerns\HasSettings;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasUlids;
    use HasSettings;
    protected $table = 'users';

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