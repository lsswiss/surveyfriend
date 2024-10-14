<?php
namespace SurveyFriend\Results;

use \EvalMath;
use \TypeError;

// only used for Eval() function, not recommended:
// use \ParseError;
// use \Exception;
// use \DivisionByZeroError;

// MathEval class is used to evaluate mathematical expressions
// it is safer then using eval() function:
include('matheval.class.php');

function q($questionNr) {
    $answers = $_SESSION['answers'] ?? [];
    $answer = $answers[$questionNr] ?? null;
    if ( is_array($answer) ) {
        $answer = array_sum($answer);
    }
    return $answer;
}

/**
 * Rechnet das Resultat aus des Surveys, mit Formeln und schreibt das Resultat der Formel in
 * $_SESSION["result"][$$formulaName].
 * 
 * Die Formeln können Variablen wie Q1, Q2, Q3, etc... enthalten, welche sich auf die Punktzahlen
 * der beantworteten Fragen beziehen.
 *
 * @param  string $formulaName
 * @param  string $formula
 * @return string
 * @author Urs Langmeier
 */
function getVal($formulaName, $formula) {
    global $gFunctions;

    // Extract variables like Q1, Q2, etc... from the formula:
    $originalFormula = $formula;
    preg_match_all('/Q\d+/', $formula, $matches);
    $variables = array_unique($matches[0]);

    //debug($variables);

    // Replace variables with their values from the session
    foreach ($variables as $variable) {
        if (isset($_SESSION['result'][$variable])) {
            $formula = str_replace($variable, $_SESSION['result'][$variable], $formula);
        } else {
            // Handle the case where the variable is not set in the session
            $_SESSION["result"][$variable] = q(intval(substr($variable, 1)));
            $formula = str_replace($variable, $_SESSION['result'][$variable], $formula);
            //debug($_SESSION["result"]);
        }
    }

    // Evaluation of the formula:
    // --------------------------

    // ula, 13.10.2024
    // Evaluate the formula using mathEval class, this is safer then using eval() function
    $m = new EvalMath;

    // Cut the functions out of the formula, so that the formula only contains the formula
    // No round, ceil, floor, sin, cos, tan, sqrt, log, exp, abs, min, max, pow, rand
    $gFunctions = []; // Reset the global functions array

    $formula = cutFunctions($formula);

    // Evaluate the formula:
    $m->suppress_errors = true; // do not print errors, catch them!
    $result = $m->evaluate($formula);

    if ($m->last_error) {
        // Handle errors in the formula
        $strErr = 'Error in formula `'.$formulaName.'`'
                    . '\nFormula: '.$formula.
                    '\n'. $m->last_error;
        
        $_SESSION["result"][$formulaName] = $strErr;
        consoleLog($strErr, true);
        
    } else {
        // Attach and run the previously cutted function to the formula...
        $result = runFunctionOnValue($result);

        // Save the result in the session
        $_SESSION["result"][$formulaName] = $result;

        // Für debug-Zwecke speichern wir die Formel und das Resultat:
        $_SESSION["formulas"][$originalFormula] = $formula;

        // Für debug-Zwecke im Browser loggen:
        consoleLog('Result of formula `'.$formulaName. '`:'
                    .'\nFormula: '.$originalFormula
                    .'\nEvaluates to: '.$formula
                    .'\nResult: ' . $result, false);

        return $result;
    }
    
    // // Evaluate the formula with Eval (not recommended, but works)
    // try {
    //     eval('$result = ' . $formula . ';');
    //     echo 'eval of '.$formula.':'.$result. " KKKK ";
    //     $_SESSION["result"][$formulaName] = $result;

    //     // Für debug-Zwecke speichern wir die Formel und das Resultat:
    //     $_SESSION["formulas"][$originalFormula] = $formula;

    //     // Für debug-Zwecke im Browser loggen:
    //     consoleLog('Result of formula `'.$formulaName. '`:'
    //                 .'\nFormula: '.$originalFormula
    //                 .'\nEvaluates to: '.$formula
    //                 .'\nResult: ' . $result, false);
        
    // } catch (ParseError $e) {
    //     // Handle parse errors in the formula:
    //     consoleLog('Error in formula `'.$formulaName. '`:\nFormula: '.$formula.'\n' . $e->getMessage(), true);
    //     $_SESSION["result"][$formulaName] = null;

    // } catch (DivisionByZeroError $e) {
    //     // Handle errors, including division by zero and other runtime errors
    //     consoleLog('Error in formula `'.$formulaName. '`:\nFormula: '.$formula.'\n' . $e->getMessage(), true);
    //     $_SESSION["result"][$formulaName] = null;

    // } catch (Exception $e) {
    //     // Handle other exceptions
    //     consoleLog('Error in formula `'.$formulaName. '`:\nFormula: '.$formula.'\n' . $e->getMessage(), true);
    //     $_SESSION["result"][$formulaName] = null;
    // }

}

/**
 * Schneidet die Funktionen aus der Formel heraus, damit die Formel nur noch die Werte enthält.
 * 
 * Die Funktionen werden in einem globalen Array $gFunctions gespeichert.
 * 
 * Aus der Formel: round(12 * 50, 2) wird so nur noch "12 * 50"
 * 
 * @param string $formula
 * @return string       Die Formel ohne Funktionen wie:
 *                      'abs', 'round', 'floor', 'ceil', 'rand', 'number_format', 'money'
 */
