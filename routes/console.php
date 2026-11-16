<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('media:sync', function () {
    $result = \App\Models\Media::sync();
    $this->info("Media Library: {$result['added']} added, {$result['removed']} removed.");
})->purpose('Add files on the public disk to the Media Library and drop missing ones');
