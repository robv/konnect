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
				$field_info = new Field_information();
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

								if(!empty($_FILES[$value['name']]['name'])){
									
									$gd = new GD();
									
										// rand_string() is located in core/extfunctions.inc.php
										$newname = $object_copy->$name = rand_string().'_'.$_FILES[$value['name']]['name'];
										
										/*
											TODO: There should be a way in options to pass multiple image locations and sizes
										*/
										
										copy($_FILES[$value['name']]['tmp_name'],'./files/uploads/original/'.$newname);
										
										$gd->scaleAndSave($value['name'],$newname,'600','500','./files/uploads/large/');
										$gd->scaleAndSave($value['name'],$newname,'200','200','./files/uploads/medium/');
										$gd->scaleAndSave($value['name'],$newname,'100','100','./files/uploads/small/');

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
						$object_copy->insert();
					else
						$object_copy->update();
						
					unset($object_copy);
				}
			return true; // passed validation
		}
		else {
			return false; // didn't pass validation
		}
	}


}