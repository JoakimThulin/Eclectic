<?php
session_start();
include "general.php";
include "base.php";
if($_SESSION['loggedin']) {player_update();} else {no_access();}

//******************************************************************
function player_update(){
//Uppdaterad 2023-05-02 av Joakim [joakim.thulin@outlook.com]

	date_default_timezone_set('Europe/Stockholm');
	$hole = $_POST["hole"];
	$score = $_POST["score"];
	$playmonth = $_POST["playmonth"];
	$playday = $_POST["playday"];
	$playyear = date("Y") + 0;
	$playerid = $_SESSION['playerid'];
	$hcp = 0;
	
	If(checkdate($playmonth, $playday, $playyear)){
		$playdate = $playyear . "-" . $playmonth . "-" . $playday;
	}
	else{
		echo "Du angav ett felaktigt datum, backa och försök igen...";
		return;
	}

	try {
		$db = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
		$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		//Kolla om det redan finns en score registrerad på hålet
		$stmt = $db->prepare("SELECT count(player) FROM ecl_scores WHERE player =:pl AND hole =:ho AND season =:se");
		$stmt->bindParam(':pl', $playerid, PDO::PARAM_INT);
		$stmt->bindParam(':ho', $hole, PDO::PARAM_INT);
		$stmt->bindParam(':se', $playyear, PDO::PARAM_INT);
		$stmt->execute();
		$r = $stmt->fetch(PDO::FETCH_NUM);
		$count = $r[0];//noll eller ett

		$stmt = $db->prepare("SELECT hcp FROM ecl_players WHERE id =:pl LIMIT 0,1");
		$stmt->bindParam(':pl', $playerid, PDO::PARAM_INT);
		$stmt->execute();
		$hcp = $stmt->fetchColumn();

		If($count == 0){
			//finns ingen tidigare score registrerad på hålet denna säsong, alltså blir det en INSERT
			$stmt = $db->prepare("INSERT INTO ecl_scores (score, hcp, play_date, player, hole, season) VALUES (:sc, :hc, :da, :pl, :ho, :se)");
		}
		else{
			//finns en score registrerad på hålet denna säsong, vill uppdatera denna post, alltså blir det en UPDATE
			$stmt = $db->prepare("UPDATE ecl_scores SET score =:sc, hcp=:hc, play_date=:da WHERE player =:pl AND hole =:ho AND season =:se");
		}
		$stmt->bindParam(':sc', $score, PDO::PARAM_INT);
		$stmt->bindParam(':hc', $hcp, PDO::PARAM_INT);
		$stmt->bindParam(':da', $playdate, PDO::PARAM_STR);
		$stmt->bindParam(':pl', $playerid, PDO::PARAM_INT);
		$stmt->bindParam(':ho', $hole, PDO::PARAM_INT);
		$stmt->bindParam(':se', $playyear, PDO::PARAM_INT);
		$stmt->execute();

		$db = null;
		die(header("Location: index.php"));
		
	} catch (PDOException $e) {
		print "Det sket sig: " . $e->getMessage() . "<br/>";
		die();
	}

}
//********************************************************************************************************
?>
