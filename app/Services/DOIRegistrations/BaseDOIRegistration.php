<?php

namespace App\Services\DOIRegistrations;

use App\Interfaces\DOIRegistrationDriver;

abstract class BaseDOIRegistration implements DOIRegistrationDriver
{
    public function updateSettings(array $settings) {}
}
