<?PHP

class Admin_controller extends Controller {
	
	function __construct($controller='',$data = '')
	{
		global $Auth;
		
		// This first checks to see if users exist
		// If not it assumes you don't want the admin protected
		// If so it ensures the logged in user is an administrator
		if(users_exist()){ $Auth->requireAdmin(WEB_ROOT.'login/'); }
		
		// This is how you set your default controller I should probably think of a better method
		if(empty($controller))
			$controller = 'dashboard';
			
		$data['header_links'] = DBObject::glob('Konnect_links','WHERE authorized_groups LIKE "%'.$Auth->level.'%" OR authorized_groups is NULL OR authorized_groups=""');
		
		parent::__construct($controller,$data);
		
	}

	public function dashboard()
	{
			$this->data['pageTitle'] = 'Your Dashboard';
			$this->loadView('admin/dashboard');
	}
	
	public function delete()
	{		
		global $Flash;
		
			$seperators = array('-','+','_',' ');
			$this->data['table_name'] = strtolower(str_replace($seperators,'_',$this->data['url_structure']['2']));
			$this->data['pageTitle'] = 'Delete '.ucwords(str_replace($seperators,' ',$this->data['url_structure']['2']));
			$obj_name = ucfirst($this->data['table_name']);
			$delete = new $obj_name($this->data['url_structure']['3']);
			$delete->delete();
			$Flash->set('<p class="success">You\'re entry was deleted successfully.</p>');
			redirect(WEB_ROOT.'admin/manage/'.$this->data['url_structure']['2'].'/');
	}

	public function add()
	{
		global $Auth,$Flash;
		
		// If they bypassed cropping this was never unset...
		if(isset($_SESSION['crop_images']))
			unset($_SESSION['crop_images']);
		
		$seperators = array('-','+','_',' ');
		
		if(!isset($this->data['url_structure']['2'])){
			$this->data['url_structure']['2'] = 'Pages';
			$this->data['pageTitle'] = 'Pages';
		}
		else {
			$this->data['pageTitle'] = ucwords(str_replace($seperators,' ',$this->data['url_structure']['2']));
		}
		
		// Array key "3" is where iterations reside
		if(isset($this->data['url_structure']['3']) && is_numeric($this->data['url_structure']['3'])){
			$this->data['iterations'] = $this->data['url_structure']['3'];
		}
		else{
			$this->data['iterations'] = 1;
		}	
		
		$scaffold = new Scaffolder(strtolower(str_replace($seperators,'_',$this->data['url_structure']['2'])),'',$this->data['iterations']);
		$scaffold->iterate();
		
		// Grab user preferences
		$this->data['preference'] = new User_preferences();
		$this->data['preference']->select(array($Auth->id,'next'),array('user','preference'));
		
			if(submit()){
				
				if($scaffold->saveObject()){
					/*
					// TODO: This needs to be refactored and probably incorporated into $scaffold->saveObject()
						$dash_log = new Dashboard_log();
						$dash_log->table = strtolower(str_replace($seperators,'_',$this->data['url_structure']['2']));
						
							TODO: Store mysql_insert_id from saveObject
							TODO: How would this even work for multiple entries?
						
						$dash_log->entry = mysql_insert_id();
						$dash_log->action = 'add';
						$dash_log->insert();
					*/
					
					$saved_object_id = mysql_insert_id();
				
					if(isset($_POST['next']) && $_POST['next'] === 'add'){
						
						// Updated or inserting user preference
						if($this->data['preference']->value !== 'add' && $this->data['preference']->value !== 'edit'){
							$this->data['preference']->preference = 'next';
							$this->data['preference']->value = 'add';
							$this->data['preference']->user = $Auth->id;
							$this->data['preference']->insert();
						} else {
								$this->data['preference']->preference = 'next';
								$this->data['preference']->value = 'add';
								$this->data['preference']->user = $Auth->id;
								$this->data['preference']->update();
						}
						
						// We need to redirect to image cropper or we don't....
						if(isset($_SESSION['crop_images']) && is_array($_SESSION['crop_images'])){
							$_SESSION['crop_redirect'] = WEB_ROOT.'admin/add/'.$this->data['url_structure']['2'].'/';
							$_SESSION['crop_flash'] = '<p class="success">You\'re entry was added successfully, you can add another below or <a href="'.WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$saved_object_id.'/"> click here to review / edit that entry</a>.</p>';
							redirect(WEB_ROOT.'admin/cropper/');
						} else {	
							$Flash->set('<p class="success">You\'re entry was added successfully, you can add another below or <a href="'.WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$saved_object_id.'/"> click here to review / edit that entry</a>.</p>');
							redirect(WEB_ROOT.'admin/add/'.$this->data['url_structure']['2'].'/');
						}
				
					} else {
				
						// Updated or inserting user preference
						if($this->data['preference']->value !== 'add' && $this->data['preference']->value !== 'edit'){
							$this->data['preference']->preference = 'next';
							$this->data['preference']->value = 'edit';
							$this->data['preference']->user = $Auth->id;
							$this->data['preference']->insert();
						} else {
								$this->data['preference']->preference = 'next';
								$this->data['preference']->value = 'edit';
								$this->data['preference']->user = $Auth->id;
								$this->data['preference']->update();
						}
						
						// We need to redirect to image cropper or we don't....
						if(isset($_SESSION['crop_images']) && is_array($_SESSION['crop_images'])){
							$_SESSION['crop_redirect'] = WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$saved_object_id.'/';
							$_SESSION['crop_flash'] = '<p class="success">You\'re entry was added successfully, you can make edits below.</p>';
							redirect(WEB_ROOT.'admin/cropper/');
						} else {
							$Flash->set('<p class="success">You\'re entry was added successfully, you can make edits below.</p>');
							redirect(WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$saved_object_id.'/');
						}
				
					}
				}
			}
			
		$this->setGlobal('form',$scaffold->display());
		
		$this->loadView('admin/edit_save');
		
	}

