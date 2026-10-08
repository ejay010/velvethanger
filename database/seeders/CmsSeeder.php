<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Default Site Settings
        $settings = [
            // Announcement Bar
            'announcement_left' => 'Free Bahamas Shipping on Orders $200+',
            'announcement_right' => 'Store Located in Nassau, Bahamas',
            'announcement_enabled' => '1',

            // Hero Section
            'hero_eyebrow' => 'Effortless Elegance. Uniquely You.',
            'hero_headline' => 'The Velvet Lifestyle',
            'hero_subtitle' => 'Timeless style. Modern edge. Designed for women who dress with confidence.',
            'hero_cta_text' => 'Shop New Arrivals',
            'hero_cta_link' => '#featured-collection',
            'hero_image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=1200&auto=format&fit=crop',

            // About Boutique Story
            'about_heading' => 'About The Velvet Lifestyle',
            'about_body' => 'For over 15 years, The Velvet Lifestyle has been Nassau\'s premier destination for chic, sophisticated style. We curate collections that empower women to look and feel their best—every day and every occasion.',
            'about_button_text' => 'Our Story',
            'about_image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1000&auto=format&fit=crop',

            // Store Info & Contact
            'store_name' => 'The Velvet Lifestyle',
            'store_address' => 'Palmdale, Tedder & Maderia Streets, Nassau, Bahamas',
            'store_phone' => '242.322.VELVET',
            'store_email' => 'hello@thevelvetlifestyle.com',
            'store_instagram' => 'https://instagram.com',
            'store_facebook' => 'https://facebook.com',
            'store_free_shipping_threshold' => '20000',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'storefront']);
        }

        SiteSetting::clearCache();

        // 2. Seed Default Customer Care & Policy Pages
        $pages = [
            [
                'title' => 'Shipping Information',
                'slug' => 'shipping',
                'sort_order' => 1,
                'meta_description' => 'Delivery and in-store pickup options throughout Nassau and the Family Islands.',
                'content' => "## Bahamas Delivery & In-Store Collection\n\nAt **The Velvet Lifestyle**, we offer convenient shopping options throughout the Bahamas.\n\n### 🛍️ Free In-Store Pickup\nPlace your order online and reserve your favorite items. Your order will be prepared and held at our Nassau boutique in Palmdale. You can inspect your items and pay at the counter upon collection.\n\n### 📦 Island Delivery\n- **Orders over $200:** Free delivery Bahamas-wide.\n- **Standard Nassau Delivery:** $10 flat rate.\n- **Family Island Freight:** Delivery to local mailboat / freight forwarding services arranged upon request.",
            ],
            [
                'title' => 'Returns & Exchanges',
                'slug' => 'returns',
                'sort_order' => 2,
                'meta_description' => 'Our 14-day store credit and exchange policy.',
                'content' => "## Returns & Store Credit Policy\n\nWe want you to love everything you purchase from Velvet Lifestyle.\n\n- **14-Day Return Window:** Items in original, unworn condition with tags attached may be returned for **Store Credit** or exchange within 14 days of purchase.\n- **Final Sale Items:** Sale merchandise, swimwear, and intimate accessories are final sale.\n- **How to Return:** Bring your item and order receipt to our Palmdale boutique.",
            ],
            [
                'title' => 'Size Guide',
                'slug' => 'size-guide',
                'sort_order' => 3,
                'meta_description' => 'Standard boutique sizing chart and fit recommendations.',
                'content' => "## Sizing Chart\n\n| Size | US Size | Bust (in) | Waist (in) | Hips (in) |\n| :--- | :--- | :--- | :--- | :--- |\n| **XS** | 0 - 2 | 32 - 33 | 24 - 25 | 34 - 35 |\n| **S** | 4 - 6 | 34 - 35 | 26 - 27 | 36 - 37 |\n| **M** | 8 - 10 | 36 - 37 | 28 - 29 | 38 - 39 |\n| **L** | 12 - 14 | 38 - 40 | 30 - 32 | 40 - 42 |\n| **XL** | 16 | 41 - 43 | 33 - 35 | 43 - 45 |\n\n*Need personalized styling help? Visit us in-store or call 242.322.VELVET for guidance.*",
            ],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faqs',
                'sort_order' => 4,
                'meta_description' => 'Answers to common questions about orders, payments, and boutique visits.',
                'content' => "## Frequently Asked Questions\n\n### How does online ordering work?\nBrowse our collections, add your favorite sizes to your bag, and complete the simple checkout. We reserve the items in our boutique immediately for you.\n\n### When do I pay?\nCurrently, payment is collected in person at the boutique via Cash, Debit, or Credit Card when you pick up your items. Online card payments via Powertranz will be available soon!\n\n### Where is the boutique located?\nWe are located at Palmdale, Tedder & Maderia Streets in Nassau, Bahamas.",
            ],
            [
                'title' => 'Store Policy',
                'slug' => 'store-policy',
                'sort_order' => 5,
                'meta_description' => 'General store terms, hold policies, and privacy.',
                'content' => "## Boutique Terms & Conditions\n\n### Item Holds\nReserved in-store pickup orders will be held for **48 hours** from the time you receive your *Ready for Pickup* confirmation. If you need additional time, please contact us.\n\n### Privacy & Customer Data\nYour contact details are solely used for order fulfillment and communications regarding your purchases. We never share or sell customer data.",
            ],
            [
                'title' => 'Our Story',
                'slug' => 'about',
                'sort_order' => 6,
                'meta_description' => 'Learn about the history and vision of The Velvet Lifestyle.',
                'content' => "## Welcome to The Velvet Lifestyle\n\nFor over 15 years, **The Velvet Lifestyle** has been Nassau's premier destination for chic, sophisticated fashion and lifestyle pieces. Founded in 2009, we curate elevated collections for women across the Bahamas who appreciate timeless quality, effortless style, and modern edge.\n\nWhether dressing for a special island event, finding everyday elegance, or seeking curated home fragrances and accessories, our boutique team is dedicated to providing an exceptional personal shopping experience.",
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(['slug' => $pageData['slug']], $pageData);
        }
    }
}
