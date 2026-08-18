<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geração de dados fictícios e realistas de Alunos.
 *
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        // Gera um CPF fictício no formato 000.000.000-00
        $cpf = sprintf(
            '%03d.%03d.%03d-%02d',
            fake()->numberBetween(100, 999),
            fake()->numberBetween(100, 999),
            fake()->numberBetween(100, 999),
            fake()->numberBetween(10, 99)
        );

        // Gera telefone celular no formato brasileiro (99) 99999-9999
        $phone = sprintf(
            '(%02d) 9%04d-%04d',
            fake()->numberBetween(11, 99),
            fake()->numberBetween(1000, 9999),
            fake()->numberBetween(1000, 9999)
        );

        return [
            'name' => fake()->name(),
            'cpf' => $cpf,
            'email' => fake()->unique()->safeEmail(),
            'phone' => $phone,
            'birth_date' => fake()->dateTimeBetween('-50 years', '-16 years')->format('Y-m-d'),
            'address' => fake()->streetAddress() . ', ' . fake()->city(),
            'photo_path' => null,
            'status' => 'ativo',
            'enrollment_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'plan_id' => Plan::query()->first()?->id ?? Plan::factory(),
            'payment_day' => fake()->randomElement([5, 10, 15, 20, 25, 28, 30]),
        ];
    }
}
