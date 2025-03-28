<?php

namespace App\Providers;
use Socialite;
use App\Services\Socialite\RedditProvider;
#use Illuminate\Support\ServiceProvider;



use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;
class AppServiceProvider extends ServiceProvider
{
    /** 
     * Register any application services.
    */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Access Socialite Factory
        //$socialite = app(Factory::class);

        // Register Reddit as a custom driver
        /*
        $socialite->extend('reddit', function ($app) use ($socialite) {
            $config = $app['config']['services.reddit'];  // Get Reddit config from services.php

            return $socialite->buildProvider(RedditProvider::class, $config);
        });
        */
        parent::boot();
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('reddit', \SocialiteProviders\Reddit\Provider::class);
        });
    }
}
