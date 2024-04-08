<?php

namespace Database\Seeders;

use App\Models\Favorite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DropFavourite extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Favorite::find(130)->delete();
    }
}
