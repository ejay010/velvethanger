<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-[#faf8f5] text-zinc-900 font-sans antialiased flex flex-col selection:bg-amber-100 selection:text-amber-900">

    {{-- Top Luxury Announcement Bar --}}
    <div class="bg-black text-white text-[11px] font-medium tracking-[0.2em] uppercase py-2.5 px-4 border-b border-white/10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <span>Free Bahamas Shipping on Orders $200+</span>
            <span class="text-zinc-400">Store Located in Nassau, Bahamas</span>
        </div>
    </div>

    {{-- Main Luxury Storefront Header --}}
    <header class="bg-[#faf8f5] border-b border-zinc-200 sticky top-0 z-40 backdrop-blur-md bg-opacity-95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="grid grid-cols-3 items-center">
                
                {{-- Left: Search & Mobile Navigation --}}
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}?search=1" class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.18em] font-medium text-zinc-700 hover:text-black transition-colors">
                        <flux:icon.magnifying-glass class="w-4 h-4 stroke-[1.5]" />
                        <span class="hidden sm:inline">Search</span>
                    </a>
                </div>

                {{-- Center: Grand Luxury Typography Logo --}}
                <div class="flex justify-center text-center">
                    <a href="{{ route('home') }}" class="group flex flex-col items-center select-none" wire:navigate>
                        <span class="text-[10px] tracking-[0.35em] uppercase text-zinc-500 font-medium group-hover:text-amber-800 transition-colors">The</span>
                        <span class="font-serif text-2xl sm:text-3xl lg:text-4xl tracking-[0.22em] font-medium uppercase text-zinc-950 leading-tight">
                            Velvet
                        </span>
                        <span class="font-serif text-xs sm:text-sm tracking-[0.35em] uppercase text-zinc-700 font-normal mt-0.5">
                            Lifestyle
                        </span>
                        <span class="text-[9px] tracking-[0.3em] uppercase text-zinc-400 mt-0.5">
                            Nassau, Bahamas
                        </span>
                    </a>
                </div>

                {{-- Right: Account & Cart Bag --}}
                <div class="flex items-center justify-end gap-5">
                    <a href="{{ route('order.lookup') }}" class="text-xs uppercase tracking-[0.15em] font-medium text-zinc-700 hover:text-black hidden md:inline transition-colors" wire:navigate>
                        Track Order
                    </a>

                    @auth
                        <x-desktop-user-menu />
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs uppercase tracking-[0.18em] font-medium text-zinc-700 hover:text-black transition-colors" wire:navigate>
                            <flux:icon.user class="w-4 h-4 stroke-[1.5]" />
                            <span class="hidden sm:inline">Account</span>
                        </a>
                    @endauth

                    <a href="{{ route('cart') }}" class="hover:opacity-80 transition-opacity" wire:navigate>
                        <livewire:storefront.cart-badge />
                    </a>
                </div>
            </div>
        </div>

        {{-- Sub-Navigation Category Bar --}}
        <nav class="border-t border-zinc-200/80 bg-[#faf8f5]/80 py-3 hidden md:block">
            <div class="max-w-7xl mx-auto px-4 flex items-center justify-center space-x-7 text-xs font-medium uppercase tracking-[0.2em] text-zinc-700">
                <a href="{{ route('home') }}" class="hover:text-black transition-colors {{ request()->routeIs('home') && !request('category') ? 'text-black font-semibold' : '' }}" wire:navigate>
                    New Arrivals
                </a>
                <a href="{{ route('home') }}?category=clothing" class="hover:text-black transition-colors" wire:navigate>
                    Clothing
                </a>
                <a href="{{ route('home') }}?category=dresses" class="hover:text-black transition-colors" wire:navigate>
                    Dresses
                </a>
                <a href="{{ route('home') }}?category=tops" class="hover:text-black transition-colors" wire:navigate>
                    Tops
                </a>
                <a href="{{ route('home') }}?category=bottoms" class="hover:text-black transition-colors" wire:navigate>
                    Bottoms
                </a>
                <a href="{{ route('home') }}?category=accessories" class="hover:text-black transition-colors" wire:navigate>
                    Accessories
                </a>
                <a href="{{ route('home') }}?category=shoes" class="hover:text-black transition-colors" wire:navigate>
                    Shoes
                </a>
                <a href="{{ route('home') }}?category=sale" class="text-[#c75d4d] hover:text-[#b04a3b] font-semibold transition-colors" wire:navigate>
                    Sale
                </a>
                <a href="#about-story" class="hover:text-black transition-colors">
                    About
                </a>
                <a href="#footer-contact" class="hover:text-black transition-colors">
                    Contact
                </a>
            </div>
        </nav>
    </header>

    {{-- Main Content --}}
    <main class="flex-grow">
        {{ $slot }}
    </main>

    {{-- Luxury Boutique Footer --}}
    <footer id="footer-contact" class="bg-[#0f0e0d] text-zinc-300 pt-16 pb-12 border-t border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">
                
                {{-- Col 1: Brand Info --}}
                <div class="space-y-4">
                    <div>
                        <span class="font-serif text-lg tracking-[0.2em] font-medium uppercase text-white block">
                            The Velvet Lifestyle
                        </span>
                        <span class="text-xs text-zinc-400 tracking-wider">Nassau, Bahamas</span>
                    </div>
                    <p class="text-xs text-zinc-400 leading-relaxed">
                        Women's Boutique<br>
                        EST. 2009
                    </p>
                    <div class="flex items-center gap-3 pt-2 text-zinc-400">
                        <a href="https://instagram.com" target="_blank" rel="noopener" class="hover:text-white transition-colors" aria-label="Instagram">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://facebook.com" target="_blank" rel="noopener" class="hover:text-white transition-colors" aria-label="Facebook">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.667 5H18V0h-3.808C10.595 0 9 1.583 9 4.615V8z"/></svg>
                        </a>
                        <a href="mailto:hello@thevelvetlifestyle.com" class="hover:text-white transition-colors" aria-label="Email Us">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M0 3v18h24v-18h-24zm21.518 2l-9.518 7.713-9.518-7.713h19.036zm-19.518 14v-11.817l10 8.104 10-8.104v11.817h-20z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Col 2: Shop Links --}}
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white mb-4">Shop</div>
                    <ul class="space-y-2 text-xs text-zinc-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">New Arrivals</a></li>
                        <li><a href="{{ route('home') }}?category=clothing" class="hover:text-white transition-colors">Clothing</a></li>
                        <li><a href="{{ route('home') }}?category=dresses" class="hover:text-white transition-colors">Dresses</a></li>
                        <li><a href="{{ route('home') }}?category=tops" class="hover:text-white transition-colors">Tops</a></li>
                        <li><a href="{{ route('home') }}?category=bottoms" class="hover:text-white transition-colors">Bottoms</a></li>
                        <li><a href="{{ route('home') }}?category=accessories" class="hover:text-white transition-colors">Accessories</a></li>
                        <li><a href="{{ route('home') }}?category=shoes" class="hover:text-white transition-colors">Shoes</a></li>
                        <li><a href="{{ route('home') }}?category=sale" class="text-[#d96b5a] hover:text-[#e58373] transition-colors">Sale</a></li>
                    </ul>
                </div>

                {{-- Col 3: Customer Care --}}
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white mb-4">Customer Care</div>
                    <ul class="space-y-2 text-xs text-zinc-400">
                        <li><a href="{{ route('order.lookup') }}" class="hover:text-white transition-colors">Track Your Order</a></li>
                        <li><a href="#about-story" class="hover:text-white transition-colors">Shipping Information</a></li>
                        <li><a href="#about-story" class="hover:text-white transition-colors">Returns & Exchanges</a></li>
                        <li><a href="#about-story" class="hover:text-white transition-colors">Size Guide</a></li>
                        <li><a href="#about-story" class="hover:text-white transition-colors">Store Policy</a></li>
                        <li><a href="mailto:hello@thevelvetlifestyle.com" class="hover:text-white transition-colors">Contact Us</a></li>
                    </ul>
                </div>

                {{-- Col 4: Store Info --}}
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white mb-4">Store Info</div>
                    <div class="space-y-2 text-xs text-zinc-400 leading-relaxed">
                        <p>Palmdale, Tedder & Maderia Streets<br>Nassau, Bahamas</p>
                        <p class="pt-1 font-medium text-zinc-300">242.322.VELVET</p>
                        <p><a href="mailto:hello@thevelvetlifestyle.com" class="hover:text-white transition-colors">hello@thevelvetlifestyle.com</a></p>
                    </div>
                </div>

                {{-- Col 5: Newsletter / Join the List --}}
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white mb-4">Join the List</div>
                    <p class="text-xs text-zinc-400 leading-relaxed mb-4">
                        Be the first to know about new arrivals, exclusives & more.
                    </p>
                    <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to Velvet Lifestyle!');" class="flex items-center border-b border-zinc-700 pb-1 focus-within:border-white transition-colors">
                        <input type="email" placeholder="Enter your email" required class="bg-transparent text-xs text-white placeholder-zinc-500 w-full focus:outline-none" />
                        <button type="submit" class="text-zinc-400 hover:text-white transition-colors p-1" aria-label="Subscribe">
                            →
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div class="border-t border-zinc-900 pt-8 text-center text-[10px] tracking-[0.2em] uppercase text-zinc-500">
                © {{ date('Y') }} THE VELVET LIFESTYLE. ALL RIGHTS RESERVED.
            </div>
        </div>
    </footer>

    @fluxScripts
</body>
</html>

