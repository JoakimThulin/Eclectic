<?php
session_start();
include "general.php";
include "base.php";
if($_SESSION['loggedin']) {player_update();} else {no_access();}

//******************************************************************
function player_update(){
//Uppdaterad 2023-04-18 av Joakim [joakim.thulin@outlook.com]

	$playername = mb_convert_encoding($_POST["playername"], 'ISO-8859-1', 'UTF-8');
	$playerpw = $_POST["playerpw"];
	$hcp = $_POST["hcp"];
	$playerid = $_SESSION['playerid'];
	//$playername = $playername;

	try {
		$db = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
		$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		if(!player_exists($db, $playername, $playerid)){
			//vi får inte byta namn till ett namn som redan existerar
			$stmt = $db->prepare("UPDATE ecl_players SET player =:p, hcp=:h, pw=:l WHERE id=:i");
			$stmt->bindParam(':p', $playername, PDO::PARAM_STR);
			$stmt->bindParam(':h', $hcp, PDO::PARAM_INT);
			$stmt->bindParam(':l', $playerpw, PDO::PARAM_STR);
			$stmt->bindParam(':i', $playerid, PDO::PARAM_INT);
			$stmt->execute();
		}
		$db = null;
		die(header("Location: players.php"));
	} catch (PDOException $e) {
		print "Det sket sig: " . $e->getMessage() . "<br/>";
		die();
	}

}
//*******************************************************************************************************************
function player_exists($db, $playername, $playerid){
//Uppdaterad 2013-11-27 av Joakim [joakim.thulin@outlook.com]
//Här kollar vi om det finns några andra spelare som redan har det önskade namnet

	$stmt = $db->prepare("SELECT count(id) FROM ecl_players WHERE (player = :p) AND (id != :i)");
	$stmt->bindParam(':p', $playername, PDO::PARAM_STR);
	$stmt->bindParam(':i', $playerid, PDO::PARAM_INT);
	$stmt->execute();
	$r = $stmt->fetch(PDO::FETCH_NUM);
	$hits = $r[0];
	//$sql = sprintf("SELECT count(id) FROM ecl_players WHERE (player='%s') AND (id!=%s)", $playername, $playerid);
	//$hits = $db->query($sql)->fetchColumn();
	if($hits > 0){$ret = true;} else {$ret = false;}
	return $ret;

}
//*******************************************************************************************************************
?>
