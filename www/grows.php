<?php
require 'libs/Smarty.class.php';

$smarty = new Smarty;
$db = new SQLite3('/opt/hydrofeeder/hydrofeeder.sqlite');

$func=$_GET['func'];
switch($func)
{
	case "add":
		$results = $db->query("SELECT id, name FROM pump");
		$PumpArray = array();
		$count = 0;
		while($pump = $results->fetchArray()){
			$PumpArray[$count]['id'] = $pump['id'];
			$PumpArray[$count]['name'] = $pump['name'];
			$count++;
		}
		
		$datetime = gmdate('c');
		$local_datetime = new DateTime($datetime, new DateTimeZone('UTC'));
		$local_datetime->setTimeZone(new DateTimeZone('America/New_York'));

		$growinfo['name'] = "";
		$growinfo['description'] = "";
		$growinfo['created'] = $datetime;
		$growinfo['created_local'] = $local_datetime->format('Y-m-d g:i A');
		$growinfo['enabled'] = 1;
		$growinfo['hidden'] = 0;
		
		$smarty->assign('func', $func);
		$smarty->assign('growinfo', $growinfo);
		$smarty->assign('pumps', $PumpArray);
		$smarty->display('grows_add.tpl');

		break;
	case "edit":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		
		$stmt = $db->prepare('SELECT id, name, description, created, enabled FROM grow WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$growinfo = array();
		while($grow = $results->fetchArray()){
			$local_datetime = new DateTime($grow[created], new DateTimeZone('UTC'));
			$local_datetime->setTimeZone(new DateTimeZone('America/New_York'));
			
			$growinfo['id'] = $grow['id'];
			$growinfo['name'] = $grow['name'];
			$growinfo['description'] = $grow['description'];
			$growinfo['created'] = $grow['created'];
			$growinfo['created_local'] = $local_datetime->format('Y-m-d g:i A');
			$growinfo['enabled'] = $grow['enabled'];
		}
		
		$stmt = $db->prepare('SELECT pump_id, addml, startdate, enddate, enabled FROM grow_pump_sched WHERE grow_id=:grow_id');
		$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$SchedArray = array();
		$count = 0;
		while($sched = $results->fetchArray()){
			$SchedArray[$count]['pump_id'] = $sched['pump_id'];
			$SchedArray[$count]['addml'] = $sched['addml'];
			$SchedArray[$count]['startdate'] = $sched['startdate'];
			$SchedArray[$count]['enddate'] = $sched['enddate'];
			$SchedArray[$count]['enabled'] = $sched['enabled'];
			$count++;
		}
		
		$stmt = $db->prepare('SELECT note, datestamp FROM grow_notes WHERE grow_id=:grow_id');
		$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$NoteArray = array();
		$count = 0;
		while($note = $results->fetchArray()){
			$NoteArray[$count]['note'] = $note['note'];
			$NoteArray[$count]['datestamp'] = $note['datestamp'];
			$count++;
		}
		
		$results = $db->query("SELECT id, name FROM pump");
		$PumpArray = array();
		$count = 0;
		while($pump = $results->fetchArray()){
			$PumpArray[$count]['id'] = $pump['id'];
			$PumpArray[$count]['name'] = $pump['name'];
			$count++;
		}

		$smarty->assign('func', $func);
		$smarty->assign('growinfo', $growinfo);
		$smarty->assign('pumps', $PumpArray);
		$smarty->assign('SchedArray', $SchedArray);
		$smarty->assign('NoteArray', $NoteArray);
		$smarty->display('grows_add.tpl');	

		break;
	case "insert":
		$name = $_REQUEST['name'];
		$description = $_REQUEST['description'];
		$created = $_REQUEST['created'];
		$enabled = (int)($_REQUEST['enabled'] ? $_REQUEST['enabled']: 0);
		$hidden = (int)($_REQUEST['hidden'] ? $_REQUEST['hidden']: 0);
		
		$pumpsched = $_REQUEST['pumpsched'];
		$notes = $_REQUEST['notes'];
		
		$stmt = $db->prepare('INSERT INTO grow (name, description, created, enabled, hidden) VALUES (:name, :description, :created, :enabled, :hidden)');
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':description', $description, SQLITE3_TEXT);
		$stmt->bindValue(':created', $created, SQLITE3_TEXT);
		$stmt->bindValue(':enabled', $enabled, SQLITE3_INTEGER);
		$stmt->bindValue(':hidden', $hidden, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$grow_id = $db->lastInsertRowID();
		
		$count = 0;
		foreach ($pumpsched[id] as $row) {
			$pump_id = $pumpsched[id][$count];
			$pump_startdate = $pumpsched[startdate][$count];
			$pump_enddate = $pumpsched[enddate][$count];
			$pump_addml = $pumpsched[addml][$count];
			$pump_enabled = $pumpsched[enabled][$count];

			$stmt = $db->prepare('INSERT INTO grow_pump_sched (grow_id, pump_id, addml, startdate, enddate, enabled) VALUES (:grow_id, :pump_id, :addml, :startdate, :enddate, :enabled)');
			$stmt->bindValue(':grow_id', $grow_id, SQLITE3_INTEGER);
			$stmt->bindValue(':pump_id', $pump_id, SQLITE3_INTEGER);
			$stmt->bindValue(':addml', $pump_addml, SQLITE3_INTEGER);
			$stmt->bindValue(':startdate', $pump_startdate, SQLITE3_TEXT);
			$stmt->bindValue(':enddate', $pump_enddate, SQLITE3_TEXT);
			$stmt->bindValue(':enabled', $pump_enabled, SQLITE3_INTEGER);
			$results = $stmt->execute();
			
			$count++;
		}
		
		$count = 0;
		foreach ($notes[note] as $row) {
			$note = $notes[note][$count];
			$note_date = $notes[datestamp][$count];

			$stmt = $db->prepare('INSERT INTO grow_notes (grow_id, note, datestamp) VALUES (:grow_id, :note, :datestamp)');
			$stmt->bindValue(':grow_id', $grow_id, SQLITE3_INTEGER);
			$stmt->bindValue(':note', $note, SQLITE3_TEXT);
			$stmt->bindValue(':datestamp', $note_date, SQLITE3_TEXT);
			$results = $stmt->execute();
			
			$count++;
		}
		
		header("Location: grows.php");

		break;
	case "update":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$name = $_REQUEST['name'];
		$description = $_REQUEST['description'];
		$created = $_REQUEST['created'];
		$enabled = (int)($_REQUEST['enabled'] ? $_REQUEST['enabled']: 0);
		$hidden = (int)($_REQUEST['hidden'] ? $_REQUEST['hidden']: 0);
		$pumpsched = $_REQUEST['pumpsched'];
		$notes = $_REQUEST['notes'];
		
		$stmt = $db->prepare('UPDATE grow SET name=:name, description=:description, created=:created, enabled=:enabled, hidden=:hidden WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$stmt->bindValue(':name', $name, SQLITE3_TEXT);
		$stmt->bindValue(':description', $description, SQLITE3_TEXT);
		$stmt->bindValue(':created', $created, SQLITE3_TEXT);
		$stmt->bindValue(':enabled', $enabled, SQLITE3_INTEGER);
		$stmt->bindValue(':hidden', $hidden, SQLITE3_INTEGER);
		$results = $stmt->execute();
		
		$stmt = $db->prepare('DELETE FROM grow_pump_sched WHERE grow_id=:grow_id');
		$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$count = 0;
		foreach ($pumpsched[id] as $row) {
			$pump_id = $pumpsched[id][$count];
			$pump_startdate = $pumpsched[startdate][$count];
			$pump_enddate = $pumpsched[enddate][$count];
			$pump_addml = $pumpsched[addml][$count];
			$pump_enabled = $pumpsched[enabled][$count];

			$stmt = $db->prepare('INSERT INTO grow_pump_sched (grow_id, pump_id, addml, startdate, enddate, enabled) VALUES (:grow_id, :pump_id, :addml, :startdate, :enddate, :enabled)');
			$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
			$stmt->bindValue(':pump_id', $pump_id, SQLITE3_INTEGER);
			$stmt->bindValue(':addml', $pump_addml, SQLITE3_INTEGER);
			$stmt->bindValue(':startdate', $pump_startdate, SQLITE3_TEXT);
			$stmt->bindValue(':enddate', $pump_enddate, SQLITE3_TEXT);
			$stmt->bindValue(':enabled', $pump_enabled, SQLITE3_INTEGER);
			$results = $stmt->execute();
			
			$count++;
		}
		
		$stmt = $db->prepare('DELETE FROM grow_notes WHERE grow_id=:grow_id');
		$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		$count = 0;

		foreach ($notes[note] as $row) {
			$note = $notes[note][$count];
			$note_date = $notes[datestamp][$count];

			$stmt = $db->prepare('INSERT INTO grow_notes (grow_id, note, datestamp) VALUES (:grow_id, :note, :datestamp)');
			$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
			$stmt->bindValue(':note', $note, SQLITE3_TEXT);
			$stmt->bindValue(':datestamp', $note_date, SQLITE3_TEXT);
			$results = $stmt->execute();
			
			$count++;
		}
		
		header("Location: grows.php");

		break;
	case "delete":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$stmt = $db->prepare('DELETE FROM grow WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		
		$stmt = $db->prepare('DELETE FROM grow_pump_sched WHERE grow_id=:grow_id');
		$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		
		$stmt = $db->prepare('DELETE FROM grow_notes WHERE grow_id=:grow_id');
		$stmt->bindValue(':grow_id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();
		header("Location: grows.php");
		break;
	case "hide":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$stmt = $db->prepare('UPDATE grow SET hidden=1 WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();

		header("Location: grows.php");
		break;
	case "unhide":
		$id = (int)($_REQUEST['id'] ? $_REQUEST['id']: 0);
		$stmt = $db->prepare('UPDATE grow SET hidden=0 WHERE id=:id');
		$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
		$results = $stmt->execute();

		header("Location: grows.php?func=hidden");
		break;
	case "hidden":
		$results = $db->query("SELECT id, name, description, created, enabled, hidden FROM grow WHERE hidden=1");
		$GrowArray = array();
		$count = 0;
		while($pump = $results->fetchArray()){
			$local_datetime = new DateTime($pump['created'], new DateTimeZone('America/New_York'));
			
			$GrowArray[$count]['id'] = $pump['id'];
			$GrowArray[$count]['name'] = $pump['name'];
			$GrowArray[$count]['description'] = $pump['description'];
			$GrowArray[$count]['created_local'] = $local_datetime->format('Y-m-d g:i A');
			$GrowArray[$count]['enabled'] = $pump['enabled'];
			$GrowArray[$count]['hidden'] = $pump['hidden'];
			$count++;
		}

		$smarty->assign('func', $func);
		$smarty->assign('grows', $GrowArray);
		$smarty->display('grows.tpl');

		break;
	default:
		$results = $db->query("SELECT id, name, description, created, enabled, hidden FROM grow WHERE hidden=0");
		$GrowArray = array();
		$count = 0;
		while($grow = $results->fetchArray()){
			$local_datetime = new DateTime($grow[created], new DateTimeZone('UTC'));
			$local_datetime->setTimeZone(new DateTimeZone('America/New_York'));
			
			$GrowArray[$count]['id'] = $grow['id'];
			$GrowArray[$count]['name'] = $grow['name'];
			$GrowArray[$count]['description'] = $grow['description'];
			$GrowArray[$count]['created_local'] = $local_datetime->format('Y-m-d g:i A');
			$GrowArray[$count]['enabled'] = $grow['enabled'];
			$GrowArray[$count]['hidden'] = $grow['hidden'];
			$count++;
		}

		$smarty->assign('func', $func);
		$smarty->assign('grows', $GrowArray);
		$smarty->display('grows.tpl');

		break;
}