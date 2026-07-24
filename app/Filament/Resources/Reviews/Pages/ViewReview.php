<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Pages;

use App\Actions\Reviews\ApproveReviewAction;
use App\Actions\Reviews\DeleteReviewAction;
use App\Actions\Reviews\UnapproveReviewAction;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function resolveRecord(int|string $key): Review
    {
        /** @var Review $record */
        $record = parent::resolveRecord($key);
        $record->loadMissing(['product', 'user']);

        return $record;
    }

    public function getTitle(): string|Htmlable
    {
        return __('reviews.pages.view_title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label(__('reviews.actions.approve'))
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->visible(fn (): bool => ! $this->getRecord()->is_approved)
                ->requiresConfirmation()
                ->action(function (ApproveReviewAction $approve): void {
                    $actor = Auth::user();
                    if ($actor === null) {
                        return;
                    }

                    /** @var Review $review */
                    $review = $this->getRecord();
                    $approve($actor, $review);
                    $this->record->refresh();

                    Notification::make()
                        ->title(__('reviews.notifications.approved'))
                        ->success()
                        ->send();
                }),
            Action::make('unapprove')
                ->label(__('reviews.actions.unapprove'))
                ->icon(Heroicon::XCircle)
                ->color('warning')
                ->visible(fn (): bool => $this->getRecord()->is_approved)
                ->requiresConfirmation()
                ->action(function (UnapproveReviewAction $unapprove): void {
                    $actor = Auth::user();
                    if ($actor === null) {
                        return;
                    }

                    /** @var Review $review */
                    $review = $this->getRecord();
                    $unapprove($actor, $review);
                    $this->record->refresh();

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
                ->action(function (DeleteReviewAction $delete): void {
                    $actor = Auth::user();
                    if ($actor === null) {
                        return;
                    }

                    /** @var Review $review */
                    $review = $this->getRecord();
                    $delete($actor, $review);

                    Notification::make()
                        ->title(__('reviews.notifications.deleted'))
                        ->success()
                        ->send();

                    $this->redirect(ListReviews::getUrl());
                }),
        ];
    }
}
