<?php

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders the category create component successfully', function () {
    Livewire::test('admin.category.create')
        ->assertStatus(200);
});

it('creates a new category with a generated slug', function () {
    Livewire::test('admin.category.create')
        ->set('name', 'Handbags & Purses')
        ->set('description', 'Stylish leather handbags and purses.')
        ->call('createCategory')
        ->assertHasNoErrors()
        ->assertSet('name', '')
        ->assertSet('description', '');

    expect(Category::where('name', 'Handbags & Purses')->first())
        ->not->toBeNull()
        ->slug->toBe('handbags-purses')
        ->description->toBe('Stylish leather handbags and purses.');
});

it('requires a category name', function () {
    Livewire::test('admin.category.create')
        ->set('name', '')
        ->call('createCategory')
        ->assertHasErrors(['name' => 'required']);
});

it('can upload a category image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('category.jpg');

    Livewire::test('admin.category.create')
        ->set('name', 'Dresses')
        ->set('image_url', $file)
        ->call('createCategory')
        ->assertHasNoErrors();

    $category = Category::where('name', 'Dresses')->first();

    expect($category)->not->toBeNull();
    expect($category->image_url)->not->toBeNull();
    Storage::disk('public')->assertExists($category->image_url);
});

it('can edit a category and update its featured image in category manager', function () {
    Storage::fake('public');

    $category = Category::create([
        'name' => 'Accessories',
        'slug' => 'accessories',
        'description' => 'Fine jewelry & accessories',
        'image_url' => null,
    ]);

    expect($category->featured_image_url)->toContain('images.unsplash.com');

    // 1. Edit with custom web URL
    Livewire::test('admin.category-manager')
        ->call('editCategory', $category->id)
        ->set('custom_image_url', 'https://images.unsplash.com/photo-custom-necklace')
        ->call('saveCategory')
        ->assertHasNoErrors();

    expect($category->fresh()->image_url)->toBe('https://images.unsplash.com/photo-custom-necklace');
    expect($category->fresh()->featured_image_url)->toBe('https://images.unsplash.com/photo-custom-necklace');

    // 2. Edit with uploaded image
    $file = UploadedFile::fake()->image('necklace.jpg');

    Livewire::test('admin.category-manager')
        ->call('editCategory', $category->id)
        ->set('image', $file)
        ->call('saveCategory')
        ->assertHasNoErrors();

    $fresh = $category->fresh();
    expect($fresh->image_url)->toStartWith('/storage/categories/');
    expect($fresh->featured_image_url)->toStartWith('/storage/categories/');

    // 3. Remove image
    Livewire::test('admin.category-manager')
        ->call('editCategory', $category->id)
        ->call('removeImage')
        ->assertHasNoErrors();

    expect($category->fresh()->image_url)->toBeNull();
});

it('can delete a category in category manager', function () {
    $category = Category::create([
        'name' => 'Footwear',
        'slug' => 'footwear',
    ]);

    Livewire::test('admin.category-manager')
        ->call('deleteCategory', $category->id)
        ->assertHasNoErrors();

    expect(Category::find($category->id))->toBeNull();
});
