<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

use Flarum\Extend;


// get all locale files
$path = __DIR__.'/locale/';
$locales = [];
foreach (scandir($path) as $f) {
    if ($f == '.' || $f == '..')
        continue;
    foreach (scandir($path.$f) as $ff) {
        if ($ff == '.' || $ff == '..')
            continue;
        array_push($locales, new Extend\Locales($path.$f.'/'.$ff));
    }
}

return $locales;
