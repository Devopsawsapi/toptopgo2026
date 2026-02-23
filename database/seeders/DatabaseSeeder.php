<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DriverProfile;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'TopTopGo',
            'email' => 'admin@toptopgo.com',
            'phone' => '242066000000',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_phone_verified' => true,
            'is_email_verified' => true,
            'country_code' => 'CG',
        ]);

        // Create demo passenger
        $passenger = User::create([
            'first_name' => 'Jean',
            'last_name' => 'Passager',
            'email' => 'passager@demo.com',
            'phone' => '242066111111',
            'password' => Hash::make('demo123'),
            'role' => 'passenger',
            'is_phone_verified' => true,
            'country_code' => 'CG',
        ]);

        // Create demo driver
        $driver = User::create([
            'first_name' => 'Pierre',
            'last_name' => 'Chauffeur',
            'email' => 'chauffeur@demo.com',
            'phone' => '242066222222',
            'password' => Hash::make('demo123'),
            'role' => 'driver',
            'is_phone_verified' => true,
            'country_code' => 'CG',
        ]);

        // Create driver profile
        DriverProfile::create([
            'user_id' => $driver->id,
            'license_number' => 'CG-12345-2024',
            'license_expiry' => now()->addYears(2),
            'id_card_number' => 'ID-123456789',
            'vehicle_brand' => 'Toyota',
            'vehicle_model' => 'Corolla',
            'vehicle_year' => 2020,
            'vehicle_color' => 'Blanc',
            'vehicle_plate_number' => 'BZ-1234-CG',
            'vehicle_type' => 'standard',
            'seats_available' => 4,
            'kyc_status' => 'approved',
            'kyc_verified_at' => now(),
            'is_online' => false,
            'is_available' => false,
            'current_latitude' => -4.2634,
            'current_longitude' => 15.2429,
            'rating_average' => 4.5,
            'rating_count' => 10,
            'total_rides' => 25,
            'total_earnings' => 125000,
        ]);

        // Create wallet for driver
        Wallet::create([
            'user_id' => $driver->id,
            'balance' => 50000,
            'pending_balance' => 0,
            'total_earned' => 125000,
            'total_withdrawn' => 75000,
            'currency' => 'XAF',
        ]);

        $this->command->info('Seed completed!');
        $this->command->info('Admin: admin@toptopgo.com / admin123');
        $this->command->info('Passenger: passager@demo.com / demo123');
        $this->command->info('Driver: chauffeur@demo.com / demo123');
    }
}
