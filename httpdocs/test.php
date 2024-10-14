<?php
use function SurveyFriend\Results\runFunctionOnValue;

require_once('lib/mainfunctions.php');
include('lib/surveyfriend.php');

function formatNumber($wishedFormat, $number) {
    switch ($wishedFormat) {
        case 'money':
            return 'CHF ' . number_format($number, 2, ',', '.');
        case 'percent':
            return number_format($number, 2) . '%';
        case 'number':
            return number_format($number, 0, '', ',');
        case 'float':
            return number_format($number, 2, ',', '.');
        case 'duration':
            return number_format($number, 2, ',', '.');
        case 'bytes':
            return number_format($number, 1) . 'b';
        case 'bits':
            return number_format($number, 1) . 'b';
        case 'color':
            return sprintf("#%06X", $number);
        default:
            return $number;
    }
    
    
}

// function formatStringToNumberFormat($format) {
//     // Überprüfen, ob der Format-String mit einer Währung beginnt
//     $currencyPattern = '/^([^\d]*)(\d{1,3})([.,]\d{0,2})?/';
    
//     if (preg_match($currencyPattern, $format, $matches)) {
//         // Extrahiere die Teile des Formats
//         $currencySymbol = trim($matches[1]); // Währungszeichen
//         $integerPart = $matches[2]; // Ganze Zahl
//         $decimalPart = isset($matches[3]) ? trim($matches[3], '.') : ''; // Dezimalstelle

//         // Bestimme die Anzahl der Dezimalstellen
//         $decimals = strlen($decimalPart) > 0 ? strlen(trim($decimalPart, ',')) : 0;

//         // Gebe das Format für number_format zurück
//         return [
//             'currency' => $currencySymbol,
//             'decimals' => $decimals,
//             'thousands_sep' => ',',
//             'decimal_sep' => '.'
//         ];
//     }

//     return null; // Rückgabe null, wenn das Format nicht erkannt wird
// }


$text = "Deine jährliche Ersparnis, wenn dein Geschäft aBusiness Suite verwenden würde: money(124464).– Schweizer Franken 
Mehrere Schweizer Unternehmen wie Deines haben dieses Sparpotential bereits entdeckt.";

// Funktion zum Formatieren des Geldbetrags
function formatMoney($matches) {
    $amount = (float)$matches[1]; // Extrahiere den Betrag aus der Funktion
    return 'CHF ' . number_format($amount, 2, ',', '.'); // Formatieren nach CHF 0,0.00
}

// Regex, um "money(x)" zu finden und durch das formatierte Ergebnis zu ersetzen
$result = preg_replace_callback('/money\((\d+)\)/', 'formatMoney', $text);

// Ausgabe des Ergebnisses
echo $result;

exit;

/*echo money(15.50, "CHF");
echo "<br>";
echo money(13.33333, 3);
echo "<br>";
echo money(15.33333);
*/




    libraries();



//    $formula = \SurveyFriend\Results\cutFunctions('money(12 * (( 360.9 + 10000 + 90 + ( 20 * 360.9 * 0.2 )) - (( 20 * 50 ) + ( 10000 * 0.1 ))), "CHF")');

$formula = \SurveyFriend\Results\cutFunctions("money((30*50)*12/365,'€')");

    debug($gFunctions);


    

    




    $m = new EvalMath;
    $m->suppress_errors = true; // do not print errors

    $result = $m->evaluate($formula);

    if ($m->last_error) {
        echo 'Error: ' . $m->last_error;
    }


    echo runFunctionOnValue($result);



    shutdown();