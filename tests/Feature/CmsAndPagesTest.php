<?php

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('site settings can be set, retrieved, and cached', function () {
    SiteSetting::set('store_phone', '242.322.8358');

    expect(SiteSetting::get('store_phone'))->toBe('242.322.8358');
    expect(SiteSetting::get('non_existent_key', 'default_val'))->toBe('default_val');
});

test('admin can update storefront content and site settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    Livewire::test('admin.content-manager')
        ->set('hero_headline', 'New Spring Collection')
        ->set('announcement_left', 'Special Island Offer')
        ->call('saveSettings')
        ->assertHasNoErrors();

    expect(SiteSetting::get('hero_headline'))->toBe('New Spring Collection');
    expect(SiteSetting::get('announcement_left'))->toBe('Special Island Offer');
});

test('admin can create, edit, toggle publish, and delete custom pages', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    // 1. Create a new page
    Livewire::test('admin.page-manager')
        ->set('title', 'Island VIP Club')
        ->set('slug', 'island-vip-club')
        ->set('content', '# VIP Perks in Nassau')
        ->set('is_published', true)
        ->call('savePage')
        ->assertHasNoErrors();

    $page = Page::where('slug', 'island-vip-club')->first();
    expect($page)->not->toBeNull();
    expect($page->title)->toBe('Island VIP Club');

    // 2. Edit the page
    Livewire::test('admin.page-manager')
        ->call('editPage', $page->id)
        ->set('title', 'Island VIP Loyalty Program')
        ->call('savePage');

    expect($page->fresh()->title)->toBe('Island VIP Loyalty Program');

    // 3. Toggle publish
    Livewire::test('admin.page-manager')
        ->call('togglePublish', $page->id);

    expect($page->fresh()->is_published)->toBeFalse();

    // 4. Delete page
    Livewire::test('admin.page-manager')
        ->call('deletePage', $page->id);

    expect(Page::find($page->id))->toBeNull();
});

test('storefront renders published custom pages', function () {
    $page = Page::create([
        'title' => 'Bahamas Delivery Guide',
        'slug' => 'delivery-guide',
        'content' => '## Fast delivery to Nassau & Family Islands',
        'meta_description' => 'Delivery details across the Bahamas',
        'is_published' => true,
    ]);

    $response = $this->get('/pages/delivery-guide');

    $response->assertStatus(200);
    $response->assertSee('Bahamas Delivery Guide');
    $response->assertSee('Fast delivery to Nassau');
});

test('storefront returns 404 for unpublished pages for guests', function () {
    $page = Page::create([
        'title' => 'Secret Sale',
        'slug' => 'secret-sale',
        'content' => 'Hidden details',
        'is_published' => false,
    ]);

    $response = $this->get('/pages/secret-sale');
    $response->assertStatus(404);
});
