<?php

use Livewire\Component;
use App\Models\SiteSetting;
use Flux\Flux;

new class extends Component
{
    // Announcement Bar
    public string $announcement_left = '';
    public string $announcement_right = '';
    public bool $announcement_enabled = true;

    // Hero Section
    public string $hero_eyebrow = '';
    public string $hero_headline = '';
    public string $hero_subtitle = '';
    public string $hero_cta_text = '';
    public string $hero_cta_link = '';
    public string $hero_image = '';

    // About / Story Section
    public string $about_heading = '';
    public string $about_body = '';
    public string $about_button_text = '';
    public string $about_image = '';

    // Store Info & Social
    public string $store_name = '';
    public string $store_address = '';
    public string $store_phone = '';
    public string $store_email = '';
    public string $store_instagram = '';
    public string $store_facebook = '';

    public function mount()
    {
        $this->announcement_left = (string) SiteSetting::get('announcement_left', 'Free Bahamas Shipping on Orders $200+');
        $this->announcement_right = (string) SiteSetting::get('announcement_right', 'Store Located in Nassau, Bahamas');
        $this->announcement_enabled = (bool) SiteSetting::get('announcement_enabled', true);

        $this->hero_eyebrow = (string) SiteSetting::get('hero_eyebrow', 'Effortless Elegance. Uniquely You.');
        $this->hero_headline = (string) SiteSetting::get('hero_headline', 'The Velvet Lifestyle');
        $this->hero_subtitle = (string) SiteSetting::get('hero_subtitle', 'Timeless style. Modern edge. Designed for women who dress with confidence.');
        $this->hero_cta_text = (string) SiteSetting::get('hero_cta_text', 'Shop New Arrivals');
        $this->hero_cta_link = (string) SiteSetting::get('hero_cta_link', '#featured-collection');
        $this->hero_image = (string) SiteSetting::get('hero_image', '');

        $this->about_heading = (string) SiteSetting::get('about_heading', 'About The Velvet Lifestyle');
        $this->about_body = (string) SiteSetting::get('about_body', '');
        $this->about_button_text = (string) SiteSetting::get('about_button_text', 'Our Story');
        $this->about_image = (string) SiteSetting::get('about_image', '');

        $this->store_name = (string) SiteSetting::get('store_name', 'The Velvet Lifestyle');
        $this->store_address = (string) SiteSetting::get('store_address', 'Palmdale, Tedder & Maderia Streets, Nassau, Bahamas');
        $this->store_phone = (string) SiteSetting::get('store_phone', '242.322.VELVET');
        $this->store_email = (string) SiteSetting::get('store_email', 'hello@thevelvetlifestyle.com');
        $this->store_instagram = (string) SiteSetting::get('store_instagram', 'https://instagram.com');
        $this->store_facebook = (string) SiteSetting::get('store_facebook', 'https://facebook.com');
    }

    public function saveSettings()
    {
        SiteSetting::set('announcement_left', $this->announcement_left);
        SiteSetting::set('announcement_right', $this->announcement_right);
        SiteSetting::set('announcement_enabled', $this->announcement_enabled ? '1' : '0');

        SiteSetting::set('hero_eyebrow', $this->hero_eyebrow);
        SiteSetting::set('hero_headline', $this->hero_headline);
        SiteSetting::set('hero_subtitle', $this->hero_subtitle);
        SiteSetting::set('hero_cta_text', $this->hero_cta_text);
        SiteSetting::set('hero_cta_link', $this->hero_cta_link);
        SiteSetting::set('hero_image', $this->hero_image);

        SiteSetting::set('about_heading', $this->about_heading);
        SiteSetting::set('about_body', $this->about_body);
        SiteSetting::set('about_button_text', $this->about_button_text);
        SiteSetting::set('about_image', $this->about_image);

        SiteSetting::set('store_name', $this->store_name);
        SiteSetting::set('store_address', $this->store_address);
        SiteSetting::set('store_phone', $this->store_phone);
        SiteSetting::set('store_email', $this->store_email);
        SiteSetting::set('store_instagram', $this->store_instagram);
        SiteSetting::set('store_facebook', $this->store_facebook);

        Flux::toast('Storefront settings updated successfully!', variant: 'success');
    }
};
?>

