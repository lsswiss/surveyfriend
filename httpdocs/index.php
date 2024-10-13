<?php
// Initialwerte:
$languageISO = "de-DE";

// Survey-Datei:
$fileSurvey = "survey.json";
//$fileSurvey = 'hotel-survey.json';

require_once('lib/mainfunctions.php');

// Start der survey-Sitzung:
session_start();

// Survey-Datei einlesen:
$survey = json_decode(file_get_contents($fileSurvey), true);

// echo print_r($survey);

// Aktuelle Frage bestimmen
$current_question = isset($_SESSION['current_question']) ? $_SESSION['current_question'] : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];

// Weiter oder Zurück
if (isset($_GET['prev'])) {
    // Zurück zur vorherigen Frage
    if ($current_question > 0) {
        $_SESSION['current_question'] = --$current_question;
    }
    $_GET['prev'] = 0;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['startOver'])) {
        // Neustart
        session_destroy();
        header('Location: index.php');
        exit();
    
    } elseif (isset($_POST['next'])) {
        // Antwort speichern
        $answers[$current_question] = $_POST['answer'] ?? null;
        $_SESSION['answers'] = $answers;

        //debug:
        //echo "a:".print_r($_POST['answer']  ?? "null"); 

        // Zur nächsten Frage
        if ($current_question < count($survey) ) {
            $_SESSION['current_question'] = ++$current_question;
        }
    }
}

// Berechnen der Gesamtpunkte
if ($current_question == count($survey)) {
    $total = 0;
    foreach ($answers as $key => $answer) {
        if (isset($answer) && is_array($answer)) {
            // Addiere die Punkte für jede gewählte Option
            // Multi-Optionen (z.B. Checkboxen, multioption)
            foreach ($answer as $value) {
                $total += (float)$value;
                echo "total++:". $value;
            }
        } else
        {   // Addiere die Punktzahl für die Antwort, falls sie numerisch ist
            if (is_numeric($answer)) {
                $total += $answer;
            }
        }
    }

    // Zusammenfassung anzeigen
    echo "<div class='container mt-5'><h2>Zusammenfassung</h2>";
    $total = 0; // Initialisiere die Gesamtsumme

    foreach ($survey as $index => $question) {
        echo "<p><strong>Frage: </strong>{$question['q']}</p>";

        if (isset($answers[$index]) && is_array($answers[$index])) {
            // Zeige die Punktzahl für die Antwort an, falls es ein Array
            // hat mit mehreren Werten (z.B. Checkboxen, multioption)
            $points = 0;
            foreach ($answers[$index] as $value) {
                $points += (float)$value; // Addiere die Punkte für jede gewählte Option
            }
            echo "<p><strong>Punkte: </strong>$points</p>";
            $total += $points; // Addiere die Punkte zur Gesamtsumme
        } else {
            // Zeige die Punktzahl für die Antwort an, falls sie numerisch ist
            if (isset($answers[$index]) && is_numeric($answers[$index])) {
                echo "<p><strong>Punkte: </strong>{$answers[$index]}</p>";
                $total += $answers[$index]; // Addiere die Punktzahl zur Gesamtsumme
            }
        }        

    }

    echo "<h3>Ihre erreichte Punktzahl: $total</h3></div>";

    // Wieder von vorne beginnen:
    $_SESSION['current_question'] = -1;

    ?>
        <form method="POST">
            <button type="submit" name="startOver" class="btn btn-primary">Neu beginnen</button>
        </form>
    <?php

    shutdown();
    session_destroy();
    exit();
}

// Aktuelle Frage
$question = $survey[$current_question];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey</title>
    <?php libraries(); ?>
    
</head>
<body class="survey">
    <?php
        // Init:
        $blnQuestionHasButtons = false;

        $backgroundImage = $question['background-image'] ?? "";
    ?>

    <?php if ($backgroundImage != ""): ?>
        <div class="survey background-image" 
                style="background-image: url('<?php echo $backgroundImage; ?>');">
        </div>
    <?php endif; ?>

    <div class="survey container mt-5 roll-in">
        <h2><?php echo $question['q']; ?></h2>
        <?php if (!empty($question['desc'])): ?>
            <p><?php echo $question['desc']; ?></p>
        <?php endif; ?>

        <form method="POST" action="?next">

            <?php if (isset($question['a']['option'])): ?>
                <?php foreach ($question['a']['option'] as $index => $option): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer" id="option<?php echo $index; ?>" value="<?php echo $option['value']; ?>" required>
                        <label class="form-check-label" for="option<?php echo $index; ?>">
                            <?php echo $option['label']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            
            <?php elseif (isset($question['a']['multioption'])): ?>
                <?php foreach ($question['a']['multioption'] as $index => $multioption): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="answer[]" id="multioption<?php echo $index; ?>" value="<?php echo $multioption['value']; ?>" >
                        <label class="form-check-label" for="multioption<?php echo $index; ?>">
                            <?php echo $multioption['label']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>

            <?php elseif (isset($question['a']['button'])): ?>
                <?php foreach ($question['a']['button'] as $index => $button): 
                    $blnQuestionHasButtons = true;
                    $buttonClass = $button['class'] ?? "btn-primary";
                    ?>
                    <div class="form-button">
                        <button class="btn <?php echo $buttonClass; ?>" type="submit" name="next" id="button<?php echo $index; ?>" value="<?php echo $button['value']; ?>">
                            <?php echo $button['label']; ?>
                        </button>
                    </div>
                <?php endforeach; ?>

            <?php elseif (isset($question['a']['range'])): 
                $prefix = $question['a']['range']['prefix'] ?? "";
                $postfix = $question['a']['range']['suffix'] ?? $question['a']['range']['postfix'] ?? "";
                $format = $question['a']['range']['format'] ?? "";

                ?>
                <script>
                    var numberFormat = "<?php echo strtolower($format); ?>";
                    var languageISO = '<?php echo $languageISO ?? "de-DE"; ?>'
                    var preFix = "<?php echo str_replace('"', '\"', $prefix); ?>"
                    var postFix = "<?php echo str_replace('"', '\"', $postfix); ?>"
                </script>

                <label for="rangeInput"><?php echo $question['a']['label']; ?></label>
                <input type="range" id="rangeInput" name="answer" class="form-control range-slider" min="<?php echo $question['a']['range']['min']; ?>" max="<?php echo $question['a']['range']['max']; ?>" step="<?php echo $question['a']['range']['step']; ?>" required>
                <div class="value-display"><?php echo $question['a']['range']['min']; ?></div>
            
            <?php elseif (isset($question['a']['fields'])): ?>
                <?php foreach ($question['a']['fields'] as $field): ?>
                    <div class="mb-3">
                        <label for="field<?php echo $field['text']['label']; ?>" class="form-label"><?php echo $field['text']['label']; ?></label>
                        <input type="text" class="form-control" id="field<?php echo $field['text']['label']; ?>" name="answer[]" <?php echo $field['text']['required'] ? 'required' : ''; ?>>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="mt-3">
                <?php if ( !$blnQuestionHasButtons ) : ?>
                    
                    <?php if ($current_question > 0): ?>
                        <button type="button" name="prev"
                                onclick="window.location.href='?prev=1';"
                        class="btn btn-secondary">Zurück</button>
                    <?php endif; ?>

                    <button type="submit" name="next" class="btn btn-primary col-6 float-end">Weiter</button>

                <?php endif; ?>
            </div>
        </form>
    </div>

   <?php shutdown(); ?>
</body>
</html>