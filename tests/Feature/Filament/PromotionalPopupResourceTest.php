<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\PromotionalPopups\Pages\CreatePromotionalPopup;
use App\Filament\Resources\PromotionalPopups\Pages\EditPromotionalPopup;
use App\Filament\Resources\PromotionalPopups\Pages\ListPromotionalPopups;
use App\Models\Coupon;
use App\Models\PromotionalPopup;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PromotionalPopupResourceTest extends TestCase
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

    public function test_guest_or_non_admin_cannot_access_promotional_popups_resource(): void
    {
        $this->get(ListPromotionalPopups::getUrl())
            ->assertRedirect();

        $regularUser = User::factory()->create(['email' => 'customer@example.com']);
        $this->actingAs($regularUser)
            ->get(ListPromotionalPopups::getUrl())
            ->assertForbidden();
    }

    public function test_admin_can_list_promotional_popups(): void
    {
        $this->actingAsAdmin();

        $popup = PromotionalPopup::factory()->create([
            'title' => ['es' => 'Descuento exclusivo hoy'],
        ]);

        Livewire::test(ListPromotionalPopups::class)
            ->assertCanSeeTableRecords([$popup])
            ->searchTable('Descuento exclusivo')
            ->assertCanSeeTableRecords([$popup]);
    }

    public function test_admin_can_create_promotional_popup(): void
    {
        $this->actingAsAdmin();

        $coupon = Coupon::factory()->create(['code' => 'PROMO15']);

        Livewire::test(CreatePromotionalPopup::class)
            ->set('data.title.es', '¡Gran Oferta de Temporada!')
            ->set('data.title.en', 'Great Seasonal Offer!')
            ->set('data.subtitle.es', 'Aprovecha un 15% de descuento en toda la tienda')
            ->set('data.subtitle.en', 'Enjoy 15% off storewide')
            ->set('data.cta_text.es', 'Comprar Ahora')
            ->set('data.cta_text.en', 'Shop Now')
            ->set('data.cta_url', 'https://leen.com.co/shop')
            ->set('data.coupon_id', $coupon->id)
            ->set('data.delay_seconds', 7)
            ->set('data.is_active', true)
            ->set('data.sort_order', 1)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('promotional_popups', [
            'coupon_id' => $coupon->id,
            'cta_url' => 'https://leen.com.co/shop',
            'delay_seconds' => 7,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $created = PromotionalPopup::first();
        $this->assertNotNull($created);
        $this->assertSame('¡Gran Oferta de Temporada!', $created->getTranslation('title', 'es'));
        $this->assertSame('Great Seasonal Offer!', $created->getTranslation('title', 'en'));
        $this->assertSame('Aprovecha un 15% de descuento en toda la tienda', $created->getTranslation('subtitle', 'es'));
        $this->assertSame('Comprar Ahora', $created->getTranslation('cta_text', 'es'));
    }

    public function test_create_requires_title_in_spanish(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreatePromotionalPopup::class)
            ->set('data.title.es', null)
            ->set('data.delay_seconds', 5)
            ->set('data.is_active', true)
            ->call('create')
            ->assertHasFormErrors(['title.es' => 'required']);
    }

    public function test_create_validates_delay_seconds_range(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreatePromotionalPopup::class)
            ->set('data.title.es', 'Título prueba')
            ->set('data.delay_seconds', 0)
            ->call('create')
            ->assertHasFormErrors(['delay_seconds']);

        Livewire::test(CreatePromotionalPopup::class)
            ->set('data.title.es', 'Título prueba')
            ->set('data.delay_seconds', 100)
            ->call('create')
            ->assertHasFormErrors(['delay_seconds']);
    }

    public function test_create_validates_ends_at_after_or_equal_starts_at(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreatePromotionalPopup::class)
            ->set('data.title.es', 'Título prueba')
            ->set('data.delay_seconds', 5)
            ->set('data.starts_at', '2026-08-10 10:00:00')
            ->set('data.ends_at', '2026-08-05 10:00:00')
            ->call('create')
            ->assertHasFormErrors(['ends_at']);
    }

    public function test_admin_can_edit_promotional_popup(): void
    {
        $this->actingAsAdmin();

        $popup = PromotionalPopup::factory()->create([
            'title' => [
                'es' => 'Título anterior',
                'en' => 'Old title',
            ],
            'is_active' => true,
        ]);

        Livewire::test(EditPromotionalPopup::class, ['record' => $popup->getRouteKey()])
            ->set('data.title.es', 'Título actualizado')
            ->set('data.title.en', 'Updated title')
            ->set('data.is_active', false)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $fresh = $popup->fresh();
        $this->assertSame('Título actualizado', $fresh->getTranslation('title', 'es'));
        $this->assertSame('Updated title', $fresh->getTranslation('title', 'en'));
        $this->assertFalse($fresh->is_active);
    }

    public function test_admin_can_delete_promotional_popup(): void
    {
        $this->actingAsAdmin();

        $popup = PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up a eliminar'],
        ]);

        Livewire::test(ListPromotionalPopups::class)
            ->callAction(TestAction::make('delete')->table($popup));

        $this->assertDatabaseMissing('promotional_popups', [
            'id' => $popup->id,
        ]);
    }
}
