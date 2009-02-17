<?PHP

class Install_controller extends Controller {

	public function index()
	{
		global $Flash;
	 	$db = Database::getDatabase();
		
		$this->data['config'] = Config::getConfig();
		
		if(isset($_POST['create'])){
			
			// Create tables
			$sql = file_get_contents('templates/install/db.sql');

			// Do this to split up creations to one per query.
			$queries = explode('#',$sql);

				foreach($queries as $query)
					$db->query($query);

			$Flash->set('<p class="info">Step two: Create Administrator Account.</p>');
			redirect(WEB_ROOT.'admin/add/users/');
		}
		
		$this->loadView('install/create_database');
	}
	
} 