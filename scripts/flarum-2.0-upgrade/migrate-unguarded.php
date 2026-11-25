<?php
// One-off for the 1.x -> 2.0 upgrade: `php flarum migrate` with Eloquent
// mass-assignment protection off. fof/links' 2024_10_11_000001 migration calls
// Permission::create(), which Flarum 2.0's Permission model (no $fillable) refuses.
// Usage, from the Flarum root: php scripts/flarum-2.0-upgrade/migrate-unguarded.php
$site = require getcwd().'/site.php';
Illuminate\Database\Eloquent\Model::unguard();
$_SERVER['argv'] = [$_SERVER['argv'][0], 'migrate'];
(new Flarum\Console\Server($site))->listen();
