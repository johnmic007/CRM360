<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {

        if ($model instanceof \App\Models\User) {
            return;
        }
        $user = auth()->user();

        if ($user && $user->company_id) {
            $builder->where('company_id', $user->company_id);
        }
    }
}
