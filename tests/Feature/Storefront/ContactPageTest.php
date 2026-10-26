<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Mail\Contact\ContactFormSubmittedMail;
use App\Models\User;
use App\Support\Contact\ContactFormRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('en');

        // The rate limiter's cache store is not cleared by RefreshDatabase;
        // clear the default test-request IP bucket so unrelated tests never bleed in.
        RateLimiter::clear((new ContactFormRateLimiter)->key('127.0.0.1'));
    }

    // --- R1, R4: route + rendering + authenticated prefill ---

    public function test_contact_page_responds_ok_for_guest(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_contact_page_responds_ok_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/contact')->assertOk();
    }

    public function test_contact_page_renders_info_card_form_and_faq_cta(): void
    {
        Livewire::test('contact-page')
            ->assertSee(__('contact.info.heading'))
            ->assertSee(__('contact.form.heading'))
            ->assertSeeHtml('/faq');
    }

    // --- Contact channels: config/ecommerce.php defaults are the single source of truth ---

    public function test_contact_channels_render_from_config_without_hardcoded_fallbacks(): void
    {
        // Sentinel values: if the Blade ever hardcodes a number again, the
        // sentinel stops appearing and the stale placeholder resurfaces.
        config([
            'ecommerce.contact.phone' => '+57 999 999 9999',
            'ecommerce.contact.phone_raw' => '+579999999999',
            'ecommerce.contact.whatsapp' => '+57 999 999 9999',
            'ecommerce.contact.whatsapp_url' => 'https://wa.me/579999999999',
        ]);

        Livewire::test('contact-page')
            ->assertSeeHtml('href="tel:+579999999999"')
            ->assertSeeHtml('href="https://wa.me/579999999999"')
            ->assertSee('+57 999 999 9999')
            ->assertDontSee('+57 300 123 4567');
    }

    public function test_contact_channels_have_real_defaults_so_deploys_need_no_env_setup(): void
    {
        // config/ecommerce.php ships complete defaults: a server whose .env
        // declares no CONTACT_* key must still render working phone/WhatsApp
        // links (the release archive does not carry .env, so this is the only
        // thing standing between a deploy and a blank contact channel).
        $this->assertNotEmpty(config('ecommerce.contact.phone'));
        $this->assertNotEmpty(config('ecommerce.contact.phone_raw'));
        $this->assertNotEmpty(config('ecommerce.contact.whatsapp'));
        $this->assertNotEmpty(config('ecommerce.contact.whatsapp_url'));

        $this->assertMatchesRegularExpression('/^\+\d{7,15}$/', (string) config('ecommerce.contact.phone_raw'));
        $this->assertStringStartsWith('https://wa.me/', (string) config('ecommerce.contact.whatsapp_url'));

        Livewire::test('contact-page')
            ->assertSeeHtml('href="tel:'.config('ecommerce.contact.phone_raw').'"')
            ->assertSeeHtml('href="'.config('ecommerce.contact.whatsapp_url').'"');
    }

    public function test_contact_phone_identity_is_locked_to_the_real_business_number(): void
    {
        // Golden value: the two assertions above only prove the channels are
        // non-empty and well-formed, so a DIFFERENT but valid number would pass
        // them. Locking the identity here means an accidental edit to the config
        // default fails loudly instead of quietly pointing customers at the
        // wrong phone or WhatsApp. Changing the business number is deliberate:
        // update these strings in the same commit as config/ecommerce.php.
        $this->assertSame('+573188667308', config('ecommerce.contact.phone_raw'));
        $this->assertSame('https://wa.me/573188667308', config('ecommerce.contact.whatsapp_url'));

        // The human-formatted strings must carry the very same digits, or the
        // visitor would read one number while the link dials another.
        $this->assertSame(
            str_replace(' ', '', (string) config('ecommerce.contact.phone')),
            (string) config('ecommerce.contact.phone_raw'),
        );
        $this->assertSame(
            str_replace(' ', '', (string) config('ecommerce.contact.whatsapp')),
            (string) config('ecommerce.contact.phone_raw'),
        );
    }

    public function test_authenticated_user_sees_prefilled_name_and_email_that_remain_editable(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        Livewire::actingAs($user)
            ->test('contact-page')
            ->assertSet('name', 'Jane Doe')
            ->assertSet('email', 'jane@example.com')
            ->set('name', 'Edited Name')
            ->assertSet('name', 'Edited Name');
    }

    // --- R10, R11, R12, R15: server-side validation ---

    public function test_submit_with_any_empty_required_field_rejects_and_shows_field_errors(): void
    {
        Mail::fake();

        Livewire::test('contact-page')
            ->set('name', '')
            ->set('email', '')
            ->set('subject', '')
            ->set('message', '')
            ->call('submit')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'required',
                'subject' => 'required',
                'message' => 'required',
            ]);

        Mail::assertNothingSent();
    }

    public function test_submit_with_invalid_email_format_rejects_with_localized_message(): void
    {
        Mail::fake();

        Livewire::test('contact-page')
            ->set('name', 'Jane Doe')
            ->set('email', 'not-an-email')
            ->set('subject', 'Hello there')
            ->set('message', 'A perfectly valid message.')
            ->call('submit')
            ->assertHasErrors(['email' => 'email']);

        Mail::assertNothingSent();
    }

    public function test_submit_with_message_over_1000_characters_blocks_submission(): void
    {
        Mail::fake();

        Livewire::test('contact-page')
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('subject', 'Hello there')
            ->set('message', str_repeat('a', 1001))
            ->call('submit')
            ->assertHasErrors(['message' => 'max']);

        Mail::assertNothingSent();
    }

    // --- R3, R6: successful submission ---

    public function test_valid_submission_sends_mail_shows_success_and_resets_fields(): void
    {
        config(['ecommerce.contact.inbox' => 'inbox@example.com']);
        Mail::fake();

        Livewire::test('contact-page')
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('subject', 'Hello there')
            ->set('message', 'A perfectly valid message.')
            ->call('submit')
            ->assertSet('sent', true)
            ->assertSet('name', '')
            ->assertSet('email', '')
            ->assertSet('subject', '')
            ->assertSet('message', '');

        Mail::assertSent(
            ContactFormSubmittedMail::class,
            fn (ContactFormSubmittedMail $mail): bool => $mail->hasTo('inbox@example.com')
                && $mail->hasReplyTo('jane@example.com'),
        );

        $this->assertDatabaseHas('contact_submissions', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Hello there',
            'message' => 'A perfectly valid message.',
            'status' => 'new',
        ]);
    }

    public function test_authenticated_user_submission_records_user_id_in_database(): void
    {
        config(['ecommerce.contact.inbox' => 'inbox@example.com']);
        Mail::fake();
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('contact-page')
            ->set('subject', 'My Order')
            ->set('message', 'Where is my order?')
            ->call('submit')
            ->assertSet('sent', true);

        $this->assertDatabaseHas('contact_submissions', [
            'user_id' => $user->id,
            'email' => $user->email,
            'subject' => 'My Order',
        ]);
    }

    public function test_send_another_resets_success_state_to_idle(): void
    {
        config(['ecommerce.contact.inbox' => 'inbox@example.com']);
        Mail::fake();

        Livewire::test('contact-page')
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('subject', 'Hello there')
            ->set('message', 'A perfectly valid message.')
            ->call('submit')
            ->assertSet('sent', true)
            ->call('sendAnother')
            ->assertSet('sent', false);
    }

    // --- R13: rate limiting ---

    public function test_fourth_submission_within_the_window_is_blocked_and_does_not_count_as_a_success(): void
    {
        config(['ecommerce.contact.inbox' => 'inbox@example.com']);
        Mail::fake();

        $component = Livewire::test('contact-page');

        for ($i = 0; $i < ContactFormRateLimiter::MAX_ATTEMPTS; $i++) {
            $component
                ->set('name', 'Jane Doe')
                ->set('email', 'jane@example.com')
                ->set('subject', 'Hello there')
                ->set('message', 'A perfectly valid message.')
                ->call('submit')
                ->assertSet('sent', true);
        }

        $component
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('subject', 'Hello there')
            ->set('message', 'A perfectly valid message.')
            ->call('submit')
            ->assertSet('errorMessage', __('contact.error.throttled'));

        Mail::assertSentCount(ContactFormRateLimiter::MAX_ATTEMPTS);
    }

    // --- R14: mail transport failure with DB resilience ---

    public function test_transport_failure_logs_error_but_preserves_submission_in_database(): void
    {
        config(['ecommerce.contact.inbox' => 'inbox@example.com', 'ecommerce.contact.public_email' => 'support@example.com']);

        Log::shouldReceive('error')->once();
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new TransportException('boom'));

        Livewire::test('contact-page')
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('subject', 'Hello there')
            ->set('message', 'A perfectly valid message.')
            ->call('submit')
            ->assertSet('sent', true);

        $this->assertDatabaseHas('contact_submissions', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Hello there',
            'status' => 'new',
        ]);
    }

    // --- R8, R9: storefront navigation links ---

    public function test_header_mobile_nav_and_footer_contact_links_resolve_to_the_registered_contact_route(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeHtml('href="'.route('contact').'"');
    }

    public function test_footer_faq_link_points_to_singular_faq_path_not_the_nonexistent_faqs_path(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeHtml('href="'.url('/faq').'"')
            ->assertDontSeeHtml('href="'.url('/faqs').'"');
    }

    // --- R16: localized copy ---

    public function test_page_renders_english_copy_from_the_contact_translation_domain(): void
    {
        app()->setLocale('en');

        Livewire::test('contact-page')
            ->assertSee('Contact us')
            ->assertSee('Send message');
    }

    public function test_page_renders_spanish_copy_from_the_contact_translation_domain(): void
    {
        app()->setLocale('es');

        Livewire::test('contact-page')
            ->assertSee('Contáctanos')
            ->assertSee('Enviar mensaje');

        app()->setLocale('en');
    }
}
