<?php
include "base.php";
show_usage();

//******************************************************************
function show_usage(){
//Uppdaterad 2024-01-29 av Joakim [joakim.thulin@outlook.com]
?>
<!DOCTYPE html>
<html lang='sv'>
<head>
<title>Att använda <?php echo APPTITLE; ?></title>
<meta charset=utf-8 />
<meta name='viewport' content='width=device-width, initial-scale=1.0'> 
<link rel='shortcut icon' href='media/favicon.ico' />
<link rel='stylesheet' media='screen' type='text/css' href='media/basic.css' />
</head>

<body>
	
<p>Gör så här för att registrera en score:</p>
<ol>
<li>Tryck på knappen "Mata in resultat..."</li>
<li>Välj in dig själv i rullgardinen.</li>
<li>Ange ditt lösenord och tryck på loginknappen (du kommer nu att vara inloggad fram tills att du väljer att logga ut eller när du stänger aktuell flik i webbläsaren).</li>
<li>Tryck på knappen "Registrera score..."</li>
<li>Välj in hål och bruttoresultat från de två översta rullgardinerna.</li>
<li>Ange tidpunkt för denna golfrunda genom att välja in månad och dag i de två understa rullgardinerna.</li>
<li>Tryck på knappen "Spara resultat".</li>
</ol>

<hr />
<address>Senast uppdaterad 2023-04-18 av <a href="mailto:<?php echo MAILADDRESS; ?>?Subject=<?php echo MAILSUBJECT; ?>"><?php echo MAILADDRESS; ?></a></address>

<p>
<input type='button' value='Regler' onclick='location.href="rules.php";' />
<input type='button' value='Leaderboard' onclick='window.location.href="/<?php echo BASEFOLDER; ?>";' />
</p>

</body>
</html>
<?php
}
//******************************************************************
?>
