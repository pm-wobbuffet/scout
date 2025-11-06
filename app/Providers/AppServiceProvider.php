<?php

namespace App\Providers;

use App\Http\Resources\ExpansionResource;
use App\Http\Resources\ScoutResource;
use App\Listeners\ReverbMessageListener;
use Dedoc\Scramble\Scramble;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Str;
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
        VarDumper::setHandler((function ($var) {
            $cloner = new VarCloner();
            $cloner->setMaxItems(-1);
            $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();

            $dumper->dump($cloner->cloneVar($var));
        }));

        // Set reasonable rate limiter on API requests
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Disable stupid JSON data wrapping
        ScoutResource::withoutWrapping();
        ExpansionResource::withoutWrapping();

        // Add in reverb event listeners
        // Event::listen(
        //     ReverbMessageListener::class,
        // );

        // Set up scramble API docs for v1 and v2

        Scramble::ignoreDefaultRoutes();

        // Old V1 API
        Scramble::registerApi('v1', [
            'api_path' => 'api/v1',
            'version' => '1.0.0',
        ])->expose(
            ui: '/docs/api/v1',
            document: '/docs/api/v1/openapi.json'
        );

        // Newer V2 API
        Scramble::registerApi('v2', [
            'api_path' => 'api/v2',
            'version' => '2.0.0',
        ])->expose(
            ui: '/docs/api/v2',
            document: '/docs/api/v2/openapi.json'
        );

        // Add shorthand ways of referring to FQCNs for morphable relations
        Relation::enforceMorphMap([
            'spawn_point'           => 'App\Models\SpawnPoint',
            'custom_spawn_point'    => 'App\Models\ScoutCustomPoint',
        ]);
    }
}
