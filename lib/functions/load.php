<?php

// simple autoloader for the functions

$files = glob(__DIR__ . '/*');

foreach ($files as $file)
{
	if ($file !== __FILE__)
	{
		require_once $file;
	}
}
