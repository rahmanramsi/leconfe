<?php

namespace App\Infolists\Components;

use App\Actions\Leconfe\CheckLatestVersion;
use Awcodes\Shout\Components\ShoutEntry;
use Filament\Support\Colors\Color;

class ShoutUpdateVersion extends ShoutEntry
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->visible(function () {
                try {
                    return CheckLatestVersion::isUpdateAvailable();
                } catch (\Throwable $th) {
                    return false;
                }
            })
            ->content(function () {
                $data = CheckLatestVersion::run();

                return view('panel.administration.components.update-available', [
                    'latestVersion' => $data['tag'],
                    'info' => $data['info'],
                    'currentVersion' => app()->getCodeVersion(),
                ]);
            })
            ->color(Color::Blue);
    }
}
