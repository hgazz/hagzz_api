<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Join;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JoinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoice = Invoice::create([
            'user_id'=>3,
            'training_id'=>2,
            'order_number'=>23,
            'status'=>'paid',
            'amount'=>123
        ]);

        Join::create([
           'user_id'=>$invoice->user_id,
           'invoice_id'=>$invoice->id,
           'training_id'=>$invoice->training_id,
           'price' => $invoice->amount
        ]);
    }
}
