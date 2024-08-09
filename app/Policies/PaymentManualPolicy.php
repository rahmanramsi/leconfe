<?php

namespace App\Policies;

use App\Models\PaymentManual;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentManualPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('PaymentManual:viewAny')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('PaymentManual:create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PaymentManual $paymentManual): bool
    {
        if ($user->can('PaymentManual:update')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PaymentManual $paymentManual): bool
    {
        if ($user->can('PaymentManual:delete')) {
            return true;
        }
    }
}
