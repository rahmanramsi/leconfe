<?php

namespace App\Http;

use Illuminate\Support\Str;

class Request extends \Illuminate\Http\Request
{

	public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
		parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);

		$scriptName = $_SERVER['SCRIPT_NAME'];
		$scriptPath = dirname($scriptName);
		$fileName = basename($scriptName);

		if($scriptPath !== '' && $scriptPath !== '/' && Str::endsWith($fileName, '.php')){
			$this->baseUrl = Str::before($scriptPath, '/public');
		}
	}


	public function duplicate(?array $query = null, ?array $request = null, ?array $attributes = null, ?array $cookies = null, ?array $files = null, ?array $server = null): static
    {
		$dup = parent::duplicate($query, $request, $attributes, $cookies, $files, $server);

		$scriptName = $_SERVER['SCRIPT_NAME'];
		$scriptPath = dirname($scriptName);

		if($scriptPath !== '' && $scriptPath !== '/'){
			$dup->baseUrl = Str::before($scriptPath, '/public');
		}

		return $dup;
	}

}