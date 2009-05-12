<?php
// Class Autoloader
function __autoload($class_name)
{
 	$folders = array('core','helpers','libraries');
	foreach ($folders as $folder)
	{
		if (file_exists(DOC_ROOT . $folder . '/class.' . strtolower($class_name) . '.php'))
 		{
			require DOC_ROOT .  $folder . '/class.' . strtolower($class_name) . '.php';
			break;
		}
	}
}