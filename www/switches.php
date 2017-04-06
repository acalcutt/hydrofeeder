<?php
require 'libs/Smarty.class.php';

$smarty = new Smarty;
$db = new SQLite3('/opt/hydrofeeder/hydrofeeder.sqlite');

$func=$_GET['func'];
switch($func)
{
	case "add":
		$switchinfo['name'] = "";
		$switchinfo['gpio'] = "";
		$switchinfo['on_value'] = 0;
		
		$smarty->assign('func', $func);
		$smarty->assign('switchinfo', $switchinfo);
		$smarty->display('switches_add.tpl');

		break;
	case "edit":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		
		$stmt = $db->prepare('SELECT id, name, gpio, on_value FROM switch WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$switchinfo = $results->fetchArray();		
		
		$smarty->assign('func', $func);
		$smarty->assign('switchinfo', $switchinfo);
		$smarty->display('switches_add.tpl');	
		break;
	case "insert":
		$name = $_REQUEST['name'];
		$gpio = (int)($_REQUEST['gpio'] ? $_REQUEST['gpio']: 0);
		$on_value = (int)($_REQUEST['on_value'] ? $_REQUEST['on_value']: 0);

		$stmt = $db->prepare('INSERT INTO switch (name, gpio, on_value) VALUES (:name, :gpio, :on_value)');
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':gpio', $gpio, SQLITE3_INTEGER);
		$stmt->bindValue(':on_value', $on_value, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$grow_id = $db->lastInsertRowID();
		
		header("Location: switches.php");
		break;
	case "update":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$name = $_REQUEST['name'];
		$gpio = (int)($_REQUEST['gpio'] ? $_REQUEST['gpio']: 0);
		$on_value = (int)($_REQUEST['on_value'] ? $_REQUEST['on_value']: 0);

		$stmt = $db->prepare('UPDATE switch SET name=:name, gpio=:gpio, on_value=:on_value WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':gpio', $gpio, SQLITE3_INTEGER);
		$stmt->bindValue(':on_value', $on_value, SQLITE3_INTEGER);
		$results = $stmt->execute();
		
		header("Location: switches.php");
		break;
	case "delete":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$stmt = $db->prepare('DELETE FROM switch WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		
		header("Location: switches.php");
		break;
	case "actions":
		$results = $db->query("SELECT id, name, function FROM switch_actions");
		$switchArray = array();
		$count = 0;
		while($switch = $results->fetchArray()){
			$switchArray[$count]['id'] = $switch['id'];
			$switchArray[$count]['name'] = $switch['name'];
			$switchArray[$count]['function'] = $switch['function'];
			$count++;
		}

		$smarty->assign('func', $func);
		$smarty->assign('switches', $switchArray);
		$smarty->display('switches_actions.tpl');
		break;
	default:
		$results = $db->query("SELECT id, name, gpio, on_value FROM switch");
		$switchArray = array();
		$count = 0;
		while($switch = $results->fetchArray()){
			$switchArray[$count]['id'] = $switch['id'];
			$switchArray[$count]['name'] = $switch['name'];
			$switchArray[$count]['gpio'] = $switch['gpio'];
			$switchArray[$count]['on_value'] = $switch['on_value'];
			$count++;
		}

		$smarty->assign('func', $func);
		$smarty->assign('switches', $switchArray);
		$smarty->display('switches.tpl');

		break;
}