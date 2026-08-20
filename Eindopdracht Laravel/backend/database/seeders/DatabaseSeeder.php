<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Maak één Administrator aan
        $admin = User::create([
            'name' => 'Admin de Vries',
            'email' => 'admin@script.nl',
            'password' => Hash::make('wachtwoord123'),
            'is_admin' => true,
        ]);

        // 2. Maak een normale klant aan
        $klant = User::create([
            'name' => 'Kees Klant',
            'email' => 'kees@test.nl',
            'password' => Hash::make('wachtwoord123'),
            'is_admin' => false,
        ]);

        // 3. Maak 3 categorieën aan
        $cat1 = Category::create(['name' => 'Facturatie']);
        $cat2 = Category::create(['name' => 'Technische Storing']);
        $cat3 = Category::create(['name' => 'Algemene Vraag']);

        // 4. Maak 2 test-tickets aan voor Kees
        $ticket1 = Ticket::create([
            'user_id' => $klant->id,
            'category_id' => $cat1->id,
            'title' => 'Waar blijft mijn factuur?',
            'description' => 'Ik heb nog steeds geen factuur ontvangen voor de maand juli. Kunnen jullie deze sturen?',
            'status' => 'open',
        ]);

        $ticket2 = Ticket::create([
            'user_id' => $klant->id,
            'admin_id' => $admin->id, // Deze is al toegewezen aan de admin
            'category_id' => $cat2->id,
            'title' => 'Website is traag',
            'description' => 'Mijn website laadt heel erg langzaam sinds vanochtend.',
            'status' => 'in_behandeling',
        ]);

        // 5. Voeg een reactie toe aan het tweede ticket
        Comment::create([
            'ticket_id' => $ticket2->id,
            'user_id' => $admin->id,
            'content' => 'Beste Kees, we zijn ernaar aan het kijken. Het ligt aan de server.',
        ]);
    }
}