<?php
spl_autoload_register(function($class) {
	$ds = DIRECTORY_SEPARATOR;
	$filename = __DIR__.$ds.str_replace('\\', $ds, $class).'.php';
	$filename = strtolower($filename);

	if(is_readable($filename))
	{
		require_once $filename;
	}
});
