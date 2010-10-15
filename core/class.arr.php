<?php

class arr
{
	public static function key_values($arr, $key)
	{
		$key_arr = array();
		foreach ($arr as $v)
		{
			if (isset($v[$key]))
			{
				$key_arr[] = $v[$key];
			}
		}
		
		return $key_arr;
	}

	public static function reindex($arr, $key = 'id')
	{
		$new_arr = array();
		foreach ($arr as $v)
		{
			if (isset($v[$key]))
			{
				$new_arr[$v[$key]] = $v;
			}
		}
		
		return $new_arr;
	}

	public static function overwrite($array1, $array2)
	{
		foreach (array_intersect_key($array2, $array1) as $key => $value)
		{
			$array1[$key] = $value;
		}

		if (func_num_args() > 2)
		{
			foreach (array_slice(func_get_args(), 2) as $array2)
			{
				foreach (array_intersect_key($array2, $array1) as $key => $value)
				{
					$array1[$key] = $value;
				}
			}
		}

		return $array1;
	}
	
	public static function kv_array($value)
	{
		$arr = array();
		$records = explode('|', trim($value));
		foreach ($records as $pair)
		{
			$pair = explode(',', $pair);
			if ($pair && count($pair) == 2)
			{
				$arr[$pair[0]] = $pair[1];
			}
		}
		
		return $arr;
	}
	
	public static function delimited($value, $delimiter = ',')
	{
		$arr = array();
		
		if ($value)
		{
			$arr = array_map('trim', @explode($delimiter, trim($value)));			
		}
		
		return $arr;
	}

	public static function csv($arr, $file)
	{
		file_exists($file) and die("<i>$file</i> already exists!</i>");

		$fp = fopen($file, 'w');
		foreach ($arr as $v)
		{
			fputcsv($fp, $v);
		}
		fclose($fp);
	}
}