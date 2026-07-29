<?php

namespace App\Jobs;

use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected User $user,
        protected string $temporaryPassword,
    ) {
    }

    public function handle(): void
    {
        if (! $this->user->email) {
            Log::warning('Password reset email skipped: no email address', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        Mail::to($this->user->email)->send(
            new TemporaryPasswordMail($this->user, $this->temporaryPassword)
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Password reset email failed permanently', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}