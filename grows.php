<?php
require 'libs/Smarty.class.php';

$smarty = new Smarty;
$db = new SQLite3('/opt/hydrofeeder/hydrofeeder.sqlite');

$results = $db->query("SELECT id, enabled, name, startdate FROM grow");
$GrowArray = array();
$count = 0;
while($pump = $results->fetchArray()){
	$GrowArray[$count]['id'] = $pump['id'];
	$GrowArray[$count]['enabled'] = $pump['enabled'];
	$GrowArray[$count]['name'] = $pump['name'];
	$GrowArray[$count]['startdate'] = date("Y-m-d", $pump['startdate']);
	$count++;
}

$smarty->assign('grows', $GrowArray);
$smarty->display('grows.tpl');