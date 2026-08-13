# Velvet Hanger Online Boutique - Design Reference

This document serves as a brief technical and structural reference for the design team when building custom front-end assets for Velvet Hanger.

## Architecture & Technology Stack

The application uses the **TALL stack** architecture:
- **Tailwind CSS**: The utility-first CSS framework used for all styling.
- **Alpine.js**: For lightweight JavaScript interactions (modals, dropdowns, etc.).
- **Laravel**: The backend PHP framework handling database, routing, and server-side logic.
- **Livewire**: Allows us to build dynamic React/Vue-like interfaces without leaving PHP.

## Existing Component Structure

The current user interface is built using **Flux UI**, a set of clean, accessible Livewire/Alpine components. 
The views you will be styling are located in:

*   `resources/views/shop/` (Wrapper Blade files)
*   `resources/views/components/storefront/` (The actual Livewire components where data and UI interact)

### Key Pages to Style

1. **Storefront (Catalog)** - `resources/views/components/storefront/⚡home.blade.php`
    *   Displays a grid of `Product` cards.
    *   Needs to feel like a premium boutique (high-quality product imagery, elegant typography, generous whitespace).
2. **Product Details** - `resources/views/components/storefront/⚡product-show.blade.php`
    *   Single product view.
    *   Includes a "Quantity" input and an "Add to Cart" button.
3. **Cart** - `resources/views/components/storefront/⚡cart.blade.php`
    *   Line-items showing product, quantity, subtotal, and a "Remove" button.
    *   Calculates the cart total dynamically.
4. **Checkout** - `resources/views/components/storefront/⚡checkout.blade.php`
    *   Customer information form (Name, Email, Pickup Address).
    *   Placeholder text indicating that "Pay In-Store" is currently active until Powertranz is integrated.

## Design Guidelines

*   **Premium Aesthetic**: Target women ages 18-60 in the Bahamas. The aesthetic should be refined, elegant, and trustworthy, suitable for a boutique selling clothing, lifestyle items, candles, and accessories.
*   **Color Palette**: Stick to sophisticated neutrals, soft pastels, or rich earthy tones depending on the brand identity. Avoid harsh, default bootstrap colors.
*   **Typography**: Recommend integrating a modern serif (e.g., Playfair Display) for headings to give a luxury feel, paired with a highly readable sans-serif (e.g., Inter, Lato) for body text.
*   **Responsiveness**: The site must be fully responsive. Mobile shopping is a priority for the target demographic.
*   **Tailwind Integration**: All custom styles should be applied using Tailwind CSS utility classes within the Blade files. For complex, reusable patterns, update the `tailwind.config.js` or add custom components.

## Developer Handoff

When providing designs or updated HTML:
1. Please provide Tailwind CSS classes rather than raw CSS files.
2. If custom CSS is necessary, ensure it is added to `resources/css/app.css` using `@layer components` or `@layer utilities`.
3. Keep the `wire:model`, `wire:click`, and `wire:submit.prevent` attributes intact on inputs, buttons, and forms. These are what connect the UI to the backend logic.
