<?php

// Determine our absolute document root, includes trailing slash
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

include DOC_ROOT . 'tests/autoload.php';

// START TEST ///////

$form = new Forms;

/*
	Info Array Example:
	type (field type: text, textarea, etc)
	display_name
	class
	layout (would replace "this->row_wrapper")
	options (this is an array with extra shizzle...)
		size
		col
		rows
		etc....
*/

$info = array(
			'type' => 'hidden',
			'display_name' => 'Sample Input',
			'value' => 'Default Value!',
			'options' => array(
							'size' => '3'
						)
		);

$form->add_field('sample_input',$info);

$info2 = array(
			'type' => 'text',
			'display_name' => 'Sample Two',
			'value' => 'Default Value!',
			'layout' => "<p><label for=\"%id%\">%name%:</label>%field%</p>\n",
			'options' => array(
							'size' => '3'
						)
		);

$form->add_field('sample two',$info2);



$form->iterate();
echo $form->display();