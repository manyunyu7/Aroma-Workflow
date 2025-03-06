<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

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
        $this->addUser("Admin", "admin@email.com", "password", "1", "0000000000000001");
        $this->addUser("user", "user@gmail.com", "password", "3", "0000000000000002");
        $this->addUser("staff", "staff@gmail.com", "password", "2", "0000000000000003");

        // Generate 20,000 users
        for ($i = 0; $i < 20000; $i++) {
            try {
                $role = $faker->randomElement([2, 3]); // Randomly assign staff (2) or user (3)
                $nip = $faker->unique()->numerify(str_repeat("#", 16)); // Unique 16-digit NIP
                $this->addUser($faker->name, $faker->unique()->safeEmail, "password", $role, $nip);
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

    public function addUser($name, $email, $password, $role, $nip)
    {
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'role' => $role,
            'nip' => $nip,
        ]);
    }
}