	public function edit()
	{
		global $Flash;
		
		// If they bypassed cropping this was never unset...
		if(isset($_SESSION['crop_images']))
			unset($_SESSION['crop_images']);
			
		$seperators = array('-','+','_',' ');
		
		if(!isset($this->data['url_structure']['2'])){
			$this->data['url_structure']['2'] = 'Pages';
			$this->data['pageTitle'] = 'Pages';
		}
		else {
			$this->data['pageTitle'] = ucwords(str_replace($seperators,' ',$this->data['url_structure']['2']));
		}
		
		// Array key "3" is where row id resides
		if(isset($this->data['url_structure']['3']) && is_numeric($this->data['url_structure']['3'])){
			$currentId = $this->data['url_structure']['3'];
		}
		else{
			$currentId = '';
		}
			
		$scaffold = new Scaffolder(strtolower(str_replace($seperators,'_',$this->data['url_structure']['2'])),$currentId);
		$scaffold->iterate();
		
			if(submit()){
				if($scaffold->saveObject()){
					/*
					// TODO: This needs to be refactored and probably incorporated into $scaffold->saveObject()
						$dash_log = new Dashboard_log();
						$dash_log->table = strtolower(str_replace($seperators,'_',$this->data['url_structure']['2']));
						
							TODO: Store mysql_insert_id from saveObject
							TODO: How would this even work for multiple entries?
						
						$dash_log->entry = mysql_insert_id();
						$dash_log->action = 'edit';
						$dash_log->insert();
					*/
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
			
		$this->setGlobal('form',$scaffold->display());
		
		$this->loadView('admin/edit_save');
		
	}
	
	
	public function manage()
	{
	 	$db = Database::getDatabase();
	
		$seperators = array('-','+','_',' ');
		
		if(!isset($this->data['url_structure']['2'])){
			$this->data['url_structure']['2'] = 'Pages';
		}
		
		$this->data['table_name'] = strtolower(str_replace($seperators,'_',$this->data['url_structure']['2']));
		$this->data['pageTitle'] = 'Manage '.ucwords(str_replace($seperators,' ',$this->data['url_structure']['2']));
		$obj_name = ucfirst($this->data['table_name']);


		// This will determin whether or not to display search
		$this->data['search_module'] = new Konnect_view_information();
		$this->data['search_module']->select(array('search',$this->data['table_name']),array('name','table_name'));
		$search = '';
		if(!is_null($this->data['search_module']->id)){
			$this->data['search_module']->options = explode(',',$this->data['search_module']->options);
			
			if(submit()){
				$search = '(';
				if($_POST['search_field'] === 'all'){
					foreach($this->data['search_module']->options as $key => $option){
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
		$this->data['entries'] = DBObject::glob($obj_name,$where.' ORDER BY '.$get_table_info->idColumnName.' DESC LIMIT '.$start.','.$this->data['pager']->perPage);
		$this->data['columns'] = new $obj_name();
		$this->data['columns'] = $this->data['columns']->getCols();
		
			// Collecting view information for each column type
			foreach($this->data['columns'] as $column => $column_value):
				$field_info = new Konnect_view_information();
				$field_info->select(array($column,$this->data['table_name']),array('name','table_name'));
					if(!is_null($field_info->type))
						$this->data['field_info'][$column] = $field_info;
			endforeach;	
		
		// This will determin whether or not to display the "ADD ENTRY" button
		$this->data['show_add_entry'] = new Konnect_view_information();
		$this->data['show_add_entry']->select(array('add entry',$this->data['table_name']),array('name','table_name'));
		
		$this->loadView('admin/manage');
		
	}
	
	public function confirm()
	{
		$this->loadView('admin/_confirm');
		
	}
	
	public function cropper()
	{
		global $Flash;
		
		$this->data['pageTitle'] = 'Crop Your Images';
		
		$this->data['entries'] = isset($_SESSION['crop_images']) ? $_SESSION['crop_images'] : array();
		
			if(submit()){

				foreach($this->data['entries'] as $entryid => $entry){
					
					$gd = new GD();
					if($gd->loadFile('./files/uploads/large/'.$entry)){
						$gd->crop($_POST[$entryid.'_x'],$_POST[$entryid.'_y'],$_POST[$entryid.'_w'],$_POST[$entryid.'_h']);
						
							// Delete originals so we don't have to replace them, sometimes that causes issues
							@unlink('./files/uploads/original/'.$entry);
								@unlink('./files/uploads/large/'.$entry);
								@unlink('./files/uploads/medium/'.$entry);
								@unlink('./files/uploads/small/'.$entry);
						
						$gd->saveAs('./files/uploads/original/'.$entry);
						$gd->scaleSafe('700','700');
						$gd->saveAs('./files/uploads/large/'.$entry);
						$gd->scaleSafe('200','200');
						$gd->saveAs('./files/uploads/medium/'.$entry);
						$gd->scaleSafe('100','100');
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
		
		$this->loadView('admin/cropper');
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
		
		$this->loadView('admin/databaseobjects');
		
	}
	
}