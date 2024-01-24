<?php
session_start();
include "base.php";
check_user();

//******************************************************************
function check_user(){
//Uppdaterad 2013-11-27 av Joakim [joakim.thulin@outlook.com]

	$playerid = $_SESSION['playerid'];
	$playerpw = $_POST['playerpw'];

	try {
		$dsn = "mysql:host=" . DBSERVER . ";port=3306;dbname=" . DBNAME;
		$db = new PDO($dsn, DBUSER, DBPW);
		$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$stmt = $db->prepare("select count(id) from ecl_players where id =:i and pw =:p");
		$stmt->bindParam(':i', $playerid, PDO::PARAM_INT);
		$stmt->bindParam(':p', $playerpw, PDO::PARAM_STR);
		$stmt->execute();
		$r = $stmt->fetch(PDO::FETCH_NUM);
		$count = $r[0];
		$db = null;
	} catch (PDOException $e) {
		print "Det sket sig: " . $e->getMessage() . "<br/>";
		die();
	}

	if($count == 1)
	{
		$_SESSION['loggedin'] = true;
		$_SESSION['playerpw'] = $playerpw;
		header("location:players.php");
	}
	else
	{
		$_SESSION['loggedin'] = false;
?>
<!DOCTYPE html>
<html lang='sv'>
<head>
<title>Logga in</title>
<meta charset=utf-8 />
<meta name='viewport' content='width=device-width, initial-scale=1.0'> 
<link rel='shortcut icon' href='media/favicon.ico' />
<link rel='stylesheet' media='screen' type='text/css' href='media/basic.css' />
</head>
<body>
<h3>Kunde inte logga in på valt namn och lösenord.</h3>
<p><input type='button' value='Det sket sig, tillbaka hem och försök på nytt' onclick='location.href="players.php";' /></p>
</body>
</html>
<?php
	}
}
?>
