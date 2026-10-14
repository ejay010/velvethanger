<?php

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('redirects unauthenticated users accessing /admin to /admin/setup when no admin exists', function () {
    // Ensure no admin users exist in database
    User::where('is_admin', true)->delete();

    $response = $this->get('/admin');

    $response->assertRedirect(route('admin.setup'));
});

it('renders the setup component on /admin/setup when no admin user exists', function () {
    User::where('is_admin', true)->delete();

    $response = $this->get('/admin/setup');

    $response->assertStatus(200);
    $response->assertSee('First-Time Administrative Store Setup');
});

it('successfully creates the primary admin account, logs in, and locks setup', function () {
    User::where('is_admin', true)->delete();

    Livewire::test('admin.setup')
        ->set('name', 'Admin Client')
        ->set('email', 'owner@velvethanger.com')
        ->set('password', 'SecurePassword123!')
        ->set('password_confirmation', 'SecurePassword123!')
        ->call('createAdminAccount')
        ->assertRedirect(route('admin.dashboard'));

    // Verify admin created in database
    $admin = User::where('email', 'owner@velvethanger.com')->first();
    expect($admin)->not->toBeNull();
    expect($admin->is_admin)->toBeTrue();
    expect(Hash::check('SecurePassword123!', $admin->password))->toBeTrue();

    // Verify user is authenticated as admin
    $this->assertAuthenticatedAs($admin);
});

it('locks /admin/setup and redirects once an admin user already exists', function () {
    // Create an existing admin user
    User::factory()->create([
        'email' => 'existing_admin@velvethanger.com',
        'is_admin' => true,
    ]);

    // Livewire component mount redirect
    Livewire::test('admin.setup')
        ->assertRedirect(route('admin.dashboard'));

    // HTTP Route redirect
    $response = $this->get('/admin/setup');
    $response->assertRedirect(route('admin.dashboard'));
});

it('redirects /setup alias route to /admin/setup', function () {
    $response = $this->get('/setup');
    $response->assertRedirect('/admin/setup');
});

it('saves custom store title into SiteSetting during setup', function () {
    User::where('is_admin', true)->delete();

    Livewire::test('admin.setup')
        ->set('site_title', 'Velvet Hanger Boutique Nassau')
        ->set('name', 'Velvet Owner')
        ->set('email', 'boutique@velvethanger.com')
        ->set('password', 'SecretPassword123!')
        ->set('password_confirmation', 'SecretPassword123!')
        ->call('createAdminAccount')
        ->assertRedirect(route('admin.dashboard'));

    expect(SiteSetting::get('store_name'))->toBe('Velvet Hanger Boutique Nassau');
});
