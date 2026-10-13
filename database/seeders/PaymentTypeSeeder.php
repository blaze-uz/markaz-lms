<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cash = PaymentType::firstOrCreate([
            'name' => 'Naqd',
        ]);

        PaymentType::firstOrCreate([
            'name' => 'Click',
        ]);

        Payment::where('payment_type_id', null)->update([
            'payment_type_id' => $cash->id,
        ]);

    }
}
