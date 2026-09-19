<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Contact;

use App\Enums\Contact\ContactSubmissionStatusEnum;
use Tests\TestCase;

class ContactSubmissionStatusEnumTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = ContactSubmissionStatusEnum::cases();

        $this->assertCount(4, $cases);
        $this->assertSame('new', ContactSubmissionStatusEnum::New->value);
        $this->assertSame('read', ContactSubmissionStatusEnum::Read->value);
        $this->assertSame('replied', ContactSubmissionStatusEnum::Replied->value);
        $this->assertSame('archived', ContactSubmissionStatusEnum::Archived->value);
    }

    public function test_every_status_resolves_a_label_in_english_and_spanish(): void
    {
        app()->setLocale('en');
        $this->assertSame('New', ContactSubmissionStatusEnum::New->getLabel());
        $this->assertSame('Read', ContactSubmissionStatusEnum::Read->getLabel());
        $this->assertSame('Replied', ContactSubmissionStatusEnum::Replied->getLabel());
        $this->assertSame('Archived', ContactSubmissionStatusEnum::Archived->getLabel());

        app()->setLocale('es');
        $this->assertSame('Nuevo', ContactSubmissionStatusEnum::New->getLabel());
        $this->assertSame('Leído', ContactSubmissionStatusEnum::Read->getLabel());
        $this->assertSame('Respondido', ContactSubmissionStatusEnum::Replied->getLabel());
        $this->assertSame('Archivado', ContactSubmissionStatusEnum::Archived->getLabel());

        app()->setLocale('en');
    }

    public function test_every_status_resolves_a_filament_color_and_icon(): void
    {
        foreach (ContactSubmissionStatusEnum::cases() as $status) {
            $this->assertNotEmpty($status->getColor());
            $this->assertNotEmpty($status->getIcon());
        }
    }
}
