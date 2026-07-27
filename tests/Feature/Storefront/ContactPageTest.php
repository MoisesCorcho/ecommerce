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
            ->assertSet('errorMessage', __('contact.error.throttled', ['email' => config('ecommerce.contact.public_email')]));

        Mail::assertSentCount(ContactFormRateLimiter::MAX_ATTEMPTS);
    }

    // --- R14: mail transport failure ---

    public function test_transport_failure_shows_error_banner_logs_and_preserves_fields(): void
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
            ->assertSet('sent', false)
            ->assertSet('errorMessage', __('contact.error.send_failed', ['email' => 'support@example.com']))
            ->assertSet('name', 'Jane Doe')
            ->assertSet('email', 'jane@example.com')
            ->assertSet('subject', 'Hello there')
            ->assertSet('message', 'A perfectly valid message.');
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
