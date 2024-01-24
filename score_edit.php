<?php
session_start();
include "general.php";
include "base.php";
if($_SESSION['loggedin']) {score_edit();} else {no_access();}

//******************************************************************
function score_edit(){
//Uppdaterad 2023-04-18 av Joakim [joakim.thulin@outlook.com]

	date_default_timezone_set('Europe/Stockholm');
	$playerid = $_SESSION['playerid'];
	$current_year = date("Y") + 0;
	//$holes;

	try {
		$db = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$playername = $db->query("SELECT player FROM ecl_players WHERE id = $playerid")->fetchColumn();
/* 		$num_rows = $db->query("select count(hole) from ecl_scores where player = $playerid and season = $current_year")->fetchColumn();
		if($num_rows > 0){
			$holes = array($num_rows);
			$k = 0;
			foreach($db->query("select hole from ecl_scores where player = $playerid and season = $current_year order by hole") as $row) {
				$holes[$k] = $row['hole'];
				$k++;
			}
		}
 */		$db = null;
	} catch (PDOException $e) {
		print "Det sket sig: " . $e->getMessage() . "<br/>";
		die();
	}

?>
<!DOCTYPE html>
<html lang='sv'>
<head>
<title>Registrera score i Burvik Running Eclectic</title>
<meta charset=utf-8 />
<meta name='viewport' content='width=device-width, initial-scale=1.0'> 
<link rel='shortcut icon' href='media/favicon.ico' />
<link rel='stylesheet' href='media/eclform.css' type='text/css' />
</head>
<body>
	
<h3>Registrera score för <?php echo $playername; ?> i Burvik Running Eclectic</h3>
<form action='score_update.php' method='post'>
<ol>
	
<li>
<label for='hole'>Hål:</label>
<select id='hole' name='hole'>
<?php
//	if($num_rows == 0){
		for ($k = 1; $k<19; $k++) {
			$sSel = "";
			if($k == 1){$sSel = " selected='selected'";}
			echo "<option value='" . $k . "'" . $sSel . ">" . $k . "</option>\n";
		}
/* 	}else{
		$dirty=false;
		for ($k = 1; $k<19; $k++) {
			$used=false;
			for ($p = 0; $p<$num_rows; $p++) {
				if($k==$holes[$p]){$used=true;}
			}
			if(!$used){
				$sSel = "";
				if(!$dirty){$sSel = " selected='selected'";$dirty=true;}
				echo "<option value='" . $k . "'" . $sSel . ">" . $k . "</option>\n";
			}
		}
	}
 */?>
</select>
</li>

<li>
<label for='score'>Score:</label>
<select id='score' name='score'>
<?php
	for ($k = 1; $k<11; $k++) {
		$sSel = "";
		if($k == 4){$sSel = " selected='selected'";}
		echo "<option value='" . $k . "'" . $sSel . ">" . $k . "</option>\n";
	}
?>
</select>
</li>

<li>
<label for='playmonth'>Spelmånad:</label>
<select id='playmonth' name='playmonth'>
<?php
	$months = array('Maj','Juni','Juli','Augusti', 'September');
	for ($k = 5; $k<=9; $k++) {
		$sSel = "";
		if($k == date("m")){$sSel = " selected='selected'";}
		echo "<option value='" . $k . "'" . $sSel . ">" . $months[$k-5] . "</option>\n";
	}
?>
</select>
</li>

<li>
<label for='playday'>Speldag:</label>
<select id='playday' name='playday'>
<?php
	for ($k = 1; $k<=31; $k++) {
		$sSel = "";
		if($k == date("d")){$sSel = " selected='selected'";}
		echo "<option value='" . $k . "'" . $sSel . ">" . $k . "</option>\n";
	}
?>
</select>
</li>

<li>
<input type='button' class='btn' value='Spara resultat' onclick='submit()' />
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
