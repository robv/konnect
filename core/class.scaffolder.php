<?PHP

class Scaffolder extends Forms {

	public $table;
	public $currentId;
	public $currentData;
	
	// Build all your info first and then send it to constructor for display
	function __construct($table,$currentId='',$iterations='1',$wrapper='',$seperator='')
	{
		$this->table = ucfirst($table);
		$this->currentId = $currentId;
		
		if(class_exists($table) && (get_parent_class($table) == 'DBObject'))
		{
			$this->currentData = new $table();
			
			if(!empty($this->currentId))
				$this->currentData->select($currentId);
		}
		else
		{
			echo '<p class="error"><strong>Framework Error:</strong><br/>Database object does not exist for table: '.strtolower($this->table).'</p>';
			die;
		}
		
		parent::__construct($this->table,$iterations,$wrapper,$seperator);
		
		$this->build();
		
	}
	
	function build()
	{
		$db = Database::getDatabase();
		
		$cols = $this->currentData->getCols();
		
        foreach($cols as $field_name => $v):
			
			$res = $db->query('SELECT `'.$field_name.'` FROM '.strtolower($this->table));
			$type  = mysql_field_type($res,0);
			$extra = mysql_field_flags($res,0);
			$notnullsearch = strpos($extra,'not_null');
			
				if ($notnullsearch === false){ $options['validate'] = ''; }
				else { $options['validate'] = 'blank'; }
				
				$field_type = ($type === 'blob') ? 'htmleditor' : 'text';
				$field_type = ($type === 'timestamp') ? 'timestamp' : $field_type;
				$field_type = ($field_name === 'password') ? 'password' : $field_type;
				
				
				// Grab rows where field name matches
				$field_info = new Konnect_field_information();
				$field_info->select(array($this->table,$field_name),array('table_name','name'));
				
				if(!is_null($field_info->type)){
					$field_type = $field_info->type;
					if($field_type === 'file') // shouldn't validate for blank if it's a file.... TODO: Come up with a way to validate files
						$options['validate'] = '';
				}
					
					if(!is_null($field_info->options)){
						$opt_builder = $field_info->options;
						$opt_builder = explode('|',$opt_builder);
							foreach($opt_builder as $option):
								if(!empty($option)){
									$option_explode = explode(',',$option);
									$options['options'][$option_explode['0']] = $option_explode['1'];
								}
							endforeach;
					}
					else{
						$options['options'] = array();
					}
				
				$options['value'] = $this->currentData->$field_name;
			
			$this->addfield($field_name,$field_type,$options);
			
		endforeach;
		
	}
	
	function saveObject() // This method adds / updates field entries into database
	{
		global $Error;
		
		$this->validate();
		
		if($Error->ok()){		

				// Loop through iterations and build new field names
				for($i=0;$i<$this->iterations;$i++){
					
					$object_copy = $this->currentData;
					
						foreach($this->fields[$i] as $name => $value) :
						
							if($value['type'] === 'file'){
								
								// Check if a session of images to crop already exists, if so we need to overwrite it
								if(isset($_SESSION['crop_images']))
									unset($_SESSION['crop_images']);

								if(!empty($_FILES[$value['name']]['name'])){
									
									$gd = new GD();
									
									// Check if file is already existing and start deleting
									if(strlen($object_copy->$name) > 0){
										@unlink('./files/uploads/original/'.$object_copy->$name);
											@unlink('./files/uploads/large/'.$object_copy->$name);
											@unlink('./files/uploads/medium/'.$object_copy->$name);
											@unlink('./files/uploads/small/'.$object_copy->$name);
									}
									
									
										// If a file of that name already exists add random string to begining else keep same name
										if(file_exists('./files/uploads/original/'.$_FILES[$value['name']]['name'])){
											// rand_string() is located in core/extfunctions.inc.php
											$newname = $object_copy->$name = str_replace(' ','',rand_string().'_'.$_FILES[$value['name']]['name']);
										} else {	
											$newname = $object_copy->$name = str_replace(' ','',$_FILES[$value['name']]['name']);
										}
									
										
										/*
											TODO: There should be a way in options to pass multiple image locations and sizes
										*/
										
										$gd->loadFile($_FILES[$value['name']]['tmp_name']);
										$gd->scaleSafe('1800','1800');
										$gd->saveAs('./files/uploads/original/'.$newname);
										$gd->scaleSafe('700','700');
										$gd->saveAs('./files/uploads/large/'.$newname);
										$gd->scaleSafe('300','300');
										$gd->saveAs('./files/uploads/medium/'.$newname);
										$gd->cropCentered('200','200');
										$gd->saveAs('./files/uploads/cropped/'.$newname);
										$gd->loadFile($_FILES[$value['name']]['tmp_name']);
										$gd->scaleSafe('150','150');
										if($gd->saveAs('./files/uploads/small/'.$newname))
											$_SESSION['crop_images'][str_replace(array('.',' '),'',$newname)] = $newname;
								
								}
							
							}
							elseif($value['type'] === 'timestamp'){
								$object_copy->$name = date('Y-m-d H:i:s',strtotime(str_replace('@','',$value['value'])));
							}
							else  // Any other type
							{
								$object_copy->$name = $value['value'];
							}

						endforeach;
					
					if(empty($this->currentId))
						$this->currentId = $object_copy->insert();
					else
						$object_copy->update();
						
					$this->currentData = $object_copy;
						
					unset($object_copy);
				}
			return true; // passed validation
		}
		else {
			return false; // didn't pass validation
		}
	}


}