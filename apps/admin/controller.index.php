<?php

class Index_controller extends Controller {
	
	public $defaultMethod = 'dashboard';
	public $app_name;
	
	function __construct($app_name,$data = '')
	{
		global $Auth;
		
		$this->app_name = $app_name;
		
		// Building the method name
		if(!isset($this->data['konnect']['rewritten_path']['2']) || empty($this->data['konnect']['rewritten_path']['2']))
			$method = $this->defaultMethod;
		else
			$method = $this->data['konnect']['rewritten_path']['2'];
		
		// This first checks to see if users exist
		// If not it assumes you don't want the admin protected
		// If so it ensures the logged in user is an administrator
		if(users_exist()){ $Auth->requireAdmin(WEB_ROOT.'login/'); };
		
		$data['header_links_return'] = new Konnect_links();
		$data['header_links_return'] = $data['header_links_return']->getLinks();
		$data['header_links'] = $data['header_links_return']['object'];
		$data['header_sub_links'] = $data['header_links_return']['sub_links'];
	
		parent::__construct($method,$data);
		
	}

	public function dashboard()
	{
			$this->data['pageTitle'] = 'Your Dashboard';
			$this->loadView('dashboard');
	}
	
	public function delete()
	{		
		global $Flash;
		
			$this->data['table_name'] = deslugify($this->data['url_structure']['2'],'_');
			$this->data['pageTitle'] = 'Delete '.ucwords(deslugify($this->data['table_name'],' '));
			$obj_name = ucfirst($this->data['table_name']);
			
			$delete = new $obj_name($this->data['url_structure']['3']);
			$delete->delete();
			
			$Flash->set('<p class="success">You\'re entry was deleted successfully.</p>');
			redirect(WEB_ROOT.'admin/manage/'.$this->data['url_structure']['2'].'/');
	
	}

	public function add($templateFile='admin/edit_save')
	{
		global $Auth,$Flash;
		
		// Just makes sure crop_images isn't being added to but being writen from scratch
		if(isset($_SESSION['crop_images']))
			unset($_SESSION['crop_images']);
		
		$seperators = array('-','+','_',' ');
		
		// Setting the default table, really this should never be the case
		if(!isset($this->data['url_structure']['2']))
			$this->data['url_structure']['2'] = $this->data['pageTitle'] = 'News';
		else
			$this->data['pageTitle'] = ucwords(deslugify($this->data['url_structure']['2'],' '));
			
		
		// Array key "3" is where iterations reside, if it's not set then set it to "1"
		if(isset($this->data['url_structure']['3']) && is_numeric($this->data['url_structure']['3']))
			$this->data['iterations'] = $this->data['url_structure']['3'];
		else
			$this->data['iterations'] = 1;
			
		
		$scaffold = new Scaffolder(deslugify($this->data['url_structure']['2'],'_'),'',$this->data['iterations']);
		$scaffold->iterate();
		
		// Grab user preferences for what to do after form submit
		$this->data['preference'] = new User_preferences();
		$this->data['preference']->select(array($Auth->id,'next'),array('user','preference'));
		
			if(submit()){
				
				if($scaffold->saveObject()){
				
					if($templateFile !== 'admin/edit_save'){
						
						$valueField = addslashes(mysql_real_escape_string($_GET['valueField']));
						$textField = addslashes(mysql_real_escape_string($_GET['textField']));
						$idField = $_GET['idField'];
						
						// Do Something
						$this->data['jscript'] = '<script type="text/javascript">
							jQuery(function($) {
								window.parent.$("#'.$idField.'").addOption("'.$scaffold->currentData->$valueField.'", "'.$scaffold->currentData->$textField.'");
							});
						</script>';
						
					} elseif(isset($_POST['next']) && $_POST['next'] === 'add'){
						
						// Updated or inserting user preference
						$this->data['preference']->setPreference($Auth->id);
						
						// We need to redirect to image cropper or we don't....
						if(isset($_SESSION['crop_images']) && is_array($_SESSION['crop_images'])){
							$_SESSION['crop_redirect'] = WEB_ROOT.'admin/add/'.$this->data['url_structure']['2'].'/';
							$_SESSION['crop_flash'] = '<p class="success">You\'re entry was added successfully, you can add another below or <a href="'.WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$scaffold->currentId.'/"> click here to review / edit that entry</a>.</p>';
							redirect(WEB_ROOT.'admin/cropper/');
						} else {	
							$Flash->set('<p class="success">You\'re entry was added successfully, you can add another below or <a href="'.WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$scaffold->currentId.'/"> click here to review / edit that entry</a>.</p>');
							redirect(WEB_ROOT.'admin/add/'.$this->data['url_structure']['2'].'/');
						}
				
					} else {
						
						// Updated or inserting user preference
						$this->data['preference']->setPreference($Auth->id,'edit');
						
						// We need to redirect to image cropper or we don't....
						if(isset($_SESSION['crop_images']) && is_array($_SESSION['crop_images'])){
							$_SESSION['crop_redirect'] = WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$scaffold->currentId.'/';
							$_SESSION['crop_flash'] = '<p class="success">You\'re entry was added successfully, you can make edits below.</p>';
							redirect(WEB_ROOT.'admin/cropper/');
						} else {
							$Flash->set('<p class="success">You\'re entry was added successfully, you can make edits below.</p>');
							redirect(WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$scaffold->currentId.'/');
						}
				
					}
				}
			}
			
		$this->data['form'] = $scaffold->display();
		
		$this->loadView($templateFile);
		
	}
	

