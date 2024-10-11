// const sliders = document.querySelectorAll(".range-slider");
// const valueDisplays = document.querySelectorAll(".value-display");

// sliders.forEach((slider, index) => {
//     slider.oninput = function() {
//         valueDisplays[index].textContent = this.value;
//     }
// });

/* Wert des Range-Sliders im gewünschten Format im Display Label anzeigen */
const rangeInput = document.getElementById('rangeInput');
if (rangeInput) {
    rangeInput.addEventListener('input', function() {
        var value = this.value;
        var formattedValue = value;
        var format = window.numberFormat;
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