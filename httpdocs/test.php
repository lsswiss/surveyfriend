<?php
use function SurveyFriend\Results\runFunctionOnValue;

require_once('lib/mainfunctions.php');
include('lib/surveyfriend.php');

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