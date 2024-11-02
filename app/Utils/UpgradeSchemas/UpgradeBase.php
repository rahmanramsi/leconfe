<?php

namespace App\Utils\UpgradeSchemas;

use Illuminate\Console\Command;

abstract class UpgradeBase
{
    public function __invoke(?Command $command = null)
    {
        $this->run();
    }

    public function __construct(
        public string $databaseVersion,
        public string $applicationVersion,
    ) {}

    abstract public function run(): void;
}
