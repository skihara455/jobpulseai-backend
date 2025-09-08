<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Role;
use App\Models\Job;
use App\Models\Application;
use App\Models\Mentor;
use App\Models\Company;

use App\Policies\UserPolicy;
use App\Policies\RolePolicy;
use App\Policies\JobPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\MentorPolicy;
use App\Policies\CompanyPolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class        => UserPolicy::class,
        Role::class        => RolePolicy::class,
        Job::class         => JobPolicy::class,
        Application::class => ApplicationPolicy::class,
        Mentor::class      => MentorPolicy::class,
        Company::class     => CompanyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