<div class="max-w-6xl mx-auto py-8 px-4 space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Storefront Content Manager</flux:heading>
            <flux:subheading>Update homepage banners, hero editorial, story copy, and store contact information.</flux:subheading>
        </div>

        <flux:button variant="primary" wire:click="saveSettings">
            Save Changes
        </flux:button>
    </div>

    <form wire:submit.prevent="saveSettings" class="space-y-8">
        
        {{-- Section 1: Announcement Bar --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <flux:heading size="lg">Top Announcement Bar</flux:heading>
                    <flux:subheading>Displayed at the very top of every page.</flux:subheading>
                </div>
                <flux:switch wire:model="announcement_enabled" label="Enable Bar" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input label="Left Announcement Text" wire:model="announcement_left" placeholder="e.g. Free Bahamas Shipping on Orders $200+" />
                <flux:input label="Right Announcement Text" wire:model="announcement_right" placeholder="e.g. Store Located in Nassau, Bahamas" />
            </div>
        </flux:card>

        {{-- Section 2: Hero Section --}}
        <flux:card class="space-y-4">
            <div class="border-b pb-3">
                <flux:heading size="lg">Homepage Hero Banner</flux:heading>
                <flux:subheading>The main visual banner that greets customers on the storefront.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input label="Eyebrow Subheading" wire:model="hero_eyebrow" placeholder="Effortless Elegance. Uniquely You." />
                <flux:input label="Main Headline" wire:model="hero_headline" placeholder="The Velvet Lifestyle" />
            </div>

            <flux:textarea label="Hero Blurb / Subtitle" wire:model="hero_subtitle" rows="2" placeholder="Short inspirational description" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input label="CTA Button Text" wire:model="hero_cta_text" placeholder="Shop New Arrivals" />
                <flux:input label="CTA Button Link / Anchor" wire:model="hero_cta_link" placeholder="#featured-collection" />
            </div>

            <div>
                <flux:input label="Hero Image URL" wire:model="hero_image" placeholder="https://..." />
                @if($hero_image)
                    <div class="mt-2 aspect-[16/7] max-w-md rounded-lg overflow-hidden border">
                        <img src="{{ $hero_image }}" alt="Hero Preview" class="w-full h-full object-cover" />
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Section 3: About Story --}}
        <flux:card class="space-y-4">
            <div class="border-b pb-3">
                <flux:heading size="lg">About Boutique Story</flux:heading>
                <flux:subheading>Featured spotlight about your boutique's history and mission in Nassau.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input label="Section Heading" wire:model="about_heading" placeholder="About The Velvet Lifestyle" />
                <flux:input label="Button Label" wire:model="about_button_text" placeholder="Our Story" />
            </div>

            <flux:textarea label="Story Body Text" wire:model="about_body" rows="4" placeholder="Tell your customers about the boutique..." />

            <div>
                <flux:input label="Boutique / Storefront Image URL" wire:model="about_image" placeholder="https://..." />
                @if($about_image)
                    <div class="mt-2 aspect-[4/3] max-w-xs rounded-lg overflow-hidden border">
                        <img src="{{ $about_image }}" alt="About Preview" class="w-full h-full object-cover" />
                    </div>
                @endif
            </div>
        </flux:card>

        {{-- Section 4: Store Info & Social --}}
        <flux:card class="space-y-4">
            <div class="border-b pb-3">
                <flux:heading size="lg">Store Information & Contact</flux:heading>
                <flux:subheading>Boutique location, phone number, and social links displayed in the footer.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input label="Store Name" wire:model="store_name" />
                <flux:input label="Contact Phone" wire:model="store_phone" placeholder="242.322.VELVET" />
                <flux:input type="email" label="Contact Email" wire:model="store_email" placeholder="hello@thevelvetlifestyle.com" />
            </div>

            <flux:input label="Physical Boutique Address" wire:model="store_address" placeholder="Palmdale, Tedder & Maderia Streets, Nassau, Bahamas" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input label="Instagram URL" wire:model="store_instagram" placeholder="https://instagram.com/..." />
                <flux:input label="Facebook URL" wire:model="store_facebook" placeholder="https://facebook.com/..." />
            </div>
        </flux:card>

        <div class="flex justify-end pt-4">
            <flux:button type="submit" variant="primary">
                Save Changes
            </flux:button>
        </div>

    </form>
</div>