	public function modalForm()
	{
		$this->add('admin/_modal_edit_save');
	}

	public function edit()
	{
		global $Flash;
		
		// Just makes sure crop_images isn't being added to but being writen from scratch
		if(isset($_SESSION['crop_images']))
			unset($_SESSION['crop_images']);
			
		
		// Setting default table to "News"
		if(!isset($this->data['url_structure']['2']))
			$this->data['url_structure']['2'] = $this->data['pageTitle'] = 'News';
		else 
			$this->data['pageTitle'] = ucwords(deslugify($this->data['url_structure']['2'],' '));
			
		
		// Array key "3" is where row id resides
		if(isset($this->data['url_structure']['3']) && is_numeric($this->data['url_structure']['3']))
			$currentId = $this->data['url_structure']['3'];
		else
			$currentId = '';
			
		$scaffold = new Scaffolder(deslugify($this->data['url_structure']['2'],'_'),$currentId);
		$scaffold->iterate();
		
			if(submit()){
				if($scaffold->saveObject()){
					
					// We need to redirect to image cropper or we don't....
					if(isset($_SESSION['crop_images']) && is_array($_SESSION['crop_images'])){
						$_SESSION['crop_redirect'] = WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$this->data['url_structure']['3'].'/';
						$_SESSION['crop_flash'] = '<p class="success">You\'re entry was saved successfully, you can make more edits below.</p>';
						redirect(WEB_ROOT.'admin/cropper/');
					} else {
						$Flash->set('<p class="success">You\'re entry was saved successfully, you can make more edits below.</p>');
						redirect(WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$this->data['url_structure']['3'].'/');
					}
				
				}
			}
			
		$this->data['form'] = $scaffold->display();
		
		$this->loadView('edit_save');
		
	}
	
	
	public function manage()
	{
	 	$db = Database::getDatabase();
	
		$seperators = array('-','+','_',' ');
		
		// Setting default table to "news" this should never happen
		if(!isset($this->data['url_structure']['2']))
			$this->data['url_structure']['2'] = 'News';
		
		$this->data['table_name'] = deslugify($this->data['url_structure']['2'],'_');
		$this->data['pageTitle'] = 'Manage '.ucwords(deslugify($this->data['url_structure']['2'],' '));
		$obj_name = ucfirst($this->data['table_name']);


		// This will determin whether or not to display search
		$this->data['search_module'] = new Konnect_view_information();
		$this->data['search_module']->select(array('search',$this->data['table_name']),array('name','table_name'));
		$search = '';
		
		// If it was determined we should display search, here's where we handle that
		if(!is_null($this->data['search_module']->id)){
			$this->data['search_module']->options = explode(',',$this->data['search_module']->options);
			
			if(submit()){
				$search = '(';
				if($_POST['search_field'] === 'all'){
					foreach($this->data['search_module']->options as $key => $option){
						
						$field_info = new Konnect_field_information();
						$field_info->select(array($this->data['table_name'],$option,'related'),array('table','name','type'));
						
						if(strlen($_POST['search']) < 3){
							$search_arr[] = '`'.$option.'` LIKE \''.addcslashes(mysql_real_escape_string($_POST['search']), '%_').'%\' ';
						} else {
							$search_arr[] = '`'.$option.'` LIKE \'%'.addcslashes(mysql_real_escape_string($_POST['search']), '%_').'%\' ';
						}
					}
				} else {		
						if(strlen($_POST['search']) < 3){
							$search_arr[] = '`'.$_POST['search_field'].'` LIKE \''.addcslashes(mysql_real_escape_string($_POST['search']), '%_').'%\' ';
						} else {
							$search_arr[] = '`'.$_POST['search_field'].'` LIKE \'%'.addcslashes(mysql_real_escape_string($_POST['search']), '%_').'%\' ';
						}
				}
				$search .= implode(' OR ',$search_arr);
				$search .= ')';
			}
		
		}
		
		
		if(isset($this->data['url_structure']['3']) && isset($this->data['url_structure']['4'])){
			if(!empty($search))
				$search = ' AND '.$search;
			$where = 'WHERE `'.$this->data['url_structure']['3'].'`="'.$this->data['url_structure']['4'].'"'.$search;
		}else{
			if(!empty($search))
				$where = 'WHERE '.$search;
		 	else
				$where = '';
		}
		
		// THIS BUILDS THE NEXT AND PREVIOUS BUTTONS

			$page = isset($_GET['page']) ? $_GET['page'] : '1';
			$num = $db->numRows('SELECT * FROM '.$this->data['table_name'].' '.$where);

			$this->data['pager'] = new Pager($page,$num);
			$this->data['pager']->perPage = '10';
			$this->data['pager']->seperator = '';
			$start = ($this->data['pager']->cur > 1) ? ($this->data['pager']->cur - 1) * $this->data['pager']->perPage : '0';

		//////
		
		
		// This is filling $this->data['columns] with current object sets
		$get_table_info = new $obj_name();
		$this->data['entries'] = DBObject::glob($obj_name,'SELECT * FROM `'.$this->data['table_name'].'` '.$where.' ORDER BY '.$get_table_info->idColumnName.' DESC LIMIT '.$start.','.$this->data['pager']->perPage);
		$this->data['columns'] = new $obj_name();
		$this->data['columns'] = $this->data['columns']->getCols();
		
			// Collecting view information for each column type
			foreach($this->data['columns'] as $column => $column_value):
				$field_info = new Konnect_view_information();
				$field_info->select(array($column,$this->data['table_name']),array('name','table_name'));
				
				
				// Check if it's a related field type in the forms table... if so use that info
				$field_form_info = new Konnect_field_information();
				$field_form_info->select(array($this->data['table_name'],$column,'related'),array('table_name','name','type'));
				if(strlen($field_form_info->options) > 1)
					$field_info = $field_form_info;
					
				
				// Check if it's a related field type in the forms table... if so use that info
				$field_form_info = new Konnect_field_information();
				$field_form_info->select(array($this->data['table_name'],$column,'related_dependent'),array('table_name','name','type'));
				if(strlen($field_form_info->options) > 1)
					$field_info = $field_form_info;
					
				
					if(!is_null($field_info->type))
						$this->data['field_info'][$column] = $field_info;
			endforeach;	
		
		// This will determin whether or not to display the "ADD ENTRY" button
		$this->data['show_add_entry'] = new Konnect_view_information();
		$this->data['show_add_entry']->select(array('add entry',$this->data['table_name']),array('name','table_name'));
		
		$this->loadView('manage');
		
	}
	
