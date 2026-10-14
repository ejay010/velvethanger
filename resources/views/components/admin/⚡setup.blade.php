<?php

use Livewire\Component;
use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    // =========================================================================
    // Educational Comment:
    // Similar to WordPress's famous installation wizard (wp-admin/install.php),
    // this component allows the boutique owner to set up the store name and create
    // the primary Administrator account upon first deployment.
    // =========================================================================

    // Form fields for the initial setup
    public string $site_title = 'The Velvet Lifestyle';
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        // Educational Comment: Security Guard!
        // If an admin user ALREADY exists in the database, lock down this setup page
        // and redirect to the dashboard/login so unauthorized visitors cannot hijack admin access.
        if (User::where('is_admin', true)->exists()) {
            return redirect()->route('admin.dashboard');
        }

        // Pre-fill the store title from database settings if one already exists
        $storedTitle = SiteSetting::get('store_name');
        if ($storedTitle) {
            $this->site_title = (string) $storedTitle;
        }
    }

    public function createAdminAccount()
    {
        // 1. Validate incoming user inputs (including the optional store title)
        $this->validate([
            'site_title' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Prevent race conditions: Double-check that no admin was created right before submitting
        if (User::where('is_admin', true)->exists()) {
            Flux::toast('An admin user already exists.', variant: 'warning');
            return redirect()->route('admin.dashboard');
        }

        // 3. Save the Store / Site Title in site settings (like WordPress Site Title)
        if (! empty($this->site_title)) {
            SiteSetting::set('store_name', $this->site_title);
        }

        // 4. Create the initial primary Administrator account
        $admin = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // 5. Automatically log the new administrator in
        Auth::login($admin);

        // 6. Notify the user with a toast and redirect straight to the Admin Dashboard
        Flux::toast('Admin account created successfully! Welcome to Velvet Hanger.', variant: 'success');

        return redirect()->route('admin.dashboard');
    }
};
?>

<div class="min-h-screen bg-gray-50 dark:bg-zinc-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200 mb-3">
                First-Time Store Setup
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Velvet Hanger</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">First-Time Administrative Store Setup</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <flux:card class="space-y-6">
            <div>
                <flux:heading size="lg">Create Primary Admin Account</flux:heading>
                <flux:subheading size="sm" class="mt-1">
                    Welcome! Similar to WordPress first-time install, configure your store name and create your administrator login.
                </flux:subheading>
            </div>

            <form wire:submit.prevent="createAdminAccount" class="space-y-4">
                {{-- Store / Site Title --}}
                <flux:input 
                    label="Store / Site Title" 
                    wire:model="site_title" 
                    placeholder="e.g. The Velvet Lifestyle" 
                    hint="The public name of your boutique (can be updated in Settings)."
                />

                <flux:separator class="my-3" />

                {{-- Administrator Name --}}
                <flux:input 
                    label="Administrator Name" 
                    wire:model="name" 
                    placeholder="e.g. Jane Doe" 
                    required 
                />

                {{-- Email Address --}}
                <flux:input 
                    type="email" 
                    label="Email Address" 
                    wire:model="email" 
                    placeholder="admin@velvethanger.com" 
                    hint="Used for store login and administrator notifications."
                    required 
                />

                {{-- Password --}}
                <flux:input 
                    type="password" 
                    label="Password" 
                    wire:model="password" 
                    hint="At least 8 characters" 
                    required 
                />

                {{-- Confirm Password --}}
                <flux:input 
                    type="password" 
                    label="Confirm Password" 
                    wire:model="password_confirmation" 
                    required 
                />

                <div class="pt-2">
                    <flux:button type="submit" variant="primary" class="w-full">
                        Complete Setup & Launch Dashboard
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</div>
