<?php

    // Pfad zur JSON-Datei
    $jsonFile = 'charts/chart.json';

    // JSON-Datei einlesen
    $jsonData = file_get_contents($jsonFile);
    $charts = json_decode($jsonData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Fehler beim Lesen der JSON-Datei. Bitte überprüfen Sie die Datei: ' . $jsonFile);
    }

    require_once('modules/mainfunctions.php');
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts mit Chart.js</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            max-width: 600px;
            margin: 20px auto;
        }
    </style>
</head>
<body class="charts">

<div class="container mt-5">
    
    <?php foreach ($charts as $chartSection): ?>

        <?php if (isset($chartSection['section'])): 
            // Wir sind in einer zu rendernden Section
            // Und rendern z.B. ein Chart...
            $blnHasChart = isset($chartSection['section']['chart']);
            $blnHasImage = isset($chartSection['section']['image']);

            if ( !$blnHasChart )
            {
                // Kein Chart vorhanden, also formatieren wir Text und Bild anders:
                if ( $blnHasImage ) {
                    // Kein Chart, aber ein Bild vorhanden
                    // Wir rendern also ein Bild mit Text
                    $textClass = "col-md-8 float-right align-items-center";
                    $imageClass = "col-md-4 float-left";
                } else {
                    // Kein Chart und kein Bild vorhanden
                    // Wir rendern also nur Text
                    $textClass = "col-md-12";
                    $imageClass = "";
                }
            } else {
                // Ein Chart ist vorhanden
                // Wir rendern also ein Chart mit Text
                $textClass = "col-md-12";
                $imageClass = "";
            }
            ?>
            <div class="card mb-4">
                <div class="card-body <?php echo $chartSection['section']['class']??""; ?>">
                    <!-- Card render Start -->
                    <?php
                        // Einstellungen und Elemente für die Section laden und platzieren:

                        // Bilder:
                        $imageURL = $chartSection['section']['image'] ?? null;
                        $imageAltText = $chartSection['section']['image-alt'] ?? null;
                        $imageClass = $chartSection['section']['image-class'] ?? "img-fluid";

                        if ( isset($imageURL) ) {
                            // Bild vorhanden, also Text links und Bild rechts
                            // und Text sowie Bild vertikal zentriert:
                            $classJustifyContentToTheMiddle = "d-flex align-items-center justify-content-between";
                        } else {
                            $classJustifyContentToTheMiddle = "";
                        }
                    ?>
                    <div class="row <?php echo $classJustifyContentToTheMiddle; ?>">
                        <?php if (isset($imageURL)): ?>
                            <div class="col <?php echo $imageClass; ?>">
                                <img src="<?php echo $imageURL; ?>"
                                        class="col-md-12"
                                        alt="<?php echo($imageAltText); ?>"
                                >
                            </div>
                        <?php endif; ?>                        
                        <div class="col <?php echo($textClass)??""; ?>"
                        >
                            <h2 class="card-title "><?php echo $chartSection['section']['title']??""; ?></h2>
                            <h4 class="card-text "><?php echo $chartSection['section']['desc']??""; ?></h4>
                            
                           


                        </div>
                    </div>

                    <?php if ($blnHasChart): ?>
                        <?php $chartId = uniqid('chart-'); ?>
                        <canvas id="<?php echo $chartId; ?>"></canvas>
                    <?php endif; ?>

                    <?php                        
                        // Buttons:
                        $buttons = $chartSection['section']['button'] ?? null;
                        $blnQuestionHasButtons = false;
                    ?>
                    <?php if (isset($buttons)): ?>
                            <?php foreach ($buttons as $index => $button): 
                                $blnQuestionHasButtons = true;
                                $buttonClass = $button['class'] ?? "btn-primary";
                                ?>
                                <div class="form-button">
                                    <button class="btn <?php echo $buttonClass; ?>" 
                                            type="submit"
                                            name="button<?php echo $index; ?>"
                                            id="button<?php echo $index; ?>"
                                            onclick="window.location.href='<?php echo $button['url']; ?>';"
                                    >
                                        <?php echo $button['label']; ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
            <?php if ( isset($chartSection['section']['chart']) ): ?>
                <script>
                
                    var ctx = document.getElementById('<?php echo $chartId; ?>').getContext('2d');
                    var chartData = <?php echo json_encode($chartSection['section']['chart']['data']); ?>;
                    var chartType = "<?php echo $chartSection['section']['chart']['type']; ?>";
                    var ChartTitle = "<?php echo str_replace(
                                                '"', '\"', 
                                                ( $chartSection['section']['chart']['title'] ?? "" )
                                            ); 
                                    ?>";
                    var blnChartDisplay = ChartTitle.length > 0 ? true : false;

                    console.log(chartData);
                    console.log(chartType);

                    console.table(chartData); // Debugging chartData
                    
                    // Chart.js Initialisierung
                    var ctx = document.getElementById('<?php echo $chartId; ?>').getContext('2d');
                        var myChart = new Chart(ctx, {
                            type: chartType,
                            data: chartData,
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                    },
                                    title: {
                                        display: blnChartDisplay,
                                        text: ChartTitle,
                                    }
                                }
                            }
                        });
                
                </script>
            <?php endif; ?>

        <?php endif; ?>

        <?php
            // Rendern von hier möglichen Elementen im Root:
            // Hintergrundbild:
            $backgroundImage = $chartSection['background-image'] ?? null;
            $backgroundClass = $chartSection['background'] ?? null;

            // Logo:
            $logo = $chartSection['logo'] ?? null;

            // Titel:
            $title = $chartSection['title'] ?? null;
            // Class des übergeordneten Titels:
            $titleClass = $chartSection['class'] ?? "text-center";

            // CI Farben:
            $primaryColor = $chartSection['ci-color'] ?? null;
            if ($primaryColor != null) : ?>
                <style>
                    :root {
                        --ci-primary-color: <?php echo $primaryColor; ?>;
                        --dark-blend-color: <?php echo hexToRgba($primaryColor, 0.6); ?>;
                    }
                </style>
            <?php endif;
            


        ?>

        <?php if ($backgroundImage != ""): ?>
            <div class="survey background-image <?php echo $backgroundClass; ?>" 
                    style="background-image: url('<?php echo $backgroundImage; ?>');">
            </div>
        <?php endif; ?>

        <?php if ($logo != ""): ?>
            <div class="survey logo">
                    <img src="<?php echo $logo; ?>" 
                            class="img-fluid col-md-3"
                            alt="Logo"
                    >
            </div>
        <?php endif; ?>

        <?php if ($title != ""): ?>
            <div>
                <h1 class="mb-4 <?php echo($titleClass); ?>">
                    <?php echo $title; ?>
                </h1>
            </div>
        <?php endif; ?>

    <!-- next session !-->
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>

