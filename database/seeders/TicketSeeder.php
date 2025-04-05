<?php

namespace Database\Seeders;

use App\Models\JenisAnggaran;
use App\Models\TicketCategory;
use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create("id_ID");

        // Predefined users
        $this->addUser("Admin", "admin@email.com", "password", "Admin", "20970970");
        $this->addUser("user", "user@gmail.com", "password", "Creator", "22000022");
        $this->addUser("staff", "staff@gmail.com", "password", "Acknowledger", "0000000000000003");

        Auth::loginUsingId(1);
        // Use raw SQL queries to insert JenisAnggaran records
        DB::statement("
            INSERT INTO jenis_anggarans (id, nama, created_by, updated_by, created_at, updated_at)
            VALUES (1, 'Capital Expenditure', 1, 1, NOW(), NOW())
            ");

        DB::statement("
            INSERT INTO jenis_anggarans (id, nama, created_by, updated_by, created_at, updated_at)
            VALUES (2, 'Operational Expenditure', 1, 1, NOW(), NOW())
            ");

        DB::statement("
            INSERT INTO jenis_anggarans (id, nama, created_by, updated_by, created_at, updated_at)
            VALUES (3, 'Revenue Expenditure', 1, 1, NOW(), NOW())
            ");

        // Available roles
        $roles = [
            "Creator",
            "Acknowledger",
            "Unit Head - Approver",
            "Reviewer-Maker",
            "Reviewer-Approver"
        ];

        // Generate 5 users
        for ($i = 0; $i < 5; $i++) {
            try {
                $role = $faker->randomElement($roles); // Randomly assign roles
                $nik = $faker->unique()->numerify(str_repeat("#", 16)); // Unique 16-digit NIK
                $this->addUser($faker->name, $faker->unique()->safeEmail, "password", $role, $nik);
            } catch (Exception $exception) {
                continue;
            }

            // Print progress every 100 users
            if ($i % 100 === 0) {
                echo "Seeded $i users...\n";
            }
        }

        // Ticket categories
        $this->addCategory("Masalah Jaringan");
        $this->addCategory("Masalah Email");
        $this->addCategory("Masalah Laptop");
        $this->addCategory("Masalah Printer");
        $this->addCategory("Masalah Aplikasi");
        $this->addCategory("Masalah Lainnya");

        echo "Seeding completed!";
    }

    public function addCategory($name)
    {
        TicketCategory::create(['name' => $name]);
    }

    public function addUser($name, $email, $password, $role, $nik)
    {
        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'nik' => $nik,
            'status' => 'Active',
            'created_by' => 'Seeder',
        ]);

        // Add role to the user
        UserRole::create([
            'user_id' => $user->id,
            'role' => $role
        ]);
    }
}
