<?php

######    Litecoin & Bitcoin Balance and Exchange Rate Server.    ######
######          Developer: BrianJHodge (GSlayerBrian)             ######
######         Copyright 2013-2014 Game Slayer Studios            ######
######                  www.gameslayer.org                        ######

/*
Contributions greatly accepted if you like my work :)
Bitcoin:  169g4J29hQAhwHHjMJdzyA6VpkGngmdxRf
Litecoin: LbRXVfvUDUnYohUgFq8wNnWt6WvP7rcL5N

#License & Disclaimer:
This is the first code I have publicly shared so I don't really know anything about licensing.
Suffice to say that any and all parties may freely modify and redistribute this software and  may use it for any purpose as long as original attribution remains above.

I offer no warranty with this software and take no responsibility for consequences of its use.


#Usage:
Call index.php with ?c=ltc or ?c=btc, and then &balance, &exchangeRate, or &bothBalances
Add &formatted=1 to add HTML tags which help it play nicer with MetaWidget


#Notes:
-This application is tailored to my exacting needs so I apologize if it is not appropriate for you. I happen to use ltc.kattare.com as my mining pool and wanted to keep on top of my balance there.
-The htmlFormat function exists because I couldn't figure out how to get MetaWidget (Android) to grab data from the page without them.
-BTC is in mBTC
-Some of these functions were taken from other projects of my own, so they may have unecessary functionality for the scope of this applicaiton.

This text can be found in both README.md and index.php. index.php is the only file required for this application to function.

*/

/* Edit these values to your own api_key and Bitcoin address of interest. */

$bitcoinAddress = '{YOUR_BITCOIN_ADDRESS}';

$kattare_api_key = '{YOUR_LTC.KATTARE.COM_API_KEY}';

/* BTC */

$currentBTCexchangeRate = file_get_contents('https://blockchain.info/q/24hrprice');
$currentBalance = file_get_contents('https://blockchain.info/q/addressbalance/' . $bitcoinAddress);

if ($_GET['c'] == 'btc' && $_GET['q'] == 'balance') {

	$content .= $currentBalance / (10e+4) ;
    
    if ($_GET['formatted'] == '1') {
        echo htmlFormat($content);
    } else {
        echo $content;
    }
    
    
}

if ($_GET['c'] == 'btc' && $_GET['q'] == 'exchangeRate') {

	$content .= '$ ' . ($currentBTCexchangeRate / 1000);
    if ($_GET['formatted'] == '1') {
        echo htmlFormat($content);
    } else {
        echo $content;
    }
    
}

if ($_GET['c'] == 'btc' && $_GET['q'] == 'bothBalances') {

	$content .= '' . ($currentBalance / (10e+4)) . ' (' . Format_BTC_USD(($currentBalance / (10e+7))) . ')';
    
	if ($_GET['formatted'] == '1') {
        echo htmlFormat($content);
    } else {
        echo $content;
    }

}

/* End BTC */

/* LTC */

/* I borrowed this curl stuff */
$curl_handle=curl_init();
curl_setopt($curl_handle, CURLOPT_URL,'http://ltc.kattare.com/api.php?api_key=' . $kattare_api_key);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, 'LTC Manager');
$query = curl_exec($curl_handle);
curl_close($curl_handle);

$decoded = json_decode($query, true);

$currentBalance = $decoded['confirmed_rewards'];

if ($_GET['c'] == 'ltc' && $_GET['q'] == 'balance') {

	$content .= $currentBalance;
    if ($_GET['formatted'] == '1') {
        echo htmlFormat($content);
    } else {
        echo $content;
    }
    
}


if ($_GET['c'] == 'ltc' && $_GET['q'] == 'bothBalances') {

    $content .= number_format($currentBalance, 5) . ' ($ ' . number_format(($currentBalance * ltcUSD()), 2) . ')' ; 
    if ($_GET['formatted'] == '1') {
        echo htmlFormat($content);
    } else {
        echo $content;
    }
    
}

if ($_GET['c'] == 'ltc' && $_GET['q'] == 'exchangeRate') {

    if ($_GET['formatted'] == '1') {
        echo htmlFormat('$ ' . ltcUSD());
    } else {
        echo '$ ' . ltcUSD();
    }

}

/* End LTC */

/* Functions */

function Format_BTC_USD($amount) {
    global $currentBTCexchangeRate;
    $content .= '$' . str_pad(number_format(($amount * $currentBTCexchangeRate), 2, '.', ','), 7, ' ', STR_PAD_LEFT);
    return $content;
}

function getTimeSince ($time) {
    /* I did not write this function - I found it somewhere. Wish I could attribute credit to the original author, but it will have to suffice admitting that I did not write it myself. */
    $time = time() - $time;

    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }

}

function ltcUSD() {

    $data = file_get_contents('https://btc-e.com/api/2/ltc_usd/trades');
    
    $data = cleanData($data);
    
    $lines = explode(';', $data);
    foreach ($lines as $line) {
        $pieces = explode(',', $line);
        $pieces2 = explode(':', $pieces['1']);
        
        $thisPrice = $pieces2['1'];
        
        $sum = $sum + $thisPrice;
    }
    
    $average = $sum / count($lines);

    return number_format($average, 5);
    
}

function cleanData($data) {
    return str_replace(Array('[', ']', '{', '},'), Array('', '', '', ';'), $data);
}

function htmlFormat($content) {
    return '<html><body><div><p></p><p>' . $content . '<p></div></body></html>';
}
