<?php

class format
{
	public function rename_if_exists($dir, $filename)
	{
		$ext = strrchr($filename, '.');
		$prefix = substr($filename, 0, -strlen($ext));

		$i = 0;
		while (file_exists($dir . $filename))
		{ // If file exists, add a number to it.
			$filename = $prefix . ++$i . $ext;
		}

		return $filename;
	}
}