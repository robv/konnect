<?php

class Load
{	

	public static function js($file) {
		if(is_array($file))
			array_map(array('self','js'), $file);
		else
			return '<script src="' . WEB_ROOT . 'js/' . $file . '.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}
	
	public static function css($file, $type='screen')
	{
		return ' <link rel="Stylesheet" href="' . WEB_ROOT . 'styles/' . $file . '.css" type="text/css" media="' . $type . '" />'."\n";
	} 

}