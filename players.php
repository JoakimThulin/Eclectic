<?php
session_start();
include "base.php";
list_players();

//******************************************************************
function list_players(){
//Uppdaterad 2023-05-02 av Joakim [joakim.thulin@outlook.com]

	try {
		$db = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
		$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$playerid = $db->query("SELECT id FROM ecl_players ORDER BY player LIMIT 0,1")->fetchColumn();
		if(!isset($_SESSION['loggedin']))	{$_SESSION['loggedin'] = false;}
		if(!isset($_SESSION['playerid']))	{$_SESSION['playerid'] = $playerid;}
		else
		{
			if(isset($_POST['playerid']))
			{
				$playerid = $_POST['playerid'];
				$_SESSION['playerid'] = $playerid;
			}
			else {$playerid = $_SESSION['playerid'];}
		}
		$stmt = $db->prepare("SELECT player FROM ecl_players WHERE id =:pl");
		$stmt->bindParam(':pl', $playerid, PDO::PARAM_INT);
		$stmt->execute();
		$player_name = mb_convert_encoding($stmt->fetchColumn(), 'UTF-8', 'ISO-8859-1');
		$db = null;
	} catch (PDOException $e) {
		print "Det sket sig: " . $e->getMessage() . "<br/>";
		die();
	}

?>
<!DOCTYPE html>
<html lang='sv'>
<head>
<title>Spelare i <?php echo APPTITLE; ?></title>
<meta charset=utf-8 />
<meta name='viewport' content='width=device-width, initial-scale=1.0'> 
<link rel='shortcut icon' href='media/favicon.ico' />
<link rel='stylesheet' href='media/eclform.css' type='text/css' />
</head>
<body>

<h3>Spelare i <?php echo APPTITLE; ?></h3>

<?php

	if(!$_SESSION['loggedin'])
	{
		echo "<form action='players.php' method='post'>\n";
		echo "<ol>\n";
		echo "<li>\n";
		echo "<label for='playerid'>Spelare:</label>\n";
		echo "<select id='playerid' name='playerid' onchange='submit()'>\n";
		try {
			$db = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
			foreach($db->query("SELECT id, player FROM ecl_players ORDER BY player") as $row) {
				$pid = $row['id'];
				$pname = mb_convert_encoding($row['player'], 'UTF-8', 'ISO-8859-1');
				$sSel = "";
				if($playerid == $pid){$sSel = " selected='selected'";}
				echo "<option value='" . $pid . "'" . $sSel . ">" . $pname . "</option>\n";
			}
			$db = null;
		} catch (PDOException $e) {
			print "Det sket sig: " . $e->getMessage() . "<br/>";
			die();
		}
		echo "</select>\n";
		echo "</li>\n";
		echo "</ol>\n";
		echo "</form>\n";
	}

	echo "<form action='login.php' method='post'>\n";
	echo "<ol>\n";

	if($_SESSION['loggedin'])
	{
		echo "<li>\n";
		$cap = "Registrera score för " . $player_name;
		echo "<input type='button' class='btn' value='" . $cap . "' onclick='location.href=\"score_edit.php\";' />\n";
		echo "</li>\n";

		echo "<li>\n";
		$genitiv = "s";
		$last_char = substr($player_name, strlen($player_name)-1, 1);
		if($last_char == "s"){$genitiv = "";}
		$cap = "Redigera " . $player_name . $genitiv . " profil";
		echo "<input type='button' class='btn' value='" . $cap . "' onclick='location.href=\"player_edit.php\";' />\n";
		echo "</li>\n";

		echo "<li>\n";
		$cap = sprintf("Logga ut %s", $player_name);
		echo "<input type='button' class='btn' value='" . $cap . "' onclick='location.href=\"logout.php\";' />\n";
		echo "</li>\n";
	}
	else
	{

		echo "<li>\n";
		echo "<label for='playerpw'>Lösenord:</label>\n";
		echo "<input type='password' style='max-width:150px;' id='playerpw' name='playerpw' maxlength='20' />\n";
		echo "</li>\n";

		echo "<li>\n";
		$cap = sprintf("Logga in %s", $player_name);
		echo "<input type='submit' class='btn' value='" . $cap . "' />\n";
		echo "</li>\n";
	}

	echo "<li>\n";
	echo "<input type='button' class='btn' value='Hem' onclick='window.location.href=\"/" . BASEFOLDER . "\";' />\n";
	echo "</li>\n";

	echo "</ol>\n";
	echo "</form>\n";

	echo "</body>\n";
	echo "</html>\n";

}

//******************************************************************
?>