function cutFunctions($formula) {
    global $gFunctions;

    // Initialisiere mit leerem Array:
    $gFunctions = [];

    $functions = ['sin', 'cos', 'tan', 'sqrt', 'log', 'exp', 'abs'
                , 'round', 'floor', 'ceil', 'min', 'max', 'pow', 'rand'
                , 'intval', 'fmod'
                , 'number_format', 'money'];
    foreach ($functions as $function) {
        if (strpos($formula, $function) !== false) {
            
            $formula = getPartOfString($formula, $function, "");
            $formula = getPartOfString_OpenToClose($formula, "(", ")");
            $param = getPartOfString($formula, ",", "");
            $formula = trim(getPartOfString($formula, "", ","));

            // Beim Parameter erste und letzte Anführungszeichen entfernen:
            $param = trim($param);
            $param = trim($param, '"');
            $param = trim($param, "'");
            
            // consoleLog('cutFunctions: '.$function.'('.$param.')', false);
            
            $gFunctions["function"] = $function;
            $gFunctions["formula"] = $formula;
            $gFunctions["param"] = $param;

            // Debug:
            //debug("cut functions: ");
            //debug($gFunctions);

            break;

        }
    }
    return $formula;    
}

/**
 * Führt eine zuvor ausgeschnittene Funktion auf einen Wert aus.
 * 
 * Es werden nur die zuvor mit ->cutFunctions() ausgeschnittenen Funktionen unterstützt.
 * 
 * Die aktuell unterstützten Funktionen sind:
 * 'abs', 'round', 'floor', 'ceil', 'rand', 'number_format', 'money'
 * 
 * @see cutFunctions()
 * 
 * @param string $value   Der Wert, auf den die Funktion angewendet wird.
 * 
 * @param string $formula
 */
function runFunctionOnValue($value) {
    global $gFunctions;

    if (isset($gFunctions["function"])) {

        // Funktionsname vom zuvor mit ->cutFunctions() erstellten Array holen:
        $functionName = $gFunctions["function"];

        // Debug:
        // debug("add function: ");
        // debug($gFunctions);

        try {
            if ( $gFunctions["param"] != null ) {
                // Function with param:
                $formula = $gFunctions["formula"];
                $param = $gFunctions["param"];
                $result = $functionName($value, $param);

                //consoleLog('Function with param: '.$functionName.'('.$value.', '.$param.')', false);
                
            } else {
                // Function without param:
                $formula = $gFunctions["formula"];
                $result = $functionName($value);

                //consoleLog('Function w/o param: '.$functionName.'('.$value.')', false);
                
            }
            return $result;
        } catch (TypeError $e) {
            // Handle type errors
            consoleLog('Error in function `'.$functionName. '`:\nFormula: '.$formula.'\n' . $e->getMessage(), true);
            return null;
        }
    } else {
        // Keine vorher ausgeschnittenen Funktionen gefunden,
        // also einfach den Wert zurückgeben:
        return $value;
    }
}

/**
 * Kalkuliert die Resultate des Surveys und speichert sie in $_SESSION["result"].
 * Alle im charts.json unter "results" definierten Formeln werden berechnet und
 * in $_SESSION["result"] gespeichert.
 *
 * @return str 
 * @author Urs Langmeier
 */
function calculateResults($chartsJsonFile)
{
    // JSON-Datei einlesen
    $jsonData = file_get_contents($chartsJsonFile);

    // Da wir Platzhalter in der JSON-Datei haben, ersetzen wir diese durch Zahlen-Werte
    // welche keine Fehler bei der JSON-Dekodierung verursachen...
    $jsonData = preg_replace('/\$(\w+)/', '12345678', $jsonData);

    $charts = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $msg = 'Fehler beim Lesen der JSON-Datei <strong>`'. $jsonFile.'`</strong>. Bitte validieren Sie die Datei unter <strong><a href="https://jsonlint.com">https://jsonlint.com</a></strong>. Fehler: <strong>'. json_last_error_msg()."</strong>";
        consoleLog(strip_tags($msg));
        die($msg);
    }

    // Unter dem Schlüssel "results" befinden sich die Formeln, die ausgerechnet werden sollen.
    // Die Formeln sind in der JSON-Datei so definiert:
    // { 
    //     "results": {
    //         "yearlySavings": "money(12 * (( Q1 + Q4 + Q2 + ( Q3 * Q1 * 0.2 )) - (( Q3 * 50 ) + ( Q4 * 0.1 ))), 0)",
    //         "youPayMonthly": "money(((Q1)*Q3*0.2)+(Q1+Q2), 0)",
    //         "youWouldPayDaily": "money((Q3*50)*12/365, 0)",
    //         "youWouldPayMonthly": "money((Q3*50)*12/12, 0)"
    //     }
    // }
    // Nach dem Schlüssel "results" suchen:
    foreach ($charts as $chartSection) {
        if (isset($chartSection['results'])) {
            // Wir sind in einer Sektion mit Resultaten, die ausgerechnet werden sollen...
            //
            // ->Rechne diese Resultate aufgrund der angegebenenen Formeln aus:
            //   Die für die Resultate benötigten Formeln können Variablen wie Q1, Q2, Q3, etc...
            //   enthalten, welche sich auf die Punktzahlen der beantworteten Fragen beziehen.
            $results = $chartSection['results'];

            // Jede einzelne Formel ausrechnen:
            foreach($results as $formulaName => $calculation)
            {
                // Rechne die Formel aus... und speichere das Resultat in der Session
                // Merke: getval() speichert das Resultat in $_SESSION["result"][$formulaName]
                //        Wir müssen also hier nichts mehr weiter speichern...
                $value = getVal($formulaName, $calculation);
            }
        }
    }
}