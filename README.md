<p align="center"><a href="https://scout.wobbuffet.net" target="_blank"><img src="https://scout.wobbuffet.net/turtleknife.png" alt="Logo"></a></p>


## About Turtle Scout

Turtle Scout is a web application intended to easily facilitate the sharing of Hunt Mobs in Final Fantasy XIV Online. Features include:
- Point and click user interface
- One button sharing of collected mob lists
- Import of mob lists from in-game chat log messages
- Positioning of custom spawn points in zones that do not yet have fully identified spawn locations.

## About the Author

I'm Kaiden Alenko of Adamantoise! I'm a full time pharmacist and occasional hobby coder. Thus, my code is far from professional but I do hope it gets the job done.
If you have suggestions for improvements, feel free to find me in the Aether Hunts Discord. (https://discord.gg/aetherhunts)

## Under the Hood

Turtle Scout is based on the laravel framework, a PHP based framework. (Yes, I'm an old head and PHP was the first language I learned, so I've just kinda stuck with it.)
This project started about 8 days before the launch of Dawntrail, when we needed a tool relatively quickly to coordinate Hunt Trains post-launch, so, owing to the fact that 
it was done quickly, there may be bugs. Feel free to submit issues on the Issues tab. I will try to get to any pressing problems while working on the MSQ.

The frontend is done with Vue.js in concert with the Inertiajs adaptor to link it to the laravel backend without creating a full API.

Please note, this was my first time using Vue on a production build, as I wanted a new learning experience. As such, there may be wildly inefficient bits of code
or things that may not make sense, so I apologize to all who read this code.

## Cloning/Building Your Own
For local development, Laravel Sail is used to make things a bit easier. (Follow the Laravel sail guide for setting up the sail alias)
- Clone the repository to your local drive
- Install php dependencies with `composer install`
- Run `php artisan sail up` from the root directory and Sail should build the proper docker containers and services
- Run `sail artisan key:generate` to generate an application key (used for encrypting sessions and other Laravel specific data)
- Run `sail artisan migrate` to set up the initial DB schema
- Run `sail npm ci` to install node dependencies
- To have hot module reloading/local development, run `sail npm run dev` to have live changes reflected. For full static build, run `sail npm run build`
- Visit `http://localhost` and you should see the site up and running.
- If everything looks good, you'll want to seed initial data into your database
    - Run `sail artisan db:seed InitialDataSeeder` to import Expacs, Zones, and Mob data from resources/json/zones.js
    - Run `sail artisan db:seed SpawnPointDataSeeder` to import SpawnPoints from resources/json/spawn_points_hunthelper.js, sourced from the wonderful HuntHelper plogon.
    - Run `sail artisan db:seed SpawnPointMobSeeder` to import old expansion spawn point mob assignments, which I manually built in resources/csv/SPAWN_POINT_MOBS.csv

## Contributing

At this time, contributions will not be accepted, just because I'll be busy with Dawntrail things. Once life settles down a bit, I'll open things up.

## Code of Conduct

Please be nice.

## License

The framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
