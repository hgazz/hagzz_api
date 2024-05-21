<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Join;
use App\Models\User;
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
            'user_id'=>149,
            'training_id'=>10,
            'order_number'=>28567,
            'status'=>'paid',
            'amount'=>123
        ]);

        Join::create([
           'user_id'=>$invoice->user_id,
           'invoice_id'=>$invoice->id,
           'training_id'=>$invoice->training_id,
           'price' => $invoice->amount
        ]);

        $invoice = Invoice::create([
            'user_id'=>149,
            'training_id'=>16,
            'order_number'=>28567,
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
