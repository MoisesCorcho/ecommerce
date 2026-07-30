<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Schemas;

use App\Actions\Reviews\ApproveReviewAction;
use App\Actions\Reviews\DeleteReviewAction;
use App\Actions\Reviews\UnapproveReviewAction;
use App\Filament\Resources\Reviews\Pages\ViewReview;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

final class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.name')
                    ->label(__('reviews.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label(__('reviews.fields.user'))
                    ->description(fn (Review $record): ?string => $record->user?->email)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label(__('reviews.fields.rating'))
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (int $state): string => (string) $state.'★')
                    ->sortable(),
                IconColumn::make('is_approved')
                    ->label(__('reviews.fields.is_approved'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_verified_purchase')
                    ->label(__('reviews.fields.is_verified_purchase'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('reviews.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label(__('reviews.filters.is_approved'))
                    ->placeholder(__('reviews.filters.all'))
                    ->trueLabel(__('reviews.filters.approved_only'))
                    ->falseLabel(__('reviews.filters.pending_only')),
                TernaryFilter::make('is_verified_purchase')
                    ->label(__('reviews.filters.is_verified'))
                    ->placeholder(__('reviews.filters.all'))
                    ->trueLabel(__('reviews.filters.verified_only'))
                    ->falseLabel(__('reviews.filters.unverified_only')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Review $record): string => ViewReview::getUrl(['record' => $record])),
                Action::make('approve')
                    ->label(__('reviews.actions.approve'))
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (Review $record): bool => ! $record->is_approved)
                    ->requiresConfirmation()
                    ->action(function (Review $record, ApproveReviewAction $approve): void {
                        $actor = Auth::user();
                        if ($actor === null) {
                            return;
                        }

                        $approve($actor, $record);

                        Notification::make()
                            ->title(__('reviews.notifications.approved'))
                            ->success()
                            ->send();
                    }),
                Action::make('unapprove')
                    ->label(__('reviews.actions.unapprove'))
                    ->icon(Heroicon::XCircle)
                    ->color('warning')
                    ->visible(fn (Review $record): bool => $record->is_approved)
                    ->requiresConfirmation()
                    ->action(function (Review $record, UnapproveReviewAction $unapprove): void {
                        $actor = Auth::user();
                        if ($actor === null) {
                            return;
                        }

                        $unapprove($actor, $record);

                        Notification::make()
                            ->title(__('reviews.notifications.unapproved'))
                            ->success()
                            ->send();
                    }),
                Action::make('delete')
                    ->label(__('reviews.actions.delete'))
                    ->icon(Heroicon::Trash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Review $record, DeleteReviewAction $delete): void {
                        $actor = Auth::user();
                        if ($actor === null) {
                            return;
                        }

                        $delete($actor, $record);

                        Notification::make()
                            ->title(__('reviews.notifications.deleted'))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([])
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleBottomCenterText)
            ->emptyStateHeading(__('reviews.empty.heading'))
            ->emptyStateDescription(__('reviews.empty.description'))
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
