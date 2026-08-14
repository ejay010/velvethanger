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
