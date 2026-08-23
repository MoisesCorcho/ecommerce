<?php

use App\Actions\Contact\SubmitContactFormAction;
use App\DTOs\Contact\SubmitContactFormDTO;
use App\Support\Contact\ContactFormRateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->name = (string) auth()->user()->name;
            $this->email = (string) auth()->user()->email;
        }
    }

    public function render()
    {
        return $this->view();
    }

    public function updated(string $property): void
    {
        $this->validateOnly($property);
    }

    public function submit(ContactFormRateLimiter $limiter, SubmitContactFormAction $action): void
    {
        $this->errorMessage = null;

        if (! $limiter->attempt(request()->ip())) {
            $this->errorMessage = __('contact.error.throttled');

            return;
        }

        $this->validate();

        $dto = new SubmitContactFormDTO(
            name: $this->name,
            email: $this->email,
            subject: $this->subject,
            message: $this->message,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            userId: auth()->id(),
        );

        $action($dto);

        $this->sent = true;
        $this->reset(['name', 'email', 'subject', 'message']);
    }

    public function sendAnother(): void
    {
        $this->sent = false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'name' => __('contact.form.name'),
            'email' => __('contact.form.email'),
            'subject' => __('contact.form.subject'),
            'message' => __('contact.form.message'),
        ];
    }
};
