<?php
session_start();
include "general.php";
include "base.php";
if($_SESSION['loggedin']) {player_edit();} else {no_access();}

//******************************************************************
function player_edit(){
//Uppdaterad 2023-04-18 av Joakim [joakim.thulin@outlook.com]

	try {
		$db = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
		$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$stmt = $db->prepare("SELECT player, hcp, pw FROM ecl_players WHERE id =:pl");
		$stmt->bindParam(':pl', $_SESSION['playerid'], PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();
		$playername = mb_convert_encoding($row['player'], 'UTF-8', 'ISO-8859-1');
		$hcp = $row['hcp'];
		$playerpw = $row['pw'];
		$db = null;
	} catch (PDOException $e) {
		print "Det sket sig: " . $e->getMessage() . "<br/>";
		die();
	}
	
?>
<!DOCTYPE html>
<html lang='sv'>
<head>
<title>Redigera spelare i Burvik Running Eclectic</title>
<meta charset=utf-8 />
<meta name='viewport' content='width=device-width, initial-scale=1.0'> 
<link rel='shortcut icon' href='media/favicon.ico' />
<link rel='stylesheet' href='media/eclform.css' type='text/css' />
</head>
<body>

<h3>Redigera spelare i Burvik Running Eclectic</h3>

<form action='player_update.php' method='post'>
<ol>

<li>
<label for='playername'>Namn:</label>
<input type='text' style='max-width:150px;' id='playername' name='playername' maxlength='30' value='<?php echo $playername; ?>' />			
</li>

<li>
<label for='hcp'>SpelHcp:</label>	
<select id='hcp' name='hcp'>	
<?php
	for ($k = 0; $k<37; $k++) {
		$sSel = "";
		if($k == $hcp){$sSel = " selected='selected'";}
		echo "<option value='" . $k . "'" . $sSel . ">" . $k . "</option>\n";
	}
?>
</select>
</li>

<li>
<label for='playerpw'>Lösenord:</label>
<input type='password' style='max-width:150px;' id='playerpw' name='playerpw' maxlength='20' value='<?php echo $playerpw; ?>' />
</li>

<li>
<input type='button' class='btn' value='Uppdatera spelare' onclick='submit()' />
</li>

<li>
<input type='button' class='btn' value='Avbryt' onclick='location.href="players.php";' />
</li>

</ol>
</form>

</body>
</html>
<?php
}
//******************************************************************
?>
