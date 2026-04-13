<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = 1; // Assuming institute ID 1 for demo

        $discounts = [
            [
                'institute_id' => $instituteId,
                'name' => 'Sibling Discount - 2nd Child',
                'code' => 'SIBLING_2',
                'type' => 'sibling',
                'percentage' => 10,
                'fixed_amount' => 0,
                'conditions' => ['min_children' => 2],
                'description' => '10% discount for second child',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Sibling Discount - 3rd Child',
                'code' => 'SIBLING_3',
                'type' => 'sibling',
                'percentage' => 20,
                'fixed_amount' => 0,
                'conditions' => ['min_children' => 3],
                'description' => '20% discount for third child',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Scholarship Discount',
                'code' => 'SCHOLARSHIP',
                'type' => 'scholarship',
                'percentage' => 25,
                'fixed_amount' => 0,
                'conditions' => null,
                'description' => '25% scholarship for meritorious students',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Merit Discount - Top 3',
                'code' => 'MERIT_TOP3',
                'type' => 'merit',
                'percentage' => 15,
                'fixed_amount' => 0,
                'conditions' => ['position' => [1, 2, 3]],
                'description' => '15% discount for top 3 position holders',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Need-Based Financial Aid',
                'code' => 'NEED_BASED',
                'type' => 'need_based',
                'percentage' => 0,
                'fixed_amount' => 1000,
                'conditions' => ['verification_required' => true],
                'description' => 'Rs. 1000 fixed discount for financially weak families',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Early Bird Discount',
                'code' => 'EARLY_BIRD',
                'type' => 'custom',
                'percentage' => 5,
                'fixed_amount' => 0,
                'conditions' => ['before_date' => '2026-03-31'],
                'description' => '5% discount for fee paid before deadline',
                'is_active' => true,
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::create($discount);
        }
    }
}
