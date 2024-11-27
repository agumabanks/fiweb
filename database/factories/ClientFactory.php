<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'status' => 'active',
            'kyc_verified_at' => $this->faker->optional()->dateTime,
            'dob' => $this->faker->date,
            'business' => $this->faker->company,
            'nin' => $this->faker->regexify('[A-Za-z0-9]{10}'),
            'recommenders' => $this->faker->optional()->words(3, true),
            'credit_balance' => $this->faker->randomFloat(2, 0, 10000),
            'savings_balance' => $this->faker->randomFloat(2, 0, 10000),
        ];
    }
}
