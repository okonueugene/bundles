<?php

namespace Database\Seeders;

use App\Models\BundleMapping;
use Illuminate\Database\Seeder;

class BundleMappingSeeder extends Seeder
{
    public function run(): void
    {
        $bundles = [
            ['slug' => '1gb-data', 'amount' => 99, 'type' => 'data', 'package_code' => 'DATA_1GB', 'description' => '1 GB', 'validity' => '24 hours'],
            ['slug' => '2gb-data', 'amount' => 199, 'type' => 'data', 'package_code' => 'DATA_2GB', 'description' => '2 GB', 'validity' => '24 hours'],
            ['slug' => '3gb-data', 'amount' => 299, 'type' => 'data', 'package_code' => 'DATA_3GB', 'description' => '3 GB', 'validity' => '24 hours'],
            ['slug' => '5gb-data', 'amount' => 499, 'type' => 'data', 'package_code' => 'DATA_5GB', 'description' => '5 GB', 'validity' => '72 hours'],
            ['slug' => '10gb-data', 'amount' => 999, 'type' => 'data', 'package_code' => 'DATA_10GB', 'description' => '10 GB', 'validity' => '7 days'],
            ['slug' => '50-sms', 'amount' => 50, 'type' => 'sms', 'package_code' => 'SMS_50', 'description' => '50 SMS', 'validity' => '24 hours'],
            ['slug' => '200-sms', 'amount' => 100, 'type' => 'sms', 'package_code' => 'SMS_200', 'description' => '200 SMS', 'validity' => '7 days'],
            ['slug' => '500-sms', 'amount' => 200, 'type' => 'sms', 'package_code' => 'SMS_500', 'description' => '500 SMS', 'validity' => '30 days'],
            ['slug' => '20-minutes', 'amount' => 55, 'type' => 'minutes', 'package_code' => 'MIN_20', 'description' => '20 Minutes', 'validity' => '24 hours'],
            ['slug' => '50-minutes', 'amount' => 120, 'type' => 'minutes', 'package_code' => 'MIN_50', 'description' => '50 Minutes', 'validity' => '7 days'],
            ['slug' => '100-minutes', 'amount' => 220, 'type' => 'minutes', 'package_code' => 'MIN_100', 'description' => '100 Minutes', 'validity' => '30 days'],
        ];

        foreach ($bundles as $bundle) {
            BundleMapping::updateOrCreate(
                ['network' => 'safaricom', 'amount' => $bundle['amount']],
                $bundle
            );
        }
    }
}
