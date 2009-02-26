<?PHP

	// These are default objects that should be loaded, just to keep things extra tidy
	require DOC_ROOT . '/core/defaultobjects.inc.php';

    // Stick your DBOjbect subclasses in here (to help keep things tidy).
	class Sites extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('sites', 'id', array('user_id', 'type_id', 'name'), $id);
		}
	}


	class Types extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('types', 'id', array('user_id', 'name'), $id);
		}
	}


