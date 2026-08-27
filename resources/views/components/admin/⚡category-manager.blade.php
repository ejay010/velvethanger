<?php

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str;
use Flux\Flux;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $description = '';
    public $image;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048', // Max 2MB image
    ];

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get();
    }

    public function createCategory()
    {
        $this->validate();

        $imageUrl = null;
        if ($this->image) {
            // Store the image in the 'public' disk inside a 'categories' folder
            $path = $this->image->store('categories', 'public');
            $imageUrl = '/storage/' . $path;
        }

        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'image_url' => $imageUrl,
        ]);

        $this->reset(['name', 'description', 'image']);
        Flux::toast('Category created successfully.', variant: 'success');
    }
};
?>

<div class="max-w-6xl mx-auto py-8 px-4">
    <flux:heading size="xl" class="mb-6">Category Manager</flux:heading>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        {{-- Form to create a new category --}}
        <div class="md:col-span-1">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Add New Category</flux:heading>
                <form wire:submit.prevent="createCategory" class="space-y-4">
                    <flux:input label="Category Name" wire:model="name" required />
                    
                    <flux:textarea label="Description" wire:model="description" />
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-indigo-50 file:text-indigo-700
                            hover:file:bg-indigo-100" />
                        @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        
                        @if ($image)
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-1">Image Preview:</p>
                                <img src="{{ $image->temporaryUrl() }}" class="w-full max-h-48 object-cover rounded-lg">
                            </div>
                        @endif
                    </div>

                    <flux:button type="submit" variant="primary" class="w-full">Create Category</flux:button>
                </form>
            </flux:card>
        </div>

        {{-- List of existing categories --}}
        <div class="md:col-span-2">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Existing Categories</flux:heading>
                
                @if($this->categories->isEmpty())
                    <p class="text-gray-500">No categories found.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($this->categories as $category)
                            <div class="flex items-center p-4 border rounded-lg">
                                @if($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-16 h-16 object-cover rounded-md mr-4">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-md mr-4 flex items-center justify-center text-gray-500">No Img</div>
                                @endif
                                <div>
                                    <h3 class="font-medium text-lg">{{ $category->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $category->products()->count() }} Products</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>

    </div>
</div>