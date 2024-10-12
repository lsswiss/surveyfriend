<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umfrageresultat</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Canvas für das Diagramm -->
    <canvas id="myChart" width="400" height="400"
        style="max-height: 300px;"
        ></canvas>

    <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar', // Ändere 'bar' zu 'pie' für ein Kuchendiagramm oder 'line' für ein Liniendiagramm
            data: {
                labels: ['Programmierer', 'Buchhalter', 'Sekretäre', 'Lehrer', 'Fluglehrer', 'Piloten'],
                datasets: [{
                    label: 'Gehalt in Tsd. €',
                    data: [12, 19, 3, 5, 2, 3],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,  // Gitterlinien anzeigen oder ausblenden
                            color: 'rgba(0, 0, 0, 0.1)',  // Gitterlinienfarbe
                            lineWidth: 2  // Dicke der Gitterlinien
                        },
                        ticks: {
                            color: 'blue',  // Farbe der Achsenwerte
                            font: {
                                size: 14  // Größe der Achsenbeschriftung
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false  // X-Achse Gitterlinien ausblenden
                        }
                    }
                }
                ,  animation: {
                    duration: 200,  // Länge der Animation in ms
                    easing: 'easeInBounce',  // Art der Animation
                }
                ,tooltip: {
                    enabled: true,  // Tooltip aktivieren/deaktivieren
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',  // Hintergrundfarbe des Tooltips
                    titleColor: '#ffffff',  // Textfarbe des Titels
                    bodyColor: '#ffcc00',  // Textfarbe des Inhalts
                    padding: 10,  // Abstand im Tooltip
                    cornerRadius: 4  // Abgerundete Ecken
                }
                ,responsive: true  // Diagramm passt sich der Fenstergröße an
                ,maintainAspectRatio: true  // Seitenverhältnis des Diagramms ignorieren
                ,plugins: {
                    legend: {
                        display: false,  // Legende anzeigen oder ausblenden
                        position: 'bottom',  // Position: 'top', 'bottom', 'left', 'right'
                        labels: {
                            boxWidth: 20,  // Größe des Farbquadrats
                            padding: 10,   // Abstand zwischen den Elementen
                        }
                    }
                }

                
            }
        });
    </script>


<canvas id="myPieChart" width="400" height="400"></canvas>

    <script>
        // Schritt 2: Erstellen des Kuchendiagramms
        var ctx = document.getElementById('myPieChart').getContext('2d');
        const myPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Ihr Unternehmen', 'Unternehmen ohne Software', 'Unternehmen mit Software', 'Unternehmen mit SaaS-Lösungen'],
                datasets: [{
                    label: 'Umsatz in Tsd.',
                    data: [12, 19, 3, 50]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Kuchendiagramm Beispiel'
                    }
                }
            }
        });
    </script>


<canvas id="myPieChart2" width="400" height="400"></canvas>
<script>
    // JSON-Daten
    const data = {
        "section": {
            "title": "So viel Geld sparst Du im Vergleich zum Mitbewerb",
            "desc": "Andere Unternehmen geben im Vergleich zu dir aus...",
            "chart": {
                "type": "pie",
                "data": {
                    "labels": ["Ihr Unternehmen", "Unternehmen ohne SaaS-Lösungen"],
                    "datasets": [{
                        "label": "Kosten monatlich",
                        "data": [500, 4500],
                        "backgroundColor": ['#36A2EB', '#FF6384'],
                    }]
                }
            }
        }
    };

    // Chart.js Initialisierung
    var ctx = document.getElementById('myPieChart2').getContext('2d');
    const myPieChart2 = new Chart(ctx, {
        type: data.section.chart.type,
        data: data.section.chart.data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                title: {
                    display: true,
                    text: data.section.title,
                }
            }
        }
    });
</script>

</body>
</html>
