<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Address;
use App\Models\Entity;
use App\Models\UserProfile;
use App\Policies\AddressPolicy;
use App\Policies\EntityPolicy;
use App\Policies\UserProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        UserProfile::class => UserProfilePolicy::class,
        Entity::class => EntityPolicy::class,
        Address::class => AddressPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }

    public function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
