<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::set('tax_percent', '11');
        \App\Models\Setting::set('service_fee_type', 'fixed');
        \App\Models\Setting::set('service_fee_value', '2500');
    }
}
