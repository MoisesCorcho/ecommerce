<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Contact\ContactSubmissionStatusEnum;
use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ContactSubmission>
 */
class ContactSubmissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => ContactSubmissionStatusEnum::New,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'read_at' => null,
            'replied_at' => null,
            'admin_notes' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContactSubmissionStatusEnum::Read,
            'read_at' => Carbon::now(),
        ]);
    }

    public function replied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContactSubmissionStatusEnum::Replied,
            'read_at' => Carbon::now()->subMinutes(10),
            'replied_at' => Carbon::now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContactSubmissionStatusEnum::Archived,
        ]);
    }
}
