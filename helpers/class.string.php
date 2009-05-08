<?php

	class String
	{	
		// Singleton object. Leave $me alone.
		private static $me;

        // Get Singleton object
        public static function exec()
        {
            if (is_null(self::$me))
                self::$me = new String();
            return self::$me;
        }

	    // Creates a friendly URL slug from a 
	    function clean($str, $replacer = '-')
	    {
	        $str = preg_replace('/[^a-zA-Z0-9 -]/', '', $str);
	        $str = strtolower(str_replace(' ', $replacer, trim($str)));
	        $str = preg_replace('/\\' . $replacer . '+/', $replacer, $str);
	        return $str;
	    }
		
		// Formats a phone number as (xxx) xxx-xxxx or xxx-xxxx depending on the length.
	    function format_phone($phone)
	    {
	        $phone = preg_replace("/[^0-9]/", '', $phone);

	        if (strlen($phone) == 7)
	            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
	        elseif (strlen($phone) == 10)
	            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
	        else
	            return $phone;
	    }

	    // Converts a date/timestamp into the specified format
	    function format_date($date = null, $format = null)
	    {
	        if (is_null($format))
	            $format = 'Y-m-d H:i:s';

	        if (is_null($date))
	            $date = time();

	        // if $date contains only numbers, treat it as a timestamp
	        if (ctype_digit($date) === true)
	            return date($format, $date);
	        else
	            return date($format, strtotime($date));
	    }
	
	    // Outputs a filesize in human readable format.
	    function format_bytes($val, $round = 0)
	    {
	        $unit = array('','K','M','G','T','P','E','Z','Y');
	        while ($val >= 1000)
	        {
	            $val /= 1024;
	            array_shift($unit);
	        }
	        return round($val, $round) . array_shift($unit) . 'B';
	    }
	
	    // Ensures $str ends with a single /
	    function slash($str)
	    {
	        return rtrim($str, '/') . '/';
	    }

	    // Ensures $str DOES NOT end with a /
	    function unslash($str)
	    {
	        return rtrim($str, '/');
	    }
	
	    // Fixes MAGIC_QUOTES
	    function fix_slashes($arr = '')
	    {
	        if (is_null($arr) || $arr == '') return null;
	        if (!get_magic_quotes_gpc()) return $arr;
	        return is_array($arr) ? array_map(array($this, 'fix_slashes'), $arr) : stripslashes($arr);
	    }
	
	    function printr($var)
	    {
	        $output = print_r($var, true);
	        $output = str_replace("\n", "<br>", $output);
	        $output = str_replace(' ', '&nbsp;', $output);
	        echo "<div style='font-family:courier;'>$output</div>";
	    }
		
		/*
			usage:
			$a = array(array("id"=>10, "name"=>"joe"), array("id"=>11, "name"=>"bob"));
			$ids = array_pluck("id", $a);        // == array(10,11)
			$names = array_pluck("name", $a);    // == array("joe", "bob")

			works on non-keyed arrays also:

			$a = array(array(3,4), array(5,6));
			$col2 = array_pluck(1,$a);            // == array(4,6) (grab 2nd column of data)
		*/
		
		function array_pluck($key, $array)
		{
		    if (is_array($key) || !is_array($array)) return array();
		    $funct = create_function('$e', 'return is_array($e) && array_key_exists("'.$key.'",$e) ? $e["'. $key .'"] : null;');
		    return array_map($funct, $array);
		}
		
		// Generates a random numerical / alpha string of the given length
		function random($length = 10)
		{
			$numbers = array(1,2,3,4,5,6,7,8,9,0);
			$alpha = 'q,w,e,r,t,y,u,i,o,p,a,s,d,f,g,h,j,k,l,z,x,c,v,b,n,m';
			$alphaup = strtoupper($alpha);
			$letters = explode(',',$alpha);
			$lettersup = explode(',',$alphaup);
	
			$characters = array_merge($numbers,$letters,$lettersup);
			$string = array_rand($characters,$length);
			$string = implode('',$string);
			return $string;
		}
		
		// Safely serialize and unserialize arrays, should be used in place of just serialize
		function safe_serialize($array)
		{
			return base64_encode(serialize($array));
		}

		function safe_unserialize($array)
		{
			return unserialize(base64_decode($array));
		}
		
		// Truncates a string to $max in length and add's $moretext to the end of it
		function truncate($string, $max, $more_text)
		{
			if (is_array($string))
			{
		        return array_map(array($this, 'truncate'), $string);
			}
			else
			{
				if (strlen($string) > $max)
				{
					$max -= strlen($moretext);

					$new_string = strrev(strstr(strrev(substr($string,0,$max)), ' '));

					if ($new_string === '')
						$new_string = substr($string,0,$max);

					$new_string .= $more_text;

					$string = $new_string;
				}

				$string = balance_tags($string);

				return $string;
			}
		}
		
		// Useful when truncating, ensures html string does not leave tags open
		// Hideous but useful, TODO: Refactor balance tags function!
		function balance_tags($text) {

			$tagstack = array(); $stacksize = 0; $tagqueue = ''; $newtext = '';

			# WP bug fix for comments - in case you REALLY meant to type '< !--'
			$text = str_replace('< !--', '<    !--', $text);
			# WP bug fix for LOVE <3 (and other situations with '<' before a number)
			$text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

			while (preg_match("/<(\/?\w*)\s*([^>]*)>/",$text,$regex))
			{
				$newtext .= $tagqueue;

				$i = strpos($text,$regex[0]);
				$l = strlen($regex[0]);

				// clear the shifter
				$tagqueue = '';
				// Pop or Push
				if ($regex[1][0] == "/") 
				{ // End Tag
					$tag = strtolower(substr($regex[1],1));
					// if too many closing tags
					if ($stacksize <= 0) 
					{ 
						$tag = '';
						//or close to be safe $tag = '/' . $tag;
					}
					// if stacktop value = tag close value then pop
					else if ($tagstack[$stacksize - 1] == $tag) 
					{ // found closing tag
						$tag = '</' . $tag . '>'; // Close Tag
						// Pop
						array_pop ($tagstack);
						$stacksize--;
					} 
					else 
					{ // closing tag not at top, search for it
						for ($j=$stacksize-1;$j>=0;$j--) 
						{
							if ($tagstack[$j] == $tag) 
							{
								// add tag to tagqueue
								for ($k=$stacksize-1;$k>=$j;$k--)
								{
									$tagqueue .= '</' . array_pop ($tagstack) . '>';
									$stacksize--;
								}
								break;
							}
						}
						$tag = '';
					}
				} 
				else 
				{ 
					// Begin Tag
					$tag = strtolower($regex[1]);

					// Tag Cleaning

					// If self-closing or '', don't do anything.
					if ((substr($regex[2],-1) == '/') || ($tag == '')) 
					{
					}
					// ElseIf it's a known single-entity tag but it doesn't close itself, do so
					elseif ($tag == 'br' || $tag == 'img' || $tag == 'hr' || $tag == 'input') 
					{
						$regex[2] .= '/';
					} 
					else 
					{	// Push the tag onto the stack
						// If the top of the stack is the same as the tag we want to push, close previous tag
						if (($stacksize > 0) && ($tag != 'div') && ($tagstack[$stacksize - 1] == $tag)) 
						{
							$tagqueue = '</' . array_pop ($tagstack) . '>';
							$stacksize--;
						}
						$stacksize = array_push ($tagstack, $tag);
					}

					// Attributes
					$attributes = $regex[2];
					if ($attributes) 
					{
						$attributes = ' '.$attributes;
					}
					$tag = '<'.$tag.$attributes.'>';
					//If already queuing a close tag, then put this tag on, too
					if ($tagqueue) 
					{
						$tagqueue .= $tag;
						$tag = '';
					}
				}
				$newtext .= substr($text,0,$i) . $tag;
				$text = substr($text,$i+$l);
			}  

			// Clear Tag Queue
			$newtext .= $tagqueue;

			// Add Remaining text
			$newtext .= $text;

			// Empty Stack
			while ($x = array_pop($tagstack)) 
			{
				$newtext .= '</' . $x . '>'; // Add remaining tags to close
			}

			// WP fix for the bug with HTML comments
			$newtext = str_replace("< !--","<!--",$newtext);
			$newtext = str_replace("<    !--","< !--",$newtext);

			return $newtext;
		}
		
	}