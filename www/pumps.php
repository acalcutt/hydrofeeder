<?php
require 'libs/Smarty.class.php';

$smarty = new Smarty;
$db = new SQLite3('/opt/hydrofeeder/hydrofeeder.sqlite');

$func=$_GET['func'];
switch($func)
{
	case "add":
		$pumpinfo['name'] = "";
		$pumpinfo['gpio'] = "";
		$pumpinfo['ml_per_minute'] = 100;;
		$pumpinfo['on_value'] = 0;
		$pumpinfo['off_value'] = 1;
		
		$smarty->assign('func', $func);
		$smarty->assign('pumpinfo', $pumpinfo);
		$smarty->display('pumps_add.tpl');

		break;
	case "edit":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		
		$stmt = $db->prepare('SELECT id, name, gpio, ml_per_minute, on_value, off_value FROM pump WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$pumpinfo = $results->fetchArray();		
		
		$smarty->assign('func', $func);
		$smarty->assign('pumpinfo', $pumpinfo);
		$smarty->display('pumps_add.tpl');	
		break;
	case "insert":
		$name = $_REQUEST['name'];
		$gpio = (int)($_REQUEST['gpio'] ? $_REQUEST['gpio']: 0);
		$ml_per_minute = $_REQUEST['ml_per_minute'];
		$on_value = (int)($_REQUEST['on_value'] ? $_REQUEST['on_value']: 0);
		$off_value = (int)($_REQUEST['off_value'] ? $_REQUEST['off_value']: 0);

		$stmt = $db->prepare('INSERT INTO pump (name, gpio, ml_per_minute, on_value, off_value) VALUES (:name, :gpio, :ml_per_minute, :on_value, :off_value)');
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':gpio', $gpio, SQLITE3_INTEGER);
		$stmt->bindValue(':ml_per_minute', $ml_per_minute, SQLITE3_TEXT);
		$stmt->bindValue(':on_value', $on_value, SQLITE3_INTEGER);
		$stmt->bindValue(':off_value', $off_value, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$grow_id = $db->lastInsertRowID();
		
		header("Location: pumps.php");
		break;
	case "update":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$name = $_REQUEST['name'];
		$gpio = (int)($_REQUEST['gpio'] ? $_REQUEST['gpio']: 0);
		$ml_per_minute = $_REQUEST['ml_per_minute'];
		$on_value = (int)($_REQUEST['on_value'] ? $_REQUEST['on_value']: 0);
		$off_value = (int)($_REQUEST['off_value'] ? $_REQUEST['off_value']: 0);

		$stmt = $db->prepare('UPDATE pump SET name=:name, gpio=:gpio, ml_per_minute=:ml_per_minute, on_value=:on_value, off_value=:off_value WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':gpio', $gpio, SQLITE3_INTEGER);
		$stmt->bindValue(':ml_per_minute', $ml_per_minute, SQLITE3_TEXT);
		$stmt->bindValue(':on_value', $on_value, SQLITE3_INTEGER);
		$stmt->bindValue(':off_value', $off_value, SQLITE3_INTEGER);
		$results = $stmt->execute();
		
		header("Location: pumps.php");
		break;
	case "delete":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$stmt = $db->prepare('DELETE FROM pump WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		
		header("Location: pumps.php");
		break;
	default:
		$results = $db->query("SELECT id, name, gpio, ml_per_minute, on_value, off_value FROM pump");
		$PumpArray = array();
		$count = 0;
		while($pump = $results->fetchArray()){
			$PumpArray[$count]['id'] = $pump['id'];
			$PumpArray[$count]['name'] = $pump['name'];
			$PumpArray[$count]['gpio'] = $pump['gpio'];
			$PumpArray[$count]['ml_per_minute'] = $pump['ml_per_minute'];
			$PumpArray[$count]['on_value'] = $pump['on_value'];
			$PumpArray[$count]['off_value'] = $pump['off_value'];
			$count++;
		}

		$smarty->assign('func', $func);
		$smarty->assign('pumps', $PumpArray);
		$smarty->display('pumps.tpl');

		break;
}