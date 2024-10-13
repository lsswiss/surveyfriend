/* Wert des Range-Sliders im gewünschten Format im Display Label anzeigen */
const rangeInput = document.getElementById('rangeInput');
if (rangeInput) {
    rangeInput.addEventListener('input', function() {
        var value = this.value;
        var formattedValue = value;
        var format = window.numberFormat;
        var languageISO = window.languageISO || 'de-DE'; // Default to 'en-US' if not defined
        var preFix = window.preFix;
        var postFix = window.postFix;

        if (format === "money" || format === "currency") {
            /* Es ist ein Geldbetrag im Slider (Format ist money oder currency):
                ->Formatiere den gewählten Range im Währungs-Format falls gewünscht:
            */                        
            formattedValue = new Intl.NumberFormat(languageISO, {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        }

        /* Präfix und Suffix hinzufügen */
        document.querySelector('.value-display').textContent = preFix + formattedValue + postFix;
        
        // alert(preFix + formattedValue + postFix);
    });
}


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