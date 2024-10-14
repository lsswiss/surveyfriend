<?php
use function SurveyFriend\Results\runFunctionOnValue;

require_once('lib/mainfunctions.php');
include('lib/surveyfriend.php');

function validate_json($jsonString) {
    // Attempt to decode the JSON
    json_decode($jsonString, ,,);
    
    // Check for JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return 'JSON is valid';
        case JSON_ERROR_DEPTH:
            return 'Error: Maximum stack depth exceeded';
        case JSON_ERROR_STATE_MISMATCH:
            return 'Error: Underflow or the modes mismatch';
        case JSON_ERROR_CTRL_CHAR:
            return 'Error: Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            // Attempt to find and report the syntax error
            return get_json_syntax_error_details($jsonString);
        case JSON_ERROR_UTF8:
            return 'Error: Malformed UTF-8 characters, possibly incorrectly encoded';
        default:
            return 'Error: Unknown JSON error occurred';
    }
}

// Function to find and display detailed syntax error
function get_json_syntax_error_details($jsonString) {
    // Perform a manual scan for common JSON syntax issues
    // Like missing commas, incorrect brackets, or bad structure
    $position = find_error_position($jsonString);
    
    if ($position !== null) {
        $line = get_line_number($jsonString, $position);
        $errorSnippet = get_json_error_snippet($jsonString, $position);
        return "Syntax error on line $line:\n$errorSnippet\n" . str_repeat('-', $position - max(0, strpos($errorSnippet, "\n"))) . "^\n";
    }

    return 'Error: Syntax error in JSON, but exact position could not be determined.';
}

// Helper function to calculate line number from a character position
function get_line_number($jsonString, $position) {
    return substr_count(substr($jsonString, 0, $position), "\n") + 1;
}

// Helper function to get a snippet around the error position for better context
function get_json_error_snippet($jsonString, $position, $contextLength = 40) {
    $start = max(0, $position - $contextLength);
    $length = min(strlen($jsonString) - $start, 2 * $contextLength);
    return substr($jsonString, $start, $length);
}

// Function to simulate finding error position (manual scan)
function find_error_position($jsonString) {
    // This could be a manual scan to look for common JSON issues (like misplaced commas, brackets, etc.)
    // For now, we'll assume it starts near the first unexpected character
    $tokens = preg_split('//u', $jsonString, -1, PREG_SPLIT_NO_EMPTY);
    
    $stack = [];
    $inString = false;
    for ($i = 0; $i < count($tokens); $i++) {
        $token = $tokens[$i];

        if ($token === '"') {
            $inString = !$inString;  // Toggle string context
        }

        if (!$inString) {
            if ($token === '{' || $token === '[') {
                array_push($stack, $token);
            } elseif ($token === '}' || $token === ']') {
                $expected = $token === '}' ? '{' : '[';
                if (array_pop($stack) !== $expected) {
                    return $i;  // Error position detected
                }
            }
        }
    }

    return null;  // No specific error found, fall back to basic message
}


$jsonString = '{
    "name": "Product"
    "data": [ 1000 , 2372 ]
}';

echo validate_json($jsonString);



exit;

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