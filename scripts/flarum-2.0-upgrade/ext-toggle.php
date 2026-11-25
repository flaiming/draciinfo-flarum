<?php
// Enable or disable extensions (runs their migrations), like the admin panel does.
// Usage, from the Flarum root: php scripts/flarum-2.0-upgrade/ext-toggle.php enable|disable <extension-id>...
$site = require getcwd().'/site.php';
$manager = $site->bootApp()->getContainer()->make(Flarum\Extension\ExtensionManager::class);
$action = $argv[1] ?? '';
if (! in_array($action, ['enable', 'disable'], true) || count($argv) < 3) {
    fwrite(STDERR, "usage: php ext-toggle.php enable|disable <extension-id>...\n");
    exit(1);
}
foreach (array_slice($argv, 2) as $id) {
    $action === 'enable' ? $manager->enable($id) : $manager->disable($id);
    echo "$action $id OK\n";
}
