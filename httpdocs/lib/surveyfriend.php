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
 * @param  string $formulaName
 * @param  string $formula
 * @return string
 * @author Urs Langmeier
 */
function getVal($formulaName, $formula) {
    // Extract variables from the formula
    $originalFormula = $formula;
    preg_match_all('/Q\d+/', $formula, $matches);
    $variables = array_unique($matches[0]);

    //debug($variables);

    // Replace variables with their values from the session
    foreach ($variables as $variable) {
        echo 'var:'.$variable;
        if (isset($_SESSION['result'][$variable])) {
            $formula = str_replace($variable, $_SESSION['result'][$variable], $formula);
        } else {
            // Handle the case where the variable is not set in the session
            $_SESSION["result"][$variable] = q(intval(substr($variable, 1)));
            $formula = str_replace($variable, $_SESSION['result'][$variable], $formula);
            debug($_SESSION["result"]);
        }
    }

    // Evaluation of the formula:
    // --------------------------

    // ula, 13.10.2024
    // Evaluate the formula using mathEval class, this is safer then using eval() function
    $m = new EvalMath;

    // Cut the functions out of the formula, so that the formula only contains the formula
    // No round, ceil, floor, sin, cos, tan, sqrt, log, exp, abs, min, max, pow, rand
    if ( isset($gFunctions) ) unset($gFunctions);
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
        echo 'eval of '.$formula.':'.$result. " KKKK ";
        $_SESSION["result"][$formulaName] = $result;

        // Für debug-Zwecke speichern wir die Formel und das Resultat:
        $_SESSION["formulas"][$originalFormula] = $formula;

        // Für debug-Zwecke im Browser loggen:
        consoleLog('Result of formula `'.$formulaName. '`:'
                    .'\nFormula: '.$originalFormula
                    .'\nEvaluates to: '.$formula
                    .'\nResult: ' . $result, false);        
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
    
    // Important:
    // In case we do not have a function in our formula,
    // we do not want to provide the global functions array.
    // Therefore we unset it here for initialization:
    if ( isset($gFunctions) ) unset($gFunctions);

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

            $gFunctions["function"] = $function;
            $gFunctions["formula"] = $formula;
            $gFunctions["param"] = $param;

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

        try {
            if ( $gFunctions["param"] != null ) {
                // Function with param:
                $formula = $gFunctions["formula"];
                $param = $gFunctions["param"];
                $result = $functionName($value, $param);
                
            } else {
                // Function without param:
                $formula = $gFunctions["formula"];
                $result = $functionName($value);
                
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