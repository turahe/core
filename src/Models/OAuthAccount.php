<?php

declare(strict_types=1);

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Turahe\Core\Facades\Google;
use Turahe\Core\OAuth\AccessTokenProvider;
use Turahe\Core\OAuth\Events\OAuthAccountDeleting;

/**
 * @property int $id
 * @property string $type
 * @property string $user_id
 * @property string $oauth_user_id
 * @property string|null $email
 * @property bool $requires_auth
 * @property mixed $access_token
 * @property string|null $refresh_token
 * @property string $expires
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Turahe\Core\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereOauthUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereRequiresAuth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount whereUserId($value)
 *
 * @property-read string $expiration
 *
 * @mixin \Eloquent
 */
class OAuthAccount extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_accounts';

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
