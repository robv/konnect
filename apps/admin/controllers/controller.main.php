<?php

class Main_Controller extends Controller {
	
	function __construct($data = '')
	{		
		$this->default_method = 'dashboard';

		// Router uri = app/controller/method
		parent::__construct($data);
	}
	
	public function dashboard()
	{
		// Kick out user if already logged in
		if (!Auth::get_auth()->logged_in()) {
			Flash::set('<p class="flash warning">You must be logged in to access admin.</p>');
			Core_Helpers::redirect(WEB_ROOT . 'login/');
		}
		
		$this->load_template('dashboard');
	}
	
	public function models()
	{
		$db = Database::get_db();
		
		$out = '';
		$arrTables = array();
		$db->query('SHOW TABLES');
		
		while($row = mysql_fetch_array($db->result)){
			if(!class_exists(ucfirst($row[0]),false))
				$arrTables[] = $row[0];
		}
		
		if(!empty($arrTables)){
			foreach($arrTables as $table)
			{
				$table = trim($table);
				$uctable = String::uc_slug($table,'_');

				$arrFields = array();
				$db->query('SHOW FIELDS FROM '.$table);
				while($row = mysql_fetch_array($db->result, MYSQL_ASSOC))
				{
					if(!isset($id_field))
						$id_field = current($row);
					else
						$arrFields[] = current($row);
				}
				$fields = '\'' . implode('\', \'', $arrFields) . '\'';
			
				if(!class_exists($uctable,false)){
				
				$out .= 'class '.$uctable.' extends Db_Object {'."\n\n";
				$out .= '	function __construct($id = \'\')'."\n";
				$out .= '	{'."\n";
				$out .= '		parent::__construct(\''.$table.'\', \''.$id_field.'\', array('.$fields.'), $id);'."\n";
				$out .= '	}'."\n\n";
				$out .= '}'."\n";
				$out .= "\n";
			
				}
	
				unset($id_field);
			}
		} else {
			$out = 'Your database objects seem to be up to date.';
		}
		
		$this->data['object_out'] = $out;
		
		$this->load_template('models');
	}
	
}