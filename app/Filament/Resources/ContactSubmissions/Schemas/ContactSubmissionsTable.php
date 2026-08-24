<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactSubmissions\Schemas;

use App\Enums\Contact\ContactSubmissionStatusEnum;
use App\Filament\Resources\ContactSubmissions\Pages\ViewContactSubmission;
use App\Models\ContactSubmission;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ContactSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label(__('contact_submissions.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label(__('contact_submissions.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('subject')
                    ->label(__('contact_submissions.fields.subject'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('status')
                    ->label(__('contact_submissions.fields.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('contact_submissions.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('contact_submissions.filters.status'))
                    ->options(ContactSubmissionStatusEnum::class),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (ContactSubmission $record): string => ViewContactSubmission::getUrl(['record' => $record])),
                Action::make('markAsRead')
                    ->label(__('contact_submissions.actions.mark_as_read'))
                    ->icon(Heroicon::EnvelopeOpen)
                    ->color('warning')
                    ->visible(fn (ContactSubmission $record): bool => $record->status === ContactSubmissionStatusEnum::New)
                    ->action(function (ContactSubmission $record): void {
                        $record->markAsRead();

                        Notification::make()
                            ->title(__('contact_submissions.notifications.marked_as_read'))
                            ->success()
                            ->send();
                    }),
                Action::make('markAsReplied')
                    ->label(__('contact_submissions.actions.mark_as_replied'))
                    ->icon(Heroicon::ArrowUturnLeft)
                    ->color('success')
                    ->visible(fn (ContactSubmission $record): bool => $record->status !== ContactSubmissionStatusEnum::Replied)
                    ->action(function (ContactSubmission $record): void {
                        $record->markAsReplied();

                        Notification::make()
                            ->title(__('contact_submissions.notifications.marked_as_replied'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ]);
    }
}
