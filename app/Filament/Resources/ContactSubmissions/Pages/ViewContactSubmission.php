<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactSubmissions\Pages;

use App\Enums\Contact\ContactSubmissionStatusEnum;
use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use App\Models\ContactSubmission;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ViewContactSubmission extends ViewRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function resolveRecord(int|string $key): ContactSubmission
    {
        /** @var ContactSubmission $record */
        $record = parent::resolveRecord($key);
        $record->loadMissing(['user']);

        return $record;
    }

    public function getTitle(): string|Htmlable
    {
        return __('contact_submissions.pages.view_title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAsRead')
                ->label(__('contact_submissions.actions.mark_as_read'))
                ->icon(Heroicon::EnvelopeOpen)
                ->color('warning')
                ->visible(fn (): bool => $this->getRecord()->status === ContactSubmissionStatusEnum::New)
                ->action(function (): void {
                    /** @var ContactSubmission $record */
                    $record = $this->getRecord();
                    $record->markAsRead();
                    $this->record->refresh();

                    Notification::make()
                        ->title(__('contact_submissions.notifications.marked_as_read'))
                        ->success()
                        ->send();
                }),
            Action::make('markAsReplied')
                ->label(__('contact_submissions.actions.mark_as_replied'))
                ->icon(Heroicon::ArrowUturnLeft)
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->status !== ContactSubmissionStatusEnum::Replied)
                ->action(function (): void {
                    /** @var ContactSubmission $record */
                    $record = $this->getRecord();
                    $record->markAsReplied();
                    $this->record->refresh();

                    Notification::make()
                        ->title(__('contact_submissions.notifications.marked_as_replied'))
                        ->success()
                        ->send();
                }),
            Action::make('editNotes')
                ->label(__('contact_submissions.actions.edit_notes'))
                ->icon(Heroicon::PencilSquare)
                ->color('gray')
                ->modalHeading(__('contact_submissions.actions.edit_notes'))
                ->schema([
                    Textarea::make('admin_notes')
                        ->label(__('contact_submissions.fields.admin_notes'))
                        ->default(fn (): ?string => $this->getRecord()->admin_notes)
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    /** @var ContactSubmission $record */
                    $record = $this->getRecord();
                    $record->update([
                        'admin_notes' => $data['admin_notes'] ?? null,
                    ]);
                    $this->record->refresh();

                    Notification::make()
                        ->title(__('contact_submissions.notifications.notes_updated'))
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
