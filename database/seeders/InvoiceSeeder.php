<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('invoices')->insert([
            'user_id'=>1,
            'training_id'=>1,
            'order_number'=>23,
            'status'=>'paid',
            'amount'=>123
        ]);
    }
}
