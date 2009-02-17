<?PHP

	
/*	-------------------------------------------------------------------------------
		THESE FUNCTIONS ARE CREATED JUST TO SIMPLIFY INCLUDING SEVERAL JS FILES
	------------------------------------------------------------------------------- */
	
	function jsFile($file='jquery')
	{
		return '<script src="'.WEB_ROOT.'js/'.$file.'.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}
	
	function cssFile($file='screen',$type='screen')
	{
		return '<style type="text/css" media="'.$type.'">@import "'.WEB_ROOT.'styles/'.$file.'.css";</style>'."\n";
	}
	
	function tinymce(){
		return '<script src="'.WEB_ROOT.'js/plugins/tiny_mce/tiny_mce.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}
	
	function jquery($load=''){
		
		$return = '';
		
		if(!empty($load)){
			$array = explode(',',$load);
			foreach($array as $load):
				$return .= $load();
			endforeach;
		}
		
		return '<script src="'.WEB_ROOT.'js/jquery.js" type="text/javascript" charset="utf-8"></script>'."\n".$return;
	}
	
	function jcommon(){
		return '<script src="'.WEB_ROOT.'js/common.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}

		function adminjcommon(){
			return '<script src="'.WEB_ROOT.'js/admincommon.js" type="text/javascript" charset="utf-8"></script>'."\n";
		}
	
	function equalHeight(){
		return '<script src="'.WEB_ROOT.'js/plugins/jquery.equalHeight.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}
	
	function wymeditor(){
		return '<script src="'.WEB_ROOT.'js/plugins/wymeditor/jquery.wymeditor.pack.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}
	
	function flash(){
		return '<script src="'.WEB_ROOT.'js/plugins/jquery.flash.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}
	
	function datepicker(){
		return '<style type="text/css" media="all">@import "'.WEB_ROOT.'js/plugins/datepicker/css/datepicker.css";</style>'."\n"
		. '<script src="'.WEB_ROOT.'js/plugins/datepicker/datepicker.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}

	function slug(){
		return '<script src="'.WEB_ROOT.'js/plugins/jquery.slug.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}

	function facebox(){
		return '<style type="text/css" media="all">@import "'.WEB_ROOT.'js/plugins/facebox/facebox.css";</style>'."\n"
		. '<script src="'.WEB_ROOT.'js/plugins/facebox/facebox.js" type="text/javascript" charset="utf-8"></script>'."\n"
		. '<script type="text/javascript">
		    jQuery(document).ready(function($) {
		      $(\'a[rel*=facebox]\').facebox({
		        loading_image : \''.WEB_ROOT.'js/plugins/facebox/loading.gif\',
		        close_image : \''.WEB_ROOT.'js/plugins/facebox/closelabel.gif\'
		      })
		    })
		  </script>';
	}

	function fancybox(){
		return '<style type="text/css" media="all">@import "'.WEB_ROOT.'js/plugins/fancybox/fancybox.css";</style>'."\n"
		. '<script src="'.WEB_ROOT.'js/plugins/jquery.fancybox.js" type="text/javascript" charset="utf-8"></script>'."\n"
		. '<script src="'.WEB_ROOT.'js/plugins/jquery.pngFix.js" type="text/javascript" charset="utf-8"></script>'."\n";
	}

/*
	-------------------------------------------------------------------------------
*/	
	

	// Simple check to see if a table exists in the database
	function mysql_is_table($tbl)
	{
		$db = Database::getDatabase();

	    $tables = array();
	    $q = $db->query('SHOW TABLES');
	    while ($r = mysql_fetch_array($q)) { $tables[] = $r[0]; }

	    if (in_array($tbl, $tables)) { return true; }
	    else { return false; }
	}
	
	function users_exist()
	{
		$db = Database::getDatabase();
		
		if(!mysql_is_table('users')){
			return false;
		}
	
		return $db->numRows('SELECT * FROM users');
	}

	function rand_string($length=10){
	
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

	function deslugify($str)
	{	
		$search = array(' ','-','/','_',':');
			
		if(is_array($str)){

			foreach($str as $key => $value):

				$str[$key] = deslugify($value);
	
			endforeach;
	
		}else{
	
			$str = str_replace($search,'',$str);
			$str = strtolower($str);
	
		}
			
		return $str;
		
	}
	
	function safe_serialize($array){
		return base64_encode(serialize($array));
	}
	
	function safe_unserialize($array){
		return unserialize(base64_decode($array));
	}
	
	function submit($trigger = 'submit')
	{
		return (isset($_POST[$trigger]) || isset($_POST[$trigger.'_x']) || isset($_GET[$trigger]) || isset($_GET[$trigger.'_x']) ? true : false);
	}
	

	function truncate_string($key,$max,$moretext){
		if(is_array($key)){
			for($i=0;$i<count($key);$i++){
				$key[$i] = truncate_string($key[$i],$max,$moretext);
			}
		}
		else {
		$key = truncate_string_run($key,$max,$moretext);
		}

		return $key;
	}


	function truncate_string_run($string,$max,$moretext){

	if(strlen($string) > $max){

	$max -= strlen($moretext);

	$new_string = strrev(strstr(strrev(substr($string,0,$max)), ' '));

	if($new_string === ''){ $new_string = substr($string,0,$max); }

	$new_string .= $moretext;

	$string = $new_string;

	}

	$string = balance_tags($string);

	return $string;

	}


	function balance_tags($text) {

		$tagstack = array(); $stacksize = 0; $tagqueue = ''; $newtext = '';

		# WP bug fix for comments - in case you REALLY meant to type '< !--'
		$text = str_replace('< !--', '<    !--', $text);
		# WP bug fix for LOVE <3 (and other situations with '<' before a number)
		$text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

		while (preg_match("/<(\/?\w*)\s*([^>]*)>/",$text,$regex)) {
			$newtext .= $tagqueue;

			$i = strpos($text,$regex[0]);
			$l = strlen($regex[0]);

			// clear the shifter
			$tagqueue = '';
			// Pop or Push
			if ($regex[1][0] == "/") { // End Tag
				$tag = strtolower(substr($regex[1],1));
				// if too many closing tags
				if($stacksize <= 0) { 
					$tag = '';
					//or close to be safe $tag = '/' . $tag;
				}
				// if stacktop value = tag close value then pop
				else if ($tagstack[$stacksize - 1] == $tag) { // found closing tag
					$tag = '</' . $tag . '>'; // Close Tag
					// Pop
					array_pop ($tagstack);
					$stacksize--;
				} else { // closing tag not at top, search for it
					for ($j=$stacksize-1;$j>=0;$j--) {
						if ($tagstack[$j] == $tag) {
						// add tag to tagqueue
							for ($k=$stacksize-1;$k>=$j;$k--){
								$tagqueue .= '</' . array_pop ($tagstack) . '>';
								$stacksize--;
							}
							break;
						}
					}
					$tag = '';
				}
			} else { // Begin Tag
				$tag = strtolower($regex[1]);

				// Tag Cleaning

				// If self-closing or '', don't do anything.
				if((substr($regex[2],-1) == '/') || ($tag == '')) {
				}
				// ElseIf it's a known single-entity tag but it doesn't close itself, do so
				elseif ($tag == 'br' || $tag == 'img' || $tag == 'hr' || $tag == 'input') {
					$regex[2] .= '/';
				} else {	// Push the tag onto the stack
					// If the top of the stack is the same as the tag we want to push, close previous tag
					if (($stacksize > 0) && ($tag != 'div') && ($tagstack[$stacksize - 1] == $tag)) {
						$tagqueue = '</' . array_pop ($tagstack) . '>';
						$stacksize--;
					}
					$stacksize = array_push ($tagstack, $tag);
				}

				// Attributes
				$attributes = $regex[2];
				if($attributes) {
					$attributes = ' '.$attributes;
				}
				$tag = '<'.$tag.$attributes.'>';
				//If already queuing a close tag, then put this tag on, too
				if ($tagqueue) {
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
		while($x = array_pop($tagstack)) {
			$newtext .= '</' . $x . '>'; // Add remaining tags to close
		}

		// WP fix for the bug with HTML comments
		$newtext = str_replace("< !--","<!--",$newtext);
		$newtext = str_replace("<    !--","< !--",$newtext);

		return $newtext;
	}
