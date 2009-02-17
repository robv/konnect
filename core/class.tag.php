<?PHP
    class Tag extends DBObject
    {
        public function __construct($id = '')
        {
            parent::__construct('tags', 'id', array('name'), '');
            $this->select($id, 'name');
            if($this->id == '')
            {
                $this->name = $id;
                $this->insert();
            }
        }
    }