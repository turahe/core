<?php

declare(strict_types=1);

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Turahe\Core\Facades\Google;
use Turahe\Core\OAuth\AccessTokenProvider;
use Turahe\Core\OAuth\Events\OAuthAccountDeleting;

class OAuthAccount extends Model
{
    use HasConfigurablePrimaryKey;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('core.tables.oauth_accounts');
    }

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'requires_auth' => 'boolean',
            'access_token' => 'encrypted',
            'user_id' => 'string',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::deleting(function (OAuthAccount $account): void {
            OAuthAccountDeleting::dispatch($account);
        });

        static::deleted(function (OAuthAccount $account): void {
            if ($account->type === 'google') {
                try {
                    Google::revokeToken($account->access_token);
                } catch (\Exception) {
                }
            }
        });
    }

    /**
     * Set that this account requires authentication.
     */
    public function setAuthRequired(bool $value = true)
    {
        $this->requires_auth = $value;
        $this->save();

        return $this;
    }

    /**
     * Get the user the OAuth account belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Create new token provider.
     */
    public function tokenProvider(): AccessTokenProvider
    {
        return new AccessTokenProvider($this->access_token, $this->email);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OAuthAccountFactory
    {
        return new OAuthAccountFactory;
    }
}