	public function confirm()
	{
		$this->loadView('_confirm');
		
	}
	
	public function cropper()
	{
		global $Flash;
		
		$this->data['pageTitle'] = 'Crop Your Images';
		
		$this->data['entries'] = isset($_SESSION['crop_images']) ? $_SESSION['crop_images'] : array();
		
			if(submit()){

				foreach($this->data['entries'] as $entryid => $entry){
					
					$gd = new GD();
					if($gd->loadFile('./files/uploads/original/'.$entry)){
						if($gd->width > 950){
							$_POST[$entryid.'_w'] = $_POST[$entryid.'_w'] * 2;
							$_POST[$entryid.'_h'] = $_POST[$entryid.'_h'] * 2;
						}
						$gd->crop($_POST[$entryid.'_x'],$_POST[$entryid.'_y'],$_POST[$entryid.'_w'],$_POST[$entryid.'_h']);
						
							// Delete originals so we don't have to replace them, sometimes that causes issues
							@unlink('./files/uploads/original/'.$entry);
								@unlink('./files/uploads/large/'.$entry);
								@unlink('./files/uploads/medium/'.$entry);
								@unlink('./files/uploads/small/'.$entry);
								@unlink('./files/uploads/cropped/'.$entry);
						
						$gd->saveAs('./files/uploads/original/'.$entry);
						$gd->scaleSafe('700','700');
						$gd->saveAs('./files/uploads/large/'.$entry);
						$gd->scaleSafe('300','300');
						$gd->saveAs('./files/uploads/medium/'.$entry);
						$gd->cropCentered('200','200');
						$gd->saveAs('./files/uploads/cropped/'.$entry);
						$gd->loadFile('./files/uploads/original/'.$entry);
						$gd->scaleSafe('150','150');
						$gd->saveAs('./files/uploads/small/'.$entry);
					}
				}
				
				if(!isset($_SESSION['crop_redirect']))
					$_SESSION['crop_redirect'] = WEB_ROOT.'admin/';
				if(isset($_SESSION['crop_flash']))
					$Flash->set($_SESSION['crop_flash']);
					
				$crop_redirect = $_SESSION['crop_redirect'];
				unset($_SESSION['crop_images'],$_SESSION['crop_redirect']);
				
				redirect($crop_redirect);
			
			}
		
		$this->data['gd'] = new GD();
		$this->loadView('cropper');
	}
	
