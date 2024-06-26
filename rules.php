﻿<?php
include "base.php";
show_rules();

//******************************************************************
function show_rules(){
//Uppdaterad 2024-05-19 av Joakim [joakim.thulin@outlook.com]
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
	
<p><?php echo APPTITLE; ?> löper från första maj fram till och med den sista lördagen i september.</p>
<ul>
    <li>Segrare är spelaren med lägst nettoresultat. Vid lika nettoresultat vinner den med lägst spelhandicap (sHcp), är även detta lika delas segern.</li>
    <li>Tävlingsledaren bestämmer hur prisbordet skall vara utformat, prisbordet skapas utifrån inbetalade spelavgifter.</li>
    <li>I utgångsläget har alla spelare bogey på alla hålen, detta för att ge en uppfattning om ställningen under pågående tävling.</li>
    <li>Man får obegränsat antal chanser att registrera resultat på ett hål.</li>
    <li>Man måste registrera ett resultat från en runda innan man påbörjar nästa runda.</li>
    <li>Minst nio hål måste spelas, med valfritt starthål.</li>
    <li>Bara resultat från singel slagspel och match får användas. Resultat från parspel, scramble och liknande får inte räknas. Resultat från Bästboll får dock användas.</li>
    <li>Allt spel sker på Burvik. Herrar spelar från gul eller vit tee, damer spelar från röd, blå eller gul tee. Herrar över 75 år får spela från röd tee.</li>
    <li>Spelhandicap är de erhållna slag spelaren har från ordinarie tee på säsongens sista spelade dag, fram tills dess uppmanas spelaren att hålla sin spelhandicap aktuell i spelarprofilen.</li>
    <li>Om herre över 75 år nyttjar rätten att registrera resultat från röd tee under en säsong, är röd tee ordinarie tee.</li>
    <li>Slag räknas såsom i slaggolf, om en spelare saknar resultat vid säsongsavslut ges tomma hål resultatet par + 5 slag.</li>
    <li>Spelhandicap reduceras med 50 %. Avrundning sker uppåt, dvs spelare med spelhandicap 9 erhåller 9/2=4,5 => 5 slag.</li>
    <li>Tävlingsledare är föregående års segrare. Om två eller fler spelare delar på segern, skall en tävlingsledare lottas bland dessa spelare.</li>
    <li>Tävlingsledaren fungerar även som kassör och tar emot startavgiften på 100 kronor.</li>
    <li>Tävlingsledaren organiserar prisutdelning som infaller en timma efter solnedgång den sista söndagen i september (tips: blocka den här söndagen i din kalender redan tidigt på året, vill ju inte missa prisutdelningen).</li>
</ul>

<hr />
<address>Senast uppdaterad 2024-05-19 av <a href="mailto:<?php echo MAILADDRESS; ?>?Subject=<?php echo MAILSUBJECT; ?>"><?php echo MAILADDRESS; ?></a></address>

<p>
    <input type='button' value='Bruksanvisning' onclick='location.href="usage.php";' />
    <input type='button' value='Leaderboard' onclick='window.location.href="/<?php echo BASEFOLDER; ?>";' />
</p>

</body>
</html>
<?php
}
//******************************************************************
?>
