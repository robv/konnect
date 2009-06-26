<?php

// Determine our absolute document root, includes trailing slash
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

include DOC_ROOT . 'tests/autoload.php';

// START TEST ///////

include DOC_ROOT . 'core/class.database.php';

$config['host'] = 'localhost:/Applications/MAMP/tmp/mysql/mysql.sock';
$config['name'] = 'konnect';
$config['username'] = 'root';
$config['password'] = 'rootpassword';
$config['die'] = true;

var_dump(Database::get_instance($config)->query('SELECT * FROM blog_entries')->get_rows());

echo "\n\n\n";

echo Database::get_instance()->escape('shit and shit + / shit""');

echo "\n\n\n";

class Blog_Entries extends Db_Object
{
    public function __construct($id = null)
    {
        parent::__construct('blog_entries', array('author', 'title', 'content'), $id);
    }
}

$entry = new Blog_Entries(array('id' => 2));
var_dump($entry);