<?php

declare(strict_types=1);

namespace Tests\Feature\Contact;

use App\Enums\Contact\ContactSubmissionStatusEnum;
use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use App\Filament\Resources\ContactSubmissions\Pages\ListContactSubmissions;
use App\Filament\Resources\ContactSubmissions\Pages\ViewContactSubmission;
use App\Models\ContactSubmission;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContactSubmissionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    private function actingAsAdmin(): User
    {
        Config::set('ecommerce.admin_emails', ['admin@example.com']);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        Role::findOrCreate('admin', 'web');
        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_list_contact_submissions(): void
    {
        $this->actingAsAdmin();

        $submissions = ContactSubmission::factory()->count(2)->create();

        Livewire::test(ListContactSubmissions::class)
            ->assertCanSeeTableRecords($submissions);
    }

    public function test_admin_can_view_contact_submission_infolist(): void
    {
        $this->actingAsAdmin();

        $submission = ContactSubmission::factory()->create([
            'message' => 'Specific unique message content for infolist assertion.',
        ]);

        Livewire::test(ViewContactSubmission::class, ['record' => $submission->getRouteKey()])
            ->assertSuccessful()
            ->assertSee('Specific unique message content for infolist assertion.');
    }

    public function test_admin_can_mark_as_read(): void
    {
        $this->actingAsAdmin();

        $submission = ContactSubmission::factory()->create([
            'status' => ContactSubmissionStatusEnum::New,
        ]);

        Livewire::test(ListContactSubmissions::class)
            ->callAction(TestAction::make('markAsRead')->table($submission))
            ->assertNotified();

        $this->assertSame(ContactSubmissionStatusEnum::Read, $submission->fresh()->status);
        $this->assertNotNull($submission->fresh()->read_at);
    }

    public function test_admin_can_mark_as_replied(): void
    {
        $this->actingAsAdmin();

        $submission = ContactSubmission::factory()->create([
            'status' => ContactSubmissionStatusEnum::New,
        ]);

        Livewire::test(ListContactSubmissions::class)
            ->callAction(TestAction::make('markAsReplied')->table($submission))
            ->assertNotified();

        $this->assertSame(ContactSubmissionStatusEnum::Replied, $submission->fresh()->status);
        $this->assertNotNull($submission->fresh()->replied_at);
    }

    public function test_resource_cannot_create(): void
    {
        $this->assertFalse(ContactSubmissionResource::canCreate());
    }

    public function test_filter_by_status(): void
    {
        $this->actingAsAdmin();

        $newSubmission = ContactSubmission::factory()->create(['status' => ContactSubmissionStatusEnum::New]);
        $readSubmission = ContactSubmission::factory()->create(['status' => ContactSubmissionStatusEnum::Read]);

        Livewire::test(ListContactSubmissions::class)
            ->filterTable('status', ContactSubmissionStatusEnum::New->value)
            ->assertCanSeeTableRecords([$newSubmission])
            ->assertCanNotSeeTableRecords([$readSubmission]);
    }

    public function test_admin_can_update_notes_from_view_page(): void
    {
        $this->actingAsAdmin();

        $submission = ContactSubmission::factory()->create([
            'admin_notes' => null,
        ]);

        Livewire::test(ViewContactSubmission::class, ['record' => $submission->getRouteKey()])
            ->callAction('editNotes', [
                'admin_notes' => 'Followed up via phone on 23/08.',
            ])
            ->assertNotified();

        $this->assertSame('Followed up via phone on 23/08.', $submission->fresh()->admin_notes);
    }

    public function test_guest_or_non_admin_cannot_access_resource_pages(): void
    {
        $submission = ContactSubmission::factory()->create();

        $this->get(ListContactSubmissions::getUrl())
            ->assertRedirect();

        $regularUser = User::factory()->create(['email' => 'customer@example.com']);
        $this->actingAs($regularUser)
            ->get(ListContactSubmissions::getUrl())
            ->assertForbidden();
    }
}
