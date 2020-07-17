<?php


namespace App\Libs;


use Tracy\ILogger;

class StdLogger implements ILogger
{

	function log($value, $level = self::INFO)
	{
		$stderr = fopen('php://stderr', 'w');
		fwrite($stderr, $value);
		fclose($stderr);
	}
}