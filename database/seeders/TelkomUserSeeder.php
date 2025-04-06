<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// php artisan db:seed --class=TelkomUserSeeder

class TelkomUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Predefined roles (from your application)
        $roles = [
            "Creator",
            "Acknowledger",
            "Unit Head - Approver",
            "Reviewer-Maker",
            "Reviewer-Approver"
        ];

        // Read the JSON file
        $jsonPath = database_path('seeders/telkom_users.json');

        // Check if file exists
        if (!file_exists($jsonPath)) {
            throw new Exception("JSON file not found at {$jsonPath}");
        }

        // Decode JSON data
        $jsonData = json_decode(file_get_contents($jsonPath), true);

        // Process each user
        foreach ($jsonData['data'] as $userData) {
            try {
                // Extract user details from the nested structure
                $nik = $userData['personal']['nik'] ?? null;
                $name = $userData['personal']['name'] ?? 'Unknown';
                $email = $userData['detail']['mail'] ?? null;

                // Extract additional fields from the detail section
                $objectId = $userData['detail']['object_id'] ?? null;
                $unitKerja = $userData['detail']['unit'] ?? null;
                $jabatan = $userData['detail']['nama_posisi'] ?? null;

                // Validate email and NIK
                if (!$email || !$nik) {
                    continue;
                }

                // Check if user already exists by email or NIK
                $existingUser = User::where('email', $email)
                    ->orWhere('nik', $nik)
                    ->first();

                // Skip if user already exists
                if ($existingUser) {
                    continue;
                }

                // Create user with additional fields
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('Telkom2024!'), // Strong default password
                    'nik' => $nik,
                    'object_id' => $objectId,
                    'unit_kerja' => $unitKerja,
                    'jabatan' => $jabatan,
                    'status' => 'Active',
                    'created_by' => 1,  // Changed from 'TelkomUserSeeder' to 1
                ]);

                // Randomly assign 1-3 roles
                $userRoles = $this->getRandomRoles($roles);

                // Create user roles
                foreach ($userRoles as $role) {
                    // Check if role already exists for this user
                    $existingRole = UserRole::where('user_id', $user->id)
                        ->where('role', $role)
                        ->first();

                    if (!$existingRole) {
                        UserRole::create([
                            'user_id' => $user->id,
                            'role' => $role
                        ]);
                    }
                }

            } catch (Exception $e) {
                // Log any errors without stopping the entire seeding process
                \Log::error('Error seeding user: ' . $e->getMessage());
                \Log::error('Problematic user data: ' . json_encode($userData));
                continue;
            }
        }

        $this->command->info('Telkom users seeding completed!');
    }

    /**
     * Get random roles for a user
     *
     * @param array $roles
     * @return array
     */
    private function getRandomRoles($roles)
    {
        // Shuffle the roles
        shuffle($roles);

        // Randomly decide how many roles to assign (1-3)
        $numRoles = random_int(1, min(3, count($roles)));

        // Return a slice of the shuffled roles
        return array_slice($roles, 0, $numRoles);
    }
}
