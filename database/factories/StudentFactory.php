<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $section = Section::inRandomOrder()->first();
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName . '.' . $lastName . '@eschool.pk'),
            'registration_number' => 'REG-' . now()->year . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'roll_no' => ($section?->grade_id ?? 1) . ($section?->name ?? 'A') . '-' . str_pad($this->faker->numberBetween(1, 50), 3, '0', STR_PAD_LEFT),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'mobile_number' => $this->faker->numerify('0300-#######'),
            'parents_name' => $this->faker->name('male') ?? $this->faker->name(),
            'parents_mobile_number' => $this->faker->numerify('030#########'),
            'date_of_birth' => $this->faker->date('Y-m-d', '-8 years'),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'address' => $this->faker->address(),
            'section_id' => $section?->id,
            'institute_id' => $section?->institute_id ?? 1,
            'admission_date' => now()->toDateString(),
            'registration_date' => now()->toDateString(),
            'status' => 'active',
        ];
    }
}