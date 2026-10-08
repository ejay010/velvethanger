<?php

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str;
use Flux\Flux;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?int $editingCategoryId = null;
    public string $name = '';
    public string $description = '';
    public string $custom_image_url = '';
    public ?string $existing_image_url = null;
    public $image;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'custom_image_url' => 'nullable|url|max:500',
            'image' => 'nullable|image|max:2048', // Max 2MB image
        ];
    }

    public function getCategoriesProperty()
    {
        return Category::withCount('products')->orderBy('name')->get();
    }

    public function editCategory(int $id)
    {
        $category = Category::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->existing_image_url = $category->featured_image_url;
        $this->custom_image_url = str_starts_with($category->image_url ?? '', 'http') ? $category->image_url : '';
        $this->image = null;
    }

    public function cancelEdit()
    {
        $this->reset(['editingCategoryId', 'name', 'description', 'custom_image_url', 'existing_image_url', 'image']);
    }

    public function saveCategory()
    {
        $this->validate();

        $imageUrl = $this->custom_image_url ?: null;

        if ($this->image) {
            // Store the uploaded file in public/categories
            $path = $this->image->store('categories', 'public');
            $imageUrl = '/storage/' . $path;
        }

        if ($this->editingCategoryId) {
            $category = Category::findOrFail($this->editingCategoryId);
            
            $data = [
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'description' => $this->description,
            ];

            if ($imageUrl !== null || !empty($this->custom_image_url)) {
                $data['image_url'] = $imageUrl;
            }

            $category->update($data);
            Flux::toast("Category '{$category->name}' updated successfully.", variant: 'success');
        } else {
            Category::create([
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'description' => $this->description,
                'image_url' => $imageUrl,
            ]);
            Flux::toast("Category '{$this->name}' created successfully.", variant: 'success');
        }

        $this->cancelEdit();
    }

    public function removeImage()
    {
        if ($this->editingCategoryId) {
            $category = Category::findOrFail($this->editingCategoryId);
            $category->update(['image_url' => null]);
            $this->existing_image_url = null;
            Flux::toast('Featured image removed.', variant: 'success');
        }
    }

    public function deleteCategory(int $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        if ($this->editingCategoryId === $id) {
            $this->cancelEdit();
        }

        Flux::toast('Category deleted successfully.', variant: 'success');
    }
};
?>

<div class="max-w-6xl mx-auto py-8 px-4 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Category Manager</flux:heading>
            <flux:subheading>Manage boutique product categories and customize their featured spotlight images.</flux:subheading>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Form to create or edit a category --}}
        <div class="lg:col-span-4">
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <flux:heading size="lg">
                        {{ $editingCategoryId ? 'Edit Category' : 'Add New Category' }}
                    </flux:heading>
                    @if($editingCategoryId)
                        <flux:button size="xs" variant="ghost" wire:click="cancelEdit">
                            Cancel
                        </flux:button>
                    @endif
                </div>

                <form wire:submit.prevent="saveCategory" class="space-y-4">
                    <flux:input label="Category Name" wire:model="name" placeholder="e.g. Dresses, Tops, Accessories" required />
                    
                    <flux:textarea label="Description" wire:model="description" rows="2" placeholder="Brief category description..." />
                    
                    {{-- Featured Image Upload & URL --}}
                    <div class="space-y-3 pt-2 border-t">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Featured Image
                        </label>
                        
                        {{-- Current / Existing Image Preview --}}
                        @if ($image)
                            <div class="space-y-1">
                                <span class="text-xs text-gray-500">New Image Preview:</span>
                                <div class="aspect-[3/4] max-h-48 rounded-lg overflow-hidden border">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                </div>
                            </div>
                        @elseif ($existing_image_url)
                            <div class="space-y-2">
                                <div class="aspect-[3/4] max-h-48 rounded-lg overflow-hidden border relative group">
                                    <img src="{{ $existing_image_url }}" class="w-full h-full object-cover">
                                </div>
                                <button type="button" wire:click="removeImage" class="text-xs text-red-600 hover:underline">
                                    Remove Current Image
                                </button>
                            </div>
                        @endif

                        {{-- File Upload --}}
                        <div>
                            <input type="file" wire:model="image" accept="image/*" class="block w-full text-xs text-gray-500
                                file:mr-3 file:py-1.5 file:px-3
                                file:rounded-md file:border-0
                                file:text-xs file:font-semibold
                                file:bg-zinc-100 file:text-zinc-700
                                hover:file:bg-zinc-200" />
                            @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Or Custom Image URL --}}
                        <div>
                            <flux:input label="Or Image Web URL" wire:model="custom_image_url" placeholder="https://images.unsplash.com/..." />
                        </div>
                    </div>

                    <div class="pt-2">
                        <flux:button type="submit" variant="primary" class="w-full">
                            {{ $editingCategoryId ? 'Save Changes' : 'Create Category' }}
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>

        {{-- List of existing categories --}}
        <div class="lg:col-span-8">
            <flux:card>
                <div class="border-b pb-3 mb-4">
                    <flux:heading size="lg">Existing Categories ({{ $this->categories->count() }})</flux:heading>
                </div>
                
                @if($this->categories->isEmpty())
                    <div class="text-center py-12 text-gray-500">
                        <p>No categories found.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($this->categories as $category)
                            <div class="flex items-center justify-between p-3.5 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:border-zinc-400 transition-colors {{ $editingCategoryId === $category->id ? 'ring-2 ring-indigo-500' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-18 bg-zinc-100 dark:bg-zinc-800 rounded-md overflow-hidden shrink-0 border">
                                        <img src="{{ $category->featured_image_url }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-sm text-gray-900 dark:text-white">{{ $category->name }}</h3>
                                        <p class="text-xs text-gray-500">{{ $category->products_count }} {{ Str::plural('Product', $category->products_count) }}</p>
                                        <span class="text-[10px] text-gray-400 font-mono">/{{ $category->slug }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1">
                                    <flux:button size="xs" variant="ghost" wire:click="editCategory({{ $category->id }})">
                                        Edit
                                    </flux:button>
                                    <flux:button size="xs" variant="ghost" class="text-red-500 hover:text-red-700" wire:click="deleteCategory({{ $category->id }})">
                                        ✕
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>

    </div>
</div>