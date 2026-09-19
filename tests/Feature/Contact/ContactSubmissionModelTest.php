<?php

declare(strict_types=1);

namespace Tests\Feature\Contact;

use App\Enums\Contact\ContactSubmissionStatusEnum;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSubmissionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_submission_with_default_new_status(): void
    {
        $submission = ContactSubmission::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question about bag',
            'message' => 'Is this bag in stock?',
        ]);

        $this->assertDatabaseHas('contact_submissions', [
            'id' => $submission->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => 'new',
        ]);

        $this->assertSame(ContactSubmissionStatusEnum::New, $submission->status);
        $this->assertNull($submission->read_at);
        $this->assertNull($submission->replied_at);
    }

    public function test_submission_can_belong_to_a_user(): void
    {
        $user = User::factory()->create();

        $submission = ContactSubmission::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($submission->user->is($user));
    }

    public function test_mark_as_read_updates_status_and_timestamp(): void
    {
        $submission = ContactSubmission::factory()->create();

        $submission->markAsRead();

        $this->assertSame(ContactSubmissionStatusEnum::Read, $submission->status);
        $this->assertNotNull($submission->read_at);
    }

    public function test_mark_as_replied_updates_status_and_timestamp(): void
    {
        $submission = ContactSubmission::factory()->create();

        $submission->markAsReplied();

        $this->assertSame(ContactSubmissionStatusEnum::Replied, $submission->status);
        $this->assertNotNull($submission->replied_at);
    }
}
