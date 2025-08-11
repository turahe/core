<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Turahe\Core\Models\OAuthAccount;
use Turahe\Core\Tests\TestCase;

class OAuthAccountModelTest extends TestCase
{

    public function test_oauth_account_table_is_configurable(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertEquals(config('core.tables.oauth_accounts'), $oauthAccount->getTable());
    }

    public function test_oauth_account_has_guarded_attributes(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertEquals([], $oauthAccount->getGuarded());
    }

    public function test_oauth_account_has_correct_casts(): void
    {
        $oauthAccount = new OAuthAccount();
        $casts = $oauthAccount->getCasts();
        
        $this->assertArrayHasKey('requires_auth', $casts);
        $this->assertEquals('boolean', $casts['requires_auth']);
        
        $this->assertArrayHasKey('access_token', $casts);
        $this->assertEquals('encrypted', $casts['access_token']);
        
        $this->assertArrayHasKey('user_id', $casts);
        $this->assertEquals('string', $casts['user_id']);
    }

    public function test_oauth_account_has_set_auth_required_method(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertTrue(method_exists($oauthAccount, 'setAuthRequired'));
    }

    public function test_oauth_account_has_token_provider_method(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertTrue(method_exists($oauthAccount, 'tokenProvider'));
    }

    public function test_oauth_account_has_user_relationship_method(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertTrue(method_exists($oauthAccount, 'user'));
    }

    public function test_oauth_account_dispatches_events(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertContains('deleting', $oauthAccount->getObservableEvents());
    }

    public function test_oauth_account_has_boolean_requires_auth_cast(): void
    {
        $oauthAccount = new OAuthAccount();
        $casts = $oauthAccount->getCasts();
        
        $this->assertEquals('boolean', $casts['requires_auth']);
    }

    public function test_oauth_account_has_encrypted_access_token_cast(): void
    {
        $oauthAccount = new OAuthAccount();
        $casts = $oauthAccount->getCasts();
        
        $this->assertEquals('encrypted', $casts['access_token']);
    }

    public function test_oauth_account_has_string_user_id_cast(): void
    {
        $oauthAccount = new OAuthAccount();
        $casts = $oauthAccount->getCasts();
        
        $this->assertEquals('string', $casts['user_id']);
    }

    public function test_oauth_account_has_correct_fillable_attributes(): void
    {
        $oauthAccount = new OAuthAccount();
        
        // Since the model uses $guarded = ['*'], it should have no fillable attributes
        $this->assertEmpty($oauthAccount->getFillable());
    }

    public function test_oauth_account_soft_delete_methods_are_not_available(): void
    {
        $oauthAccount = new OAuthAccount();
        
        // Check that the model doesn't have soft delete specific methods
        // Note: Laravel might add some methods globally, so we'll be more specific
        $this->assertFalse($oauthAccount->isSoftDeletable());
    }

    public function test_oauth_account_has_timestamps(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertTrue($oauthAccount->usesTimestamps());
    }

    public function test_oauth_account_has_configurable_primary_key(): void
    {
        $oauthAccount = new OAuthAccount();
        
        $this->assertEquals('id', $oauthAccount->getKeyName());
        
        // Test that the model has the configurable primary key trait methods
        $this->assertTrue(method_exists($oauthAccount, 'shouldUseUniqueIds'));
        $this->assertTrue(method_exists($oauthAccount, 'getConfiguredKeyType'));
        $this->assertTrue(method_exists($oauthAccount, 'shouldUseIncrementing'));
        $this->assertTrue(method_exists($oauthAccount, 'newUniqueId'));
        $this->assertTrue(method_exists($oauthAccount, 'uniqueIds'));
        
        // Test that the model uses ULIDs by default
        $this->assertTrue($oauthAccount->shouldUseUniqueIds());
        $this->assertEquals('string', $oauthAccount->getConfiguredKeyType());
        $this->assertFalse($oauthAccount->shouldUseIncrementing());
        $this->assertEquals('ulid', $oauthAccount->getPrimaryKeyType());
    }

    public function test_oauth_account_generates_unique_ids_when_configured_for_ulid(): void
    {
        config(['userstamps.users_table_column_type' => 'ulid']);
        
        $oauthAccount = new OAuthAccount();
        
        $this->assertTrue(method_exists($oauthAccount, 'newUniqueId'));
        $this->assertNotEmpty($oauthAccount->newUniqueId());
        $this->assertEquals(['id'], $oauthAccount->uniqueIds());
    }

    public function test_oauth_account_generates_unique_ids_when_configured_for_uuid(): void
    {
        config(['userstamps.users_table_column_type' => 'uuid']);
        
        $oauthAccount = new OAuthAccount();
        
        $this->assertTrue(method_exists($oauthAccount, 'newUniqueId'));
        $this->assertNotEmpty($oauthAccount->newUniqueId());
        $this->assertEquals(['id'], $oauthAccount->uniqueIds());
    }

    public function test_oauth_account_does_not_generate_unique_ids_when_configured_for_bigincrements(): void
    {
        config(['userstamps.users_table_column_type' => 'bigincrements']);
        
        $oauthAccount = new OAuthAccount();
        
        $this->assertTrue(method_exists($oauthAccount, 'newUniqueId'));
        $this->assertEmpty($oauthAccount->uniqueIds());
    }
}
