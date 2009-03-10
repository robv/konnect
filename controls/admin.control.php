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
			$this->data['dash_log'] = DBObject::glob('Dashboard_log','ORDER BY timestamp DESC LIMIT 0,20');
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
		global $Flash;
		
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
					
					if(isset($_POST['next']) && $_POST['next'] === 'add'){
						$Flash->set('<p class="success">You\'re entry was added successfully, you can add another below or <a href="'.WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.mysql_insert_id().'/">click here to review / edit that entry</a>.</p>');
						redirect(WEB_ROOT.'admin/add/'.$this->data['url_structure']['2'].'/');
					}else{
						$Flash->set('<p class="success">You\'re entry was added successfully, you can make edits below.</p>');
						redirect(WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.mysql_insert_id().'/');
					}
				}
			}
			
		$this->setGlobal('form',$scaffold->display());
		
		$this->loadView('admin/edit_save');
		
	}

	public function edit()
	{
		global $Flash;
		
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
					$Flash->set('<p class="success">You\'re entry was saved successfully, you can make more edits below.</p>');
					redirect(WEB_ROOT.'admin/edit/'.$this->data['url_structure']['2'].'/'.$this->data['url_structure']['3'].'/');
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
		
		if(isset($this->data['url_structure']['3']) && isset($this->data['url_structure']['4']))
			$where = 'WHERE `'.$this->data['url_structure']['3'].'`="'.$this->data['url_structure']['4'].'"';
		else
			$where = '';
		 
		
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