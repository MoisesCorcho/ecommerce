<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Contact\ContactSubmissionStatusEnum;
use Database\Factories\ContactSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'name',
    'email',
    'subject',
    'message',
    'status',
    'ip_address',
    'user_agent',
    'read_at',
    'replied_at',
    'admin_notes',
])]
class ContactSubmission extends Model
{
    /** @use HasFactory<ContactSubmissionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContactSubmissionStatusEnum::class,
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): self
    {
        $this->forceFill([
            'status' => ContactSubmissionStatusEnum::Read,
            'read_at' => Carbon::now(),
        ])->save();

        return $this;
    }

    public function markAsReplied(): self
    {
        $this->forceFill([
            'status' => ContactSubmissionStatusEnum::Replied,
            'replied_at' => Carbon::now(),
        ])->save();

        return $this;
    }
}
