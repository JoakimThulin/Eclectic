<?php
include "base.php";
leaderboard();

//******************************************************************
function leaderboard(){
//Uppdaterad 2023-04-18 av Joakim [joakim.thulin@outlook.com]

	date_default_timezone_set('Europe/Stockholm');

	$current_year = date("Y");
	if(!isset($_GET["season"])) {$_GET["season"] = "";}
	if(!isset($_POST["season"])) {$_POST["season"] = "";}
	$season = $_POST["season"];
	if($season == ""){$season = $_GET["season"];}
	if($season == ""){$season = $current_year;}
	$season = $season + 0;

	?>
<!DOCTYPE html>
<html lang='sv'>
<head>
<title>Burvik Running Eclectic</title>
<meta charset=utf-8 />
<meta name='viewport' content='width=device-width, initial-scale=1.0'> 
<link rel='shortcut icon' href='media/favicon.ico' />
<link rel='stylesheet' media='screen' type='text/css' href='media/basic.css' />
</head>
<body>
<h3>Burvik Running Eclectic <?php echo $season ?></h3>

	<?php
	SetupSeasonSwitch($current_year, $season);
	
	$gotdata = !(IsPreSeason() and ($current_year == $season));
	if($gotdata){
		$sb = ReadTableData($season);
		$userCount = count($sb);
		if($userCount > 0){
			$sorted = multiSort($sb);
			PrintTable($sorted);
		}else{
			echo "<p>Det saknas än så länge resultat för denna säsong.</p>\n";
		}
	}

	if(!IsPreSeason() and ($current_year == $season)){
		PrintRecentChanges();
	}
	
	?>

<hr />
<p>
<input type='button' value='Bruksanvisning' onclick='location.href="usage.html";' />
<input type='button' value='Regler' onclick='location.href="rules.html";' />
</p>

</body>
</html>
<?php
}
//******************************************************************
function ReadTableData($season){
//Uppdaterad 2013-11-08 av Joakim [joakim.thulin@outlook.com]

	//Matrisen som skapas är på följande format:
	//$sb[$idx]['name']
	//$sb[$idx]['hcp']
	//$sb[$idx][hXXcap] XX = 1-18
	//$sb[$idx][hXXsco] XX = 1-18
	//$sb[$idx]['brutto']
	//$sb[$idx]['netto']
	//$sb[$idx]['caption']

	$default_score = GetBurvikParArray();//Skapa ett paket med Burviks par
	$sb = null;

	$idx = -1;
	$extra_stroke = 1;//ospelat hål i sommarläge ger bogey
	$noscore_code = 88;
	$noscore_cap = "Hålet ännu inte spelat, ansätter bogey tills vidare (";
	$winter_mode = IsWinter();
	$historic_season = false;
	if($season != date("Y")){
		$historic_season = true;
		$winter_mode = true;
	}
	if($winter_mode){
		$extra_stroke = 5;//ospelat hål i vinterläge ger slaggolfkringla
		$noscore_code = 99;
		$noscore_cap = "Hålet blev aldrig spelat, straffas därför med slaggolfkringla (";
	}
	
	try {
			$db = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
			$k = -1;
			foreach($db->query("SELECT id, player, hcp FROM ecl_players ORDER BY id") as $row) {
				$k++;
				$players[$k]['id'] = $row['id'];
				$players[$k]['name'] = mb_convert_encoding($row['player'], 'UTF-8', 'ISO-8859-1');
				$players[$k]['hcp'] = $row['hcp'];
			}
			
			$playerCount = $db->query("SELECT count(*) FROM ecl_players")->fetchColumn();

			$q_season_hits = $db->prepare("SELECT count(score) FROM ecl_scores WHERE player=:p AND season=:s");
			$q_hist_season = $db->prepare("SELECT hcp FROM ecl_scores WHERE player =:p AND season =:s ORDER BY play_date DESC LIMIT 0,1");
			$q_shots = $db->prepare("SELECT count(score) FROM ecl_scores WHERE player=:p AND season=:s AND hole=:h");
			$q_hole = $db->prepare("SELECT score, play_date FROM ecl_scores WHERE player=:p AND season=:s AND hole=:h");

			for ($player = 0; $player<$playerCount; $player++) {

				$player_id = $players[$player]['id'];
				$player_name = $players[$player]['name'];
				$player_hcp = $players[$player]['hcp'];
				
				//Hur många scorer har spelaren reggat aktuell säsong?
				$q_season_hits->bindParam(':p', $player_id, PDO::PARAM_INT);
				$q_season_hits->bindParam(':s', $season, PDO::PARAM_INT);
				$q_season_hits->execute();
				$r = $q_season_hits->fetch(PDO::FETCH_NUM);
				$hits = $r[0];

				if($hits > 0){
				//spelaren är aktiv aktuell säsong, fortsätt leta data...

					//Om det är en gammal säsong som visas så hämtas hcp som spelaren hade vid det tillfället
					if($historic_season){
						$q_hist_season->bindParam(':p', $player_id, PDO::PARAM_INT);
						$q_hist_season->bindParam(':s', $season, PDO::PARAM_INT);
						$q_hist_season->execute();
						$result_season = $q_hist_season->fetchObject();
						$player_hcp = $result_season->hcp;					
					}
					
					$idx++;
					$sb[$idx]['name'] = $player_name;
					$sb[$idx]['hcp'] = $player_hcp;
					$score = 0;

					for ($f = 1; $f<19; $f++) {
						$par = $default_score[$f - 1];		
						$hole_result = 0;
						$noscore = true;
						$q_shots->bindParam(':p', $player_id, PDO::PARAM_INT);
						$q_shots->bindParam(':s', $season, PDO::PARAM_INT);
						$q_shots->bindParam(':h', $f, PDO::PARAM_INT);
						$q_shots->execute();
						$r = $q_shots->fetch(PDO::FETCH_NUM);
						if($r[0] > 0){
							$q_hole->bindParam(':p', $player_id, PDO::PARAM_INT);
							$q_hole->bindParam(':s', $season, PDO::PARAM_INT);
							$q_hole->bindParam(':h', $f, PDO::PARAM_INT);
							$q_hole->execute();
							$result_hole = $q_hole->fetchObject();
							$noscore = false;
							$hole_result = $result_hole->score;
							$play_date = $result_hole->play_date;
						}else{
							$hole_result = $par + $extra_stroke;//icke-spelat hål
						}
						$score += $hole_result;
						if($noscore){
							$par_status_code = $noscore_code;//icke-spelat hål
							$cap = $noscore_cap . $hole_result . " slag)";//icke-spelat hål
						}else{
							$par_status_code = GetParStatusCode($par, $hole_result);
							$parstatus = GetParStatus($par_status_code);
							$cap = $parstatus . " " . $play_date;
						}
						$my_class = GetParStatusClass($par_status_code);
						if(($f==9) or ($f==18)){
							$my_class .= " style='border-right: black 3px solid;'";
						}
						$ent_sco = "h" . $f . "sco";
						$ent_cap = "h" . $f . "cap";
						$sb[$idx][$ent_cap] = $my_class . " title='" . $cap . "'";
						if($noscore){
							$sb[$idx][$ent_sco] = "";
						}else{
							$sb[$idx][$ent_sco] = $hole_result;
						}
					}
					$net_score = $score - round($player_hcp / 2);
					$sb[$idx]['brutto'] = $score;
					$sb[$idx]['netto'] = $net_score;
					$sb[$idx]['caption'] = $score . " - " . round($player_hcp / 2) . " = " . $net_score;
					
				}

			}

			$db = null;
	} catch (PDOException $e) {
			print "Fråga till DB om användarlista kraschade med: " . $e->getMessage() . "<br/>";
			die();
	}
	
	return $sb;
}
//******************************************************************
function PrintRecentChanges(){
	//Uppdaterad 2023-05-02 av Joakim [joakim.thulin@outlook.com]
	
		$gotdata = false;
	
		try {
			$conn = new PDO("mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME, DBUSER, DBPW);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$q_player = $conn->prepare("SELECT player FROM ecl_players WHERE id=:i");
			
			$current_year = date("Y");
			$sth = $conn->prepare("SELECT player, hole, score, play_date FROM ecl_scores WHERE season=:y ORDER BY play_date DESC LIMIT 0,20");
			$sth->bindParam(':y', $current_year, PDO::PARAM_INT);
			$sth->execute();
			$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
	
			$idx = -1;
			foreach($rows as $row) {
				$gotdata = true;
				$player_id = $row['player'];
				$hole = $row['hole'];
				$score = $row['score'];
				$play_date = $row['play_date'];
				
				$q_player->bindParam(':i', $player_id, PDO::PARAM_INT);
				$q_player->execute();
				$result = $q_player->fetchObject();
				$player_name = mb_convert_encoding($result->player, 'UTF-8', 'ISO-8859-1');				
	
				$msg = $play_date . ": " . $player_name . " får " . ResolveResult($score, $hole) . " på hål " . $hole . ".";
				$idx++;
				$sb[$idx] = $msg;
			}
			$conn = null;
		} catch (PDOException $e) {
				print "Fråga till DB om senaste resultat kraschade med: " . $e->getMessage() . "<br/>";
				die();
		}
	
		if($gotdata){
			$userCount = count($sb);
			echo "<h3>De " . $userCount . " senaste resultaten</h3>\n";
			echo "<table style='border:0;border-collapse: collapse;'>\n";//border:solid 2px black;border-collapse: collapse;
			for ($idx = 0; $idx<$userCount; $idx++){
				echo "<tr><td style='border:0;'>" . $sb[$idx] . "</td></tr>\n";// class='ram'
			}
			echo "</table>\n";
		}
		
	}
	//******************************************************************
function IsPreSeason(){
//Uppdaterad 2014-09-29 av Joakim [joakim.thulin@outlook.com]

	//Funktionen kollar om det är försäsong idag, dvs före förste maj

/* 	$dtime = date_create();
	date_date_set($dtime, 2023, 5, 20);
	$timestamp = $dtime->getTimestamp();
 */
	$today = getdate();//$timestamp

	$winter = false;
	$month = $today['mon'];//aktuellt månadsnummer
	if($month < 5) {$winter = true;}//Vinter och vår = vinter

	return $winter;

}
//******************************************************************
function IsWinter(){
//Uppdaterad 2023-09-01 av Joakim [joakim.thulin@outlook.com]

	//Funktionen kollar om det är sommar- eller vinterläge idag,
	//dvs från förste maj till sista lördagen i september

/* 	$dtime = date_create();
	date_date_set($dtime, 2023, 5, 20);
	$timestamp = $dtime->getTimestamp();
 */
	$today = getdate();//$timestamp

	$winter = false;
	$month = $today['mon'];//aktuellt månadsnummer
	if($month < 5) {$winter = true;}//Vinter och vår = vinter
	if($month == 9){
		//nästan hela september är sommar, men kanske inte slutet
		$no_saturdays_left = true;
		$current_day = $today['mday'];
		$current_year = $today['year'];
		for ($f = $current_day; $f<31; $f++) {
			if($f < 10){$p = "0";}else{$p = "";}
			$test_date = $current_year . "-09-" . $p . $f;
			$week_day = date("N", strtotime($test_date) );
			if($week_day == 6) {$no_saturdays_left = false;}
		}
		if($no_saturdays_left) $winter = true;
	}
	if($month > 9) {$winter = true;}//hösten = vinter

	return $winter;

}
//******************************************************************
function ResolveResult($score, $hole){
//Uppdaterad 2013-06-09 av Joakim [joakim.thulin@outlook.com]

	//Översätter ett resultat till birdie, par, bogey osv
	
	$default_score = GetBurvikParArray();//Skapa ett paket med Burviks par
	$par = $default_score[$hole - 1];
	$balance = $score - $par;
	switch($balance){
		case -2:
			$parstatus = "en <strong>eagle</strong>";
			break;
		case -1:
			$parstatus = "en birdie";
			break;
		case 0:
			$parstatus = "ett par";
			break;
		case 1:
			$parstatus = "en bogey";
			break;
		case 2:
			$parstatus = "en dubbelbogey";
			break;
		case 3:
			$parstatus = "en trippelbogey";
			break;
		case 4:
			$parstatus = "en kvadrupelbogey";
			break;
		case 5:
			$parstatus = "en <strong>kringla</strong>";
			break;
		default:
			$parstatus = "<strong>något konstigt</strong>";
	}
	return $parstatus;
	
}
//******************************************************************
function GetBurvikParArray(){
//Uppdaterad 2013-05-05 av Joakim [joakim.thulin@outlook.com]

	//Skapa ett paket med Burviks par

	$par = array(18);
	$par[0] = 5;
	$par[1] = 3;
	$par[2] = 4;
	$par[3] = 4;
	$par[4] = 3;
	$par[5] = 5;
	$par[6] = 5;
	$par[7] = 3;
	$par[8] = 4;
	$par[9] = 4;
	$par[10] = 5;
	$par[11] = 3;
	$par[12] = 4;
	$par[13] = 3;
	$par[14] = 4;
	$par[15] = 5;
	$par[16] = 4;
	$par[17] = 4;
	return $par;
}
//******************************************************************
function PrintTable($sb){
//Uppdaterad 2023-04-18 av Joakim [joakim.thulin@outlook.com]

	//Indatamatrisen är på följande format:
	//$sb[$idx]['name']
	//$sb[$idx]['hcp']
	//$sb[$idx][hXXcap] XX = 1-18
	//$sb[$idx][hXXsco] XX = 1-18
	//$sb[$idx]['brutto']
	//$sb[$idx]['netto']
	//$sb[$idx]['caption']

	$userCount = count($sb);

	echo "<table style='border:solid 2px black;border-collapse: collapse;'>\n";
	echo "<tr>\n";
	echo "<th>Spelare</th>\n";
	echo "<th style='border-right: black 3px solid;'>sHcp</th>\n";
	//$a;
	for ($k = 1; $k<19; $k++) {
		if(($k==9) or ($k==18)){
			$a = "<th style='border-right: black 3px solid;'>" . $k . "</th>\n"; 
		}else{
			$a = "<th>" . $k . "</th>\n"; 
		}
		echo $a;
	}
	echo "<th>Resultat</th>\n";
	echo "</tr>\n";

	for ($idx = 0; $idx<$userCount; $idx++){
		echo "<tr>\n";
		echo "<td class='ram'>" . $sb[$idx]['name'] . "</td>\n";
		echo "<td class='ramc' style='border-right: black 3px solid;'>" . $sb[$idx]['hcp'] . "</td>\n";
		for ($f = 1; $f<19; $f++) {
			$ent_sco = "h" . $f . "sco";
			$ent_cap = "h" . $f . "cap";
			$cell_string = "<td" . $sb[$idx][$ent_cap] . ">" . $sb[$idx][$ent_sco] . "</td>\n";
			echo $cell_string;
		}
		echo "<td class='ramc'>" . $sb[$idx]['caption'] . "</td>\n";
		echo "</tr>\n";
	}

	echo "</table>\n";

}
//******************************************************************
function SetupSeasonSwitch($current_year, $season){
//Uppdaterad 2023-05-02 av Joakim [joakim.thulin@outlook.com]

	$winter = IsWinter();
	if($winter){
		echo "<p>Tävlingen är stängd, en ny omgång startar förste maj, välkommen tillbaka då. Har du frågor kan du mejla dem till Burvik Running Eclectic på adressen  <a href='mailto:eclectic@thuborg.se?Subject=Fr&aring;ga%20till%20Burvik%20Eclectic'>eclectic@thuborg.se</a></p>\n";
	}else{
		echo "<p><input type='button' value='Tryck här för att mata in resultat och ändra din spelarprofil' onclick='location.href=\"players.php\";' /></p>\n";
	}

	echo "<form action='' method='post'>\n";//Leaving action value empty is not W3C Valid. Just to notice. However it works well
	echo "<p>\n";
	echo "Säsong: <select name='season' onchange='submit()'>\n";

	$idx = 0;
	$years[$idx] = $current_year;
	try {
		$dsn = "mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME;
		$db = new PDO($dsn, DBUSER, DBPW);
		foreach($db->query('SELECT DISTINCT season FROM ecl_scores ORDER BY season DESC') as $row) {
			$year = $row['season'];
			if($year != $current_year){
				$idx++;
				$years[$idx] = $year;
				}
		}
		$db = null;
	} catch (PDOException $e) {
		print "Felaktigheter!: " . $e->getMessage() . "<br/>";
		die();
	}

	foreach($years as $y) {
		$sSel = "";
		if($y == $season){
			$sSel = " selected='selected'";
			}
		echo "<option value='" . $y . "'" . $sSel . ">" . $y . "</option>\n";
}

	echo "</select>\n";
	echo "</p>\n";
	echo "</form>\n\n";
}
//******************************************************************
function GetParStatus($i){
//Uppdaterad 2012-04-10 av Joakim [thulin11@gmail.com]

	switch($i){
		case 0:
			$parstatus = "Birdie";
			break;
		case 1:
			$parstatus = "Par";
			break;
		case 2:
			$parstatus = "Bogey";
			break;
		case 3:
			$parstatus = "Dubbelbogey";
			break;
		case 4:
			$parstatus = "Eagle";
			break;
		case 88:
			$parstatus = "Hålet inte spelat ännu";
			break;
		case 99:
			$parstatus = "Hålet blev aldrig spelat";
			break;
		default:
			$parstatus = "Sämre än dubbelbogey";
	}
	return $parstatus;
}
//********************************************************************************************************f
function GetParStatusClass($i){
//Uppdaterad 2012-04-10 av Joakim [thulin11@gmail.com]

	switch($i){
		case 0:
			$my_class = " class='ram_birdie'";
			break;
		case 1:
			$my_class = " class='ram_par'";
			break;
		case 2:
			$my_class = " class='ram_bogey'";
			break;
		case 3:
			$my_class = " class='ram_dbogey'";
			break;
		case 4:
			$my_class = " class='ram_eagle'";
			break;
		case 88:
			$my_class = " class='ram_88'";
			break;
		case 99:
			$my_class = " class='ram_99'";
			break;
		default:
			$my_class = " class='ramc'";
	}
	return $my_class;
}
//********************************************************************************************************
function GetParStatusCode($par, $score){
//Uppdaterad 2012-04-10 av Joakim [thulin11@gmail.com]

	$netto = $score - $par;
	//$score_code;
	switch($netto){
		case -1:
			$score_code = 0;
			break;
		case 0:
			$score_code = 1;
			break;
		case 1:
			$score_code = 2;
			break;
		case 2:
			$score_code = 3;
			break;
		case -2:
			$score_code = 4;
			break;
		default:
			$score_code = 9;
			break;
	}
	return $score_code;
}
//******************************************************************
function multiSort($data){
//Uppdaterad 2013-09-08 av Joakim [joakim.thulin@outlook.com]

	//Indatamatrisen är på följande format:
	//$sb[$idx]['name']
	//$sb[$idx]['hcp']
	//$sb[$idx][hXXcap] XX = 1-18
	//$sb[$idx][hXXsco] XX = 1-18
	//$sb[$idx]['brutto']
	//$sb[$idx]['netto']
	//$sb[$idx]['caption']
	
	//Exempel 3 från http://se2.php.net/array_multisort har använts för sorteringen
	foreach ($data as $key => $row) {
		$netto[$key]  = $row['netto'];
		$hcp[$key] = $row['hcp'];
	}
	array_multisort($netto, SORT_ASC, $hcp, SORT_ASC, $data);
	return $data;

}
//*********************************************************************************************************
?>
