<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

new class extends Component {
    // Form fields for creating the initial admin account
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        // Educational Comment: Security Guard!
        // If an admin user ALREADY exists in the database, lock down this setup page
        // and redirect to login/home so unauthorized users cannot claim admin access.
        if (User::where('is_admin', true)->exists()) {
            return redirect()->route('admin.dashboard');
        }
    }

    public function createAdminAccount()
    {
        // 1. Validate incoming user inputs
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Prevent race conditions: Ensure no admin was created right before submitting
        if (User::where('is_admin', true)->exists()) {
            Flux::toast('An admin user already exists.', variant: 'warning');
            return redirect()->route('admin.dashboard');
        }

        // 3. Create the initial primary Administrator account
        $admin = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // 4. Log the new admin user in automatically
        Auth::login($admin);

        // 5. Notify the user and redirect to the Admin Dashboard
        Flux::toast('Admin account created successfully! Welcome to Velvet Hanger.', variant: 'success');

        return redirect()->route('admin.dashboard');
    }
};
?>

<div class="min-h-screen bg-gray-50 dark:bg-zinc-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Velvet Hanger</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">First-Time Administrative Store Setup</p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <flux:card class="space-y-6">
            <div>
                <flux:heading size="lg">Create Primary Admin Account</flux:heading>
                <flux:subheading size="sm" class="mt-1">
                    Welcome! Set up the store administrator login for your online boutique.
                </flux:subheading>
            </div>

            <form wire:submit.prevent="createAdminAccount" class="space-y-4">
                <flux:input label="Administrator Name" wire:model="name" placeholder="e.g. Jane Doe" required />

                <flux:input type="email" label="Email Address" wire:model="email" placeholder="admin@velvethanger.com" required />

                <flux:input type="password" label="Password" wire:model="password" hint="At least 8 characters" required />

                <flux:input type="password" label="Confirm Password" wire:model="password_confirmation" required />

                <div class="pt-2">
                    <flux:button type="submit" variant="primary" class="w-full">
                        Complete Setup & Launch Dashboard
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</div>
