<?php
require 'libs/Smarty.class.php';

$smarty = new Smarty;
$db = new SQLite3('/opt/hydrofeeder/hydrofeeder.sqlite');

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

$smarty->assign('pumps', $PumpArray);
$smarty->display('pumps.tpl');