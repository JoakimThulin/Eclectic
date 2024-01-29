<?php
include "base.php";

//******************************************************************
function no_access(){
//Uppdaterad 2013-05-05 av Joakim [joakim.thulin@outlook.com]

?>
<!DOCTYPE html>
<html lang="sv">
<head>
<title>Inga rättigheter i <?php echo APPTITLE; ?></title>
<meta charset=utf-8 />
<meta name='viewport' content='width=device-width, initial-scale=1.0'> 
<link rel='shortcut icon' href='media/favicon.ico' />
<link rel='stylesheet' media='screen' type='text/css' href='media/basic.css' />
</head>
<body>

<h3>Otillåtet tillträde</h3>
<p>Du kom till den här sidan utan att ha loggit in korrekt.</p>
<p><input type='button' class='btn' value='Tillbaks till leaderboard' onclick='window.location.href="/<?php echo BASEFOLDER; ?>";' /></p>
</body>
</html>
<?php
}
?>
