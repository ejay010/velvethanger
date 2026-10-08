<?php

use Livewire\Component;
use App\Models\Page;
use Flux\Flux;
use Illuminate\Support\Str;

new class extends Component
{
    // Page form attributes
    public ?int $editingPageId = null;
    public string $title = '';
    public string $slug = '';
    public string $meta_description = '';
    public string $content = '';
    public bool $is_published = true;
    public int $sort_order = 0;

    public bool $showModal = false;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . ($this->editingPageId ?? 'NULL'),
            'meta_description' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'is_published' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function getPagesProperty()
    {
        return Page::orderBy('sort_order')->orderBy('title')->get();
    }

    public function updatedTitle($value)
    {
        if (! $this->editingPageId) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreateModal()
    {
        $this->reset(['editingPageId', 'title', 'slug', 'meta_description', 'content', 'sort_order']);
        $this->is_published = true;
        $this->showModal = true;
    }

    public function editPage(int $pageId)
    {
        $page = Page::findOrFail($pageId);
        $this->editingPageId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->meta_description = $page->meta_description ?? '';
        $this->content = $page->content ?? '';
        $this->is_published = (bool) $page->is_published;
        $this->sort_order = (int) $page->sort_order;

        $this->showModal = true;
    }

    public function savePage()
    {
        $this->validate();

        if ($this->editingPageId) {
            $page = Page::findOrFail($this->editingPageId);
            $page->update([
                'title' => $this->title,
                'slug' => Str::slug($this->slug),
                'meta_description' => $this->meta_description,
                'content' => $this->content,
                'is_published' => $this->is_published,
                'sort_order' => $this->sort_order,
            ]);
            Flux::toast('Page updated successfully!', variant: 'success');
        } else {
            Page::create([
                'title' => $this->title,
                'slug' => Str::slug($this->slug),
                'meta_description' => $this->meta_description,
                'content' => $this->content,
                'is_published' => $this->is_published,
                'sort_order' => $this->sort_order,
            ]);
            Flux::toast('Page created successfully!', variant: 'success');
        }

        $this->showModal = false;
        $this->reset(['editingPageId', 'title', 'slug', 'meta_description', 'content', 'sort_order']);
    }

    public function togglePublish(int $pageId)
    {
        $page = Page::findOrFail($pageId);
        $page->update(['is_published' => ! $page->is_published]);

        $status = $page->is_published ? 'published' : 'draft';
        Flux::toast("Page marked as {$status}.", variant: 'success');
    }

    public function deletePage(int $pageId)
    {
        $page = Page::findOrFail($pageId);
        $page->delete();

        Flux::toast('Page deleted successfully.', variant: 'success');
    }
};
?>

<div class="max-w-6xl mx-auto py-8 px-4 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Custom Pages Manager</flux:heading>
            <flux:subheading>Manage informational, customer care, and policy pages across your boutique.</flux:subheading>
        </div>

        <flux:button variant="primary" wire:click="openCreateModal">
            + New Page
        </flux:button>
    </div>

    <flux:card>
        @if($this->pages->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <p class="font-medium">No custom pages created yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Title</th>
                            <th class="py-3 px-4">URL Slug</th>
                            <th class="py-3 px-4">Order</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        @foreach($this->pages as $page)
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">
                                    {{ $page->title }}
                                </td>
                                <td class="py-3 px-4 text-gray-500 font-mono text-xs">
                                    <a href="/pages/{{ $page->slug }}" target="_blank" class="hover:underline text-indigo-600">
                                        /pages/{{ $page->slug }} ↗
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-gray-500">
                                    {{ $page->sort_order }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($page->is_published)
                                        <flux:badge color="green" size="sm">Published</flux:badge>
                                    @else
                                        <flux:badge color="gray" size="sm">Draft</flux:badge>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <flux:button size="sm" variant="ghost" wire:click="togglePublish({{ $page->id }})">
                                        {{ $page->is_published ? 'Unpublish' : 'Publish' }}
                                    </flux:button>
                                    <flux:button size="sm" variant="primary" wire:click="editPage({{ $page->id }})">
                                        Edit
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="deletePage({{ $page->id }})">
                                        Delete
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </flux:card>

    {{-- Create / Edit Page Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl max-w-2xl w-full p-6 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center border-b pb-3">
                    <flux:heading size="lg">{{ $editingPageId ? 'Edit Page' : 'Create New Page' }}</flux:heading>
                    <button type="button" wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form wire:submit.prevent="savePage" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input label="Page Title" wire:model.live="title" placeholder="e.g. Return Policy" required />
                        <flux:input label="URL Slug" wire:model="slug" placeholder="e.g. return-policy" required />
                    </div>

                    <flux:input label="Meta Description (SEO)" wire:model="meta_description" placeholder="Brief summary of this page..." />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                        <flux:input type="number" label="Sort Order" wire:model="sort_order" />
                        <div class="pt-4">
                            <flux:switch label="Publish Immediately" wire:model="is_published" />
                        </div>
                    </div>

                    <div>
                        <flux:textarea label="Page Content (Markdown / HTML supported)" wire:model="content" rows="10" placeholder="Write page content here..." />
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t">
                        <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancel</flux:button>
                        <flux:button type="submit" variant="primary">Save Page</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
