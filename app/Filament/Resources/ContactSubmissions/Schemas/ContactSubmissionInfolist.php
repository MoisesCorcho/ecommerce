<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactSubmissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ContactSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('contact_submissions.sections.sender'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('contact_submissions.fields.name')),
                        TextEntry::make('email')
                            ->label(__('contact_submissions.fields.email'))
                            ->copyable(),
                        TextEntry::make('user.name')
                            ->label(__('contact_submissions.fields.user'))
                            ->placeholder('—'),
                        TextEntry::make('ip_address')
                            ->label(__('contact_submissions.fields.ip_address'))
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label(__('contact_submissions.fields.created_at'))
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('status')
                            ->label(__('contact_submissions.fields.status'))
                            ->badge(),
                    ])
                    ->columns(2),
                Section::make(__('contact_submissions.sections.message'))
                    ->schema([
                        TextEntry::make('subject')
                            ->label(__('contact_submissions.fields.subject'))
                            ->columnSpanFull()
                            ->weight('bold'),
                        TextEntry::make('message')
                            ->label(__('contact_submissions.fields.message'))
                            ->columnSpanFull()
                            ->prose(),
                    ]),
                Section::make(__('contact_submissions.sections.management'))
                    ->schema([
                        TextEntry::make('read_at')
                            ->label(__('contact_submissions.fields.read_at'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('replied_at')
                            ->label(__('contact_submissions.fields.replied_at'))
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('admin_notes')
                            ->label(__('contact_submissions.fields.admin_notes'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
