<?php

namespace App\Observers;

use App\Helpers\CustomLog;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('email')) {
            $user->email_verified_at = null;
            $user->saveQuietly();
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $exception) {
                CustomLog::saveLog(
                    'ERROR',
                    'Error trying to send email verification notification',
                    $exception->getMessage(),
                );
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
