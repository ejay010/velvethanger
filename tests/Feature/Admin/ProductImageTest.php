<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('allows uploading multiple product images and selecting a featured image', function () {
    Storage::fake('public');

    $category = Category::factory()->create();

    $file1 = UploadedFile::fake()->image('front.jpg');
    $file2 = UploadedFile::fake()->image('back.jpg');

    Livewire::test('admin.product-manager')
        ->set('name', 'Velvet Blazer')
        ->set('category_id', $category->id)
        ->set('price', 8900)
        ->set('new_images', [$file1, $file2])
        ->set('featured_image_index', 1) // Set 2nd image (back.jpg) as featured
        ->call('createProduct')
        ->assertHasNoErrors();

    $product = Product::where('name', 'Velvet Blazer')->first();

    expect($product)->not->toBeNull();
    expect($product->images)->toHaveCount(2);

    $featuredImage = $product->featuredImage;
    expect($featuredImage)->not->toBeNull();
    expect($featuredImage->is_featured)->toBeTrue();

    // Verify storage
    Storage::disk('public')->assertExists($featuredImage->image_path);
    expect($product->featured_image_url)->toContain(Storage::url($featuredImage->image_path));
});