	public function databaseobjects()
	{
	 	$db = Database::getDatabase();
	
		$this->data['pageTitle'] = 'Your Database Objects';
	
		$out = '';
		$arrTables = array();
		$result = $db->query('SHOW TABLES');
		
		
		while($row = mysql_fetch_array($result)){
			if(!class_exists(ucfirst($row[0]),false))
				$arrTables[] = $row[0];
		}
		
		if(!empty($arrTables)){
			foreach($arrTables as $table)
			{
				$table = trim($table);
				$uctable = ucfirst($table);

				$arrFields = array();
				$result = $db->query('SHOW FIELDS FROM '.$table);
				while($row = mysql_fetch_array($result, MYSQL_ASSOC))
				{
					if(!isset($id_field))
						$id_field = current($row);
					else
						$arrFields[] = current($row);
				}
				$fields = '\'' . implode('\', \'', $arrFields) . '\'';
			
				if(!class_exists($uctable,false)){
				
				$out .= 'class '.$uctable.' extends DBObject'."\n";
				$out .= '{'."\n";
				$out .= '	function __construct($id = "")'."\n";
				$out .= '	{'."\n";
				$out .= '		parent::__construct(\''.$table.'\', \''.$id_field.'\', array('.$fields.'), $id);'."\n";
				$out .= '	}'."\n";
				$out .= '}'."\n";
				$out .= "\n\n";
			
				}
	
				unset($id_field);
			}
		} else {
			$out = 'Your database objects seem to be up to date.';
		}
		
		$this->data['object_out'] = $out;
		
		$this->loadView('databaseobjects');
		
	}
	
}