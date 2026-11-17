/* Wert des Range-Sliders im gewünschten Format im Display Label anzeigen */

/* Einen Slider-Wert je nach gewünschtem Format formatieren: */
function formatRangeValue(value) {
    var format = window.numberFormat;
    var languageISO = window.languageISO || 'de-DE'; // Default to 'de-DE' if not defined

    if (format === "money" || format === "currency") {
        /* Es ist ein Geldbetrag im Slider (Format ist money oder currency):
            -> Formatiere den gewählten Range im Währungs-Format:
        */
        return new Intl.NumberFormat(languageISO, {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    if (format === "percent") {
        /* Prozentwerte mit bis zu 2 Dezimalstellen anzeigen: */
        return new Intl.NumberFormat(languageISO, {
            style: 'decimal',
            maximumFractionDigits: 2
        }).format(value);
    }

    /* Alle anderen Formate unverändert lassen: */
    return value;
}

/* Den aktuellen Slider-Wert formatiert inkl. Präfix und Suffix anzeigen: */
function displayRangeValue() {
    var rangeInput = document.getElementById('rangeInput');
    if (!rangeInput) return;

    var preFix = window.preFix || '';
    var postFix = window.postFix || '';

    document.querySelector('.value-display').textContent = preFix + formatRangeValue(rangeInput.value) + postFix;
}

document.addEventListener('DOMContentLoaded', function() {
    var rangeInput = document.getElementById('rangeInput');
    if (rangeInput) {
        /* Initialwert bereits formatiert anzeigen: */
        displayRangeValue();

        /* Bei jeder Änderung neu formatieren: */
        rangeInput.addEventListener('input', displayRangeValue);
    }
});


 /* Alle Werte im jeweiligen Formular auf dem Server abspeichern...
    -> Sobald sich ein Wert ändert, wird dieser in der Session gespeichert
*/
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, textarea, select');

    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // Eingegebener Wert in Session speichern:
            var value = {  
                            "type": this.type,
                            "id": this.id,
                            "value": this.value,
                            "checked": this.checked
                        };
            
            // In Session abspeichern:
            httpsPost_JSON('save.php', value, true);

        });
    });
});