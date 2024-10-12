<?php
// Pfad zur JSON-Datei
$jsonFile = 'charts/chart.json';

// JSON-Datei einlesen
$jsonData = file_get_contents($jsonFile);
$charts = json_decode($jsonData, true);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts mit Chart.js</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            max-width: 600px;
            margin: 20px auto;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    
    <?php foreach ($charts as $chartSection): ?>

        <?php if (isset($chartSection['section'])): 
            // Wir sind in einer zu rendernden Section
            // Und rendern z.B. ein Chart...
            ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title"><?php echo $chartSection['section']['title']??""; ?></h2>
                    <p class="card-text"><?php echo $chartSection['section']['desc']??""; ?></p>
                    <?php $chartId = uniqid('chart-'); ?>
                    <canvas id="<?php echo $chartId; ?>"></canvas>

                    <?php
                        // Rendern von hier in der card möglichen Buttons:
                        $buttons = $chartSection['section']['button'] ?? null;
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

        <?php
            // Rendern von hier möglichen Elementen im Root:
            // Hintergrundbild:
            $backgroundImage = $chartSection['background-image'] ?? null;
            $title = $chartSection['title'] ?? null;
        ?>

        <?php if ($backgroundImage != ""): ?>
            <div class="survey background-image" 
                    style="background-image: url('<?php echo $backgroundImage; ?>');">
            </div>
        <?php endif; ?>

        <?php if ($title != ""): ?>
            <h1 class="text-center mb-4">
                <?php echo $title; ?>
            </h1>
        <?php endif; ?>

    <!-- next session !-->
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
