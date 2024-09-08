<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Turahe\Core\Database\Factories\OAuthAccountFactory;
use Turahe\Core\Facades\Google;
use Turahe\Core\OAuth\AccessTokenProvider;
use Turahe\Core\OAuth\Events\OAuthAccountDeleting;

/**
 * Turahe\Core\Models\OAuthAccount
 *
 * @property-read \Turahe\Users\Models\User $user
 *
 * @method static \Turahe\Core\Database\Factories\OAuthAccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|OAuthAccount query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $type
 * @property int $user_id
 * @property string $oauth_user_id
 * @property string|null $email
 * @property bool $requires_auth
 * @property mixed $access_token
 * @property string|null $refresh_token
 * @property string $expires
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
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
 * @mixin \Eloquent
 */
class OAuthAccount extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_accounts';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'requires_auth' => 'boolean',
        'access_token' => 'encrypted',
        'user_id' => 'int',
    ];

    /**
     * Boot the OAuthAccount Model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($account) {
            OAuthAccountDeleting::dispatch($account);
        });

        static::deleted(function ($account) {
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
    public function setRequiresAuthentication(bool $value = true)
    {
        $this->requires_auth = $value;
        $this->save();

        return $this;
    }

    /**
     * Get the user the OAuth account belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Turahe\Users\Models\User::class);
    }

    /**
     * Create new token provider
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
