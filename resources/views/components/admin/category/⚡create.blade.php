<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Flux\Flux;
use App\Models\Category; // Import correct singular model name
use Illuminate\Support\Str; // Helper to generate URL-friendly slugs

new class extends Component {
    use WithFileUploads;

    // Public properties mapped to form inputs via wire:model
    public string $name = '';
    public string $description = '';

    // Note: Do NOT strictly type file upload properties as string!
    // Livewire assigns a TemporaryUploadedFile object to $image_url when a file is selected.
    public $image_url = null;

    // Livewire validation rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        // Use standard 'image' or 'mimes:jpeg,png...' rules for file validation
        'image_url' => 'nullable|image|max:2048',
    ];

    public function createCategory()
    {
        // 1. Validate form fields against $rules
        $this->validate();

        // 2. Handle image upload if a file was selected
        $imagePath = null;
        if ($this->image_url) {
            // Stores file in storage/app/public/categories and returns relative path
            $imagePath = $this->image_url->store('categories', 'public');
        }

        // 3. Create the Category record in database
        // Generating a unique slug from the category name (required by migration)
        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'image_url' => $imagePath,
        ]);

        // 4. Reset form fields to initial state
        $this->reset(['name', 'description', 'image_url']);

        // 5. Provide instant feedback using Flux UI toast notification
        Flux::toast('Category created successfully.', variant: 'success');
    }
};
?>

<div>
    <flux:card>
        <flux:heading size="lg" class="mb-4">Create Category</flux:heading>
        <form wire:submit.prevent="createCategory" class="space-y-4">
            <flux:input label="Category Name" wire:model="name" required />
            <flux:textarea label="Description" wire:model="description" />
            <flux:input type="file" label="Image" wire:model="image_url" name="image_url"
                accept="image/jpeg,image/png,image/gif,image/jpg,image/webp,image/avif"
                hint="Only JPEG, PNG, JPG, GIF, WEBP, AVIF" />
            <flux:button type="submit" variant="primary">Create Category</flux:button>
        </form>
    </flux:card>
</div>

