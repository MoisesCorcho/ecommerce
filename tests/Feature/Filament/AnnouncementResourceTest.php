<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\Announcements\Pages\EditAnnouncement;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Models\Announcement;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnnouncementResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('es');
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

    public function test_guest_or_non_admin_cannot_access_announcements_resource(): void
    {
        $this->get(ListAnnouncements::getUrl())
            ->assertRedirect();

        $regularUser = User::factory()->create(['email' => 'customer@example.com']);
        $this->actingAs($regularUser)
            ->get(ListAnnouncements::getUrl())
            ->assertForbidden();
    }

    public function test_admin_can_list_announcements(): void
    {
        $this->actingAsAdmin();

        $announcement = Announcement::factory()->create([
            'text' => ['es' => 'Descuento especial hoy'],
        ]);

        Livewire::test(ListAnnouncements::class)
            ->assertCanSeeTableRecords([$announcement])
            ->searchTable('Descuento especial')
            ->assertCanSeeTableRecords([$announcement]);
    }

    public function test_admin_can_create_announcement(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateAnnouncement::class)
            ->set('data.text.es', '¡Envío gratis a toda Colombia!')
            ->set('data.text.en', 'Free shipping across Colombia!')
            ->set('data.url', 'https://leen.com.co/shop')
            ->set('data.is_active', true)
            ->set('data.sort_order', 1)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('announcements', [
            'url' => 'https://leen.com.co/shop',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $created = Announcement::first();
        $this->assertNotNull($created);
        $this->assertSame('¡Envío gratis a toda Colombia!', $created->getTranslation('text', 'es'));
        $this->assertSame('Free shipping across Colombia!', $created->getTranslation('text', 'en'));
    }

    public function test_create_requires_text_in_spanish(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateAnnouncement::class)
            ->set('data.text.es', null)
            ->set('data.is_active', true)
            ->call('create')
            ->assertHasFormErrors(['text.es' => 'required']);
    }

    public function test_create_validates_ends_at_after_or_equal_starts_at(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateAnnouncement::class)
            ->set('data.text.es', 'Texto prueba')
            ->set('data.starts_at', '2026-08-10 10:00:00')
            ->set('data.ends_at', '2026-08-05 10:00:00')
            ->call('create')
            ->assertHasFormErrors(['ends_at']);
    }

    public function test_admin_can_edit_announcement(): void
    {
        $this->actingAsAdmin();

        $announcement = Announcement::factory()->create([
            'text' => [
                'es' => 'Texto anterior',
                'en' => 'Old text',
            ],
            'is_active' => true,
        ]);

        Livewire::test(EditAnnouncement::class, ['record' => $announcement->getRouteKey()])
            ->set('data.text.es', 'Texto actualizado')
            ->set('data.text.en', 'Updated text')
            ->set('data.is_active', false)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $fresh = $announcement->fresh();
        $this->assertSame('Texto actualizado', $fresh->getTranslation('text', 'es'));
        $this->assertSame('Updated text', $fresh->getTranslation('text', 'en'));
        $this->assertFalse($fresh->is_active);
    }

    public function test_admin_can_delete_announcement(): void
    {
        $this->actingAsAdmin();

        $announcement = Announcement::factory()->create([
            'text' => ['es' => 'Para eliminar'],
        ]);

        Livewire::test(ListAnnouncements::class)
            ->callAction(TestAction::make('delete')->table($announcement));

        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);
    }
}
