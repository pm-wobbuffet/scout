<?php

namespace App\Providers;

use App\Http\Resources\ScoutResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

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
        // Show unlimited depth var dumps for debugging
        VarDumper::setHandler((function($var) {
            $cloner = new VarCloner();
            $cloner->setMaxItems(-1);
            $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();

            $dumper->dump($cloner->cloneVar($var));
        }));

        // Set reasonable rate limiter on API requests
        RateLimiter::for('api', function(Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Disable stupid JSON data wrapping
        ScoutResource::withoutWrapping();
        
        // Add shorthand ways of referring to FQCNs for morphable relations
        Relation::enforceMorphMap([
            'spawn_point'           => 'App\Models\SpawnPoint',
            'custom_spawn_point'    => 'App\Models\ScoutCustomPoint',
        ]);
    }
}
