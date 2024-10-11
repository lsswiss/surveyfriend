<?php
session_start();

// Survey laden
$survey = json_decode(file_get_contents('survey.json'), true);

// Aktuelle Frage bestimmen
$current_question = isset($_SESSION['current_question']) ? $_SESSION['current_question'] : 0;
$answers = isset($_SESSION['answers']) ? $_SESSION['answers'] : [];

// Weiter oder Zurück
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['next'])) {
        // Antwort speichern
        $answers[$current_question] = $_POST['answer'] ?? null;
        $_SESSION['answers'] = $answers;

        // Zur nächsten Frage
        if ($current_question < count($survey) - 1) {
            $_SESSION['current_question'] = ++$current_question;
        }
    } elseif (isset($_POST['prev'])) {
        // Zurück zur vorherigen Frage
        if ($current_question > 0) {
            $_SESSION['current_question'] = --$current_question;
        }
    }
}

// Berechnen der Gesamtpunkte
if ($current_question == count($survey)) {
    $total = 0;
    foreach ($answers as $key => $answer) {
        if (is_numeric($answer)) {
            $total += $answer;
        }
    }

    // Zusammenfassung anzeigen
    echo "<div class='container mt-5'><h2>Zusammenfassung</h2>";
    foreach ($survey as $index => $question) {
        echo "<p><strong>Frage: </strong>{$question['q']}</p>";
        echo "<p><strong>Antwort: </strong>{$answers[$index]}</p>";
    }
    echo "<h3>Gesamtpunkte: $total</h3></div>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo $question['q']; ?></h2>
        <?php if (!empty($question['desc'])): ?>
            <p><?php echo $question['desc']; ?></p>
        <?php endif; ?>

        <form method="POST">
            <?php if (isset($question['a']['option'])): ?>
                <?php foreach ($question['a']['option'] as $option): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answer" id="option<?php echo $option['value']; ?>" value="<?php echo $option['value']; ?>" required>
                        <label class="form-check-label" for="option<?php echo $option['value']; ?>">
                            <?php echo $option['label']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php elseif (isset($question['a']['range'])): ?>
                <label for="rangeInput"><?php echo $question['a']['label']; ?></label>
                <input type="number" id="rangeInput" name="answer" class="form-control" min="<?php echo $question['a']['range']['min']; ?>" max="<?php echo $question['a']['range']['max']; ?>" step="<?php echo $question['a']['range']['step']; ?>" required>
            <?php elseif (isset($question['a']['fields'])): ?>
                <?php foreach ($question['a']['fields'] as $field): ?>
                    <div class="mb-3">
                        <label for="field<?php echo $field['text']['label']; ?>" class="form-label"><?php echo $field['text']['label']; ?></label>
                        <input type="text" class="form-control" id="field<?php echo $field['text']['label']; ?>" name="answer" <?php echo $field['text']['required'] ? 'required' : ''; ?>>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="mt-3">
                <?php if ($current_question > 0): ?>
                    <button type="submit" name="prev" class="btn btn-secondary">Zurück</button>
                <?php endif; ?>
                <button type="submit" name="next" class="btn btn-primary">Weiter</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
