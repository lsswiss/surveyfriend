<?php

    function hexToRgba($hex, $alpha = 0.4) {
        // Entferne das Hash (#) Symbol, falls vorhanden
        $hex = ltrim($hex, '#');

        // Überprüfe, ob der HEX-Wert korrekt ist (6 oder 3 Zeichen)
        if (strlen($hex) === 6) {
            list($r, $g, $b) = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        } elseif (strlen($hex) === 3) {
            list($r, $g, $b) = [hexdec(str_repeat(substr($hex, 0, 1), 2)), hexdec(str_repeat(substr($hex, 1, 1), 2)), hexdec(str_repeat(substr($hex, 2, 1), 2))];
        } else {
            return null; // Ungültiger HEX-Wert
        }

        // Erstelle den rgba-Wert
        return "rgba($r, $g, $b, $alpha)";
    }