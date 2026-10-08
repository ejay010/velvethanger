<?php

use Livewire\Component;
use App\Models\Page;
use Illuminate\Support\Str;

new class extends Component
{
    public Page $page;

    public function mount(Page $page)
    {
        if (! $page->is_published && ! auth()->user()?->is_admin) {
            abort(404);
        }

        $this->page = $page;
    }
};
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white border border-zinc-200/80 p-6 sm:p-12 shadow-xs space-y-8">
        {{-- Breadcrumb --}}
        <div class="text-[10px] uppercase tracking-[0.25em] text-zinc-400">
            <a href="/" class="hover:text-black transition-colors">Home</a>
            <span class="mx-2">/</span>
            <span class="text-zinc-700">Customer Care</span>
        </div>

        {{-- Page Header --}}
        <div class="border-b border-zinc-200 pb-6">
            <h1 class="font-serif text-3xl sm:text-4xl text-zinc-950 font-normal tracking-wide">
                {{ $page->title }}
            </h1>
            @if($page->meta_description)
                <p class="text-xs sm:text-sm text-zinc-500 mt-2 font-light">
                    {{ $page->meta_description }}
                </p>
            @endif
        </div>

        {{-- Page Content Formatted with Markdown / HTML --}}
        <div class="prose prose-zinc max-w-none prose-headings:font-serif prose-headings:font-normal prose-headings:tracking-wider prose-table:text-sm prose-th:bg-zinc-50 prose-th:p-3 prose-td:p-3 leading-relaxed text-zinc-700 text-sm">
            {!! Str::markdown($page->content ?? '') !!}
        </div>

        {{-- Help footer box --}}
        <div class="mt-12 pt-6 border-t border-zinc-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-zinc-500 bg-[#faf8f5] p-4 border border-zinc-200">
            <span>Have questions or need assistance?</span>
            <a href="mailto:hello@thevelvetlifestyle.com" class="font-semibold text-black uppercase tracking-wider hover:underline">
                Contact Customer Care →
            </a>
        </div>
    </div>
</div>
