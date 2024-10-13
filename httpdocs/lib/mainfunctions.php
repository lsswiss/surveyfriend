<?php

    /**
     * Inkludiert die Libriaries für die Webseite die spät geladen werden sollen.
     *
     * @return str 
     * @author Urs Langmeier
     * 
     * Diese Funktion muss am Ende des Body-Tags im HTML-Dokument aufgerufen werden.
     * 
     */
    function librariesInclude_LateLoad() {

        // Interne Hauptfunktionen (Mainfunctions):
        $libURL = "lib/mainfunctions.js";
        echo '<script src="'.$libURL.'"></script>';

        // Bootstrap:
        // Neu in ->requireLibrary() hinzugefügt und somit bei ->librariesInclude() geladen.
        //$libURL = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js";
        //echo '<script src="'.$libURL.'"></script>';

        // Aktuelles Skript:
        // -> Falls eine .js-Datei mit dem aktuellen PHP-Skriptnamen
        //    existiert...
        $currentScript = basename($_SERVER['SCRIPT_FILENAME'], '.php') . '.js';

        if (file_exists($currentScript)) {
            echo '<script src="'.$currentScript.'"></script>';
        } else {
            consoleLog($currentScript." existiert nicht!");
        }

        // Module, die zum später laden hinzugefügt wurden
        // werden hier geladen:
        global $globalRequiredLibrariesToLoadLater_JS;
        if (!empty($globalRequiredLibrariesToLoadLater_JS)) {
            foreach ($globalRequiredLibrariesToLoadLater_JS as $jsURL) {
                echo '<script src="'.$jsURL.'"></script>';
            }
        }
    
    }

    /**
     * Inkludiert die Libriaries für die Webseite die früh geladen werden sollen.
     *
     * @return string
     * @param string $strAdditionalModules Zusätzliche Module, die geladen werden sollen
     *  (durch Kommas getrennt)
     * 
     *  Beispiel: "animate,font-awesome"
     * 
     * @return void
     * @author Urs Langmeier
     * 
     * Diese Funktion muss im Head-Tag des HTML-Dokuments aufgerufen werden.
     * Vergessen Sie nicht, die Funktion librariesInclude_LateLoad() am Ende
     * des Body-Tags aufzurufen.
     * 
     * @see librariesInclude_LateLoad()
     * 
     * Diese Funktion lädt die folgenden Libraries:
     * - Bootstrap
     * - Aktuelles Stylesheet (falls eine .css-Datei mit dem aktuellen PHP-Skriptnamen existiert)
     * - Zusätzliche Module, die nicht von allen Seiten benötigt werden
     * 
     */
    function librariesInclude($strAdditionalModules = "") {

        // Bootstrap:
        requireLibrary("bootstrap5.3");

        // Aktuelles Stylesheet (falls eine .css-Datei mit dem aktuellen PHP-Skriptnamen
        // existiert):
        $currentStylesheet = basename($_SERVER['SCRIPT_FILENAME'], '.php') . '.css';
        if (file_exists($currentStylesheet)) {
            echo '<link href="'.$currentStylesheet.'" rel="stylesheet">';
        } else {
            consoleLog($currentStylesheet." existiert nicht!");
        }

        // Zusätzliche Module die nicht von allen Seiten benötigt werden:
        // -> Diese werden nur geladen, wenn sie benötigt werden.
        // -> Die Module werden durch Kommas getrennt.
        // -> Beispiel: "animate,font-awesome"
        // -> Die Module müssen in der Funktion requireLibrary() definiert sein.
        // -> Die Module werden in der Reihenfolge geladen, wie sie hier definiert sind.
        // -> Die Module werden nur geladen, wenn sie nicht bereits geladen wurden.
        if ($strAdditionalModules != "") {
            $arModules = explode(",", $strAdditionalModules);
            foreach ($arModules as $module) {
                $module = trim($module);
                if ($module != "") {
                    consoleLog("Module: ".$module);
                    requireLibrary($module);
                }
            }
        }

    }

    /**
     * Inkludiert eine Library in die Webseite.
     *
     * @param  string $libName Der Name der Library
     * @return void
     * 
     * Nicht alle Libraries werden sofort geladen, sondern manche erst später.
     * Dies ist der Fall, wenn die Library nicht sofort benötigt wird.
     * Dies wird dann durch die Funktion librariesInclude_LateLoad() gemacht.
     * Der Grund dafür ist, dass so die Webseite schneller geladen wird.
     * 
     * @see librariesInclude_LateLoad()
     */
    function requireLibrary($libName) {

        global $globalRequiredLibraries;
        if (!isset($globalRequiredLibraries)) {
            $globalRequiredLibraries = array();
        }

        global $globalRequiredLibrariesToLoadLater_JS;
        if (!isset($globalRequiredLibrariesToLoadLater_JS)) {
            $globalRequiredLibrariesToLoadLater_JS = array();
        }
        
        // Wurde die Library bereits hinzugefügt?
        if (in_array($libName, $globalRequiredLibraries)) {
            // Ja!
            // -> Nichts tun. Nur einmal hinzufügen.
            consoleLog("Library wurde bereits hinzugefügt: ".$libName);
            return;
        }

        // Anfügen dieser Library zu den bereits hinzugefügten Libraries:
        $globalRequiredLibraries[] = $libName;

        // Initialisierung:
        $cssLink = "";
        $jsLoadNow = "";
        $jsLoadLater = "";

        // Welches Modul soll geladen werden?
        switch ($libName) {
            
            case "bootstrap5.3":
                $cssLink = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css";
                $jsLoadLater = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js";        
                break;
            
            case "bootstrap4.3.1":
                $cssLink = "https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css";
                $jsLoadLater = "https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js";
                break;

            case "bootstrap-icons":
                $cssLink = "https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css";
                $jsLoadLater = "https://cdn.jsdelivr.net/npm/bootstrap-icons/bootstrap-icons.js";
                break;
            
            case "font-awesome":
                $cssLink = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css";
                $jsLoadLater = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js";
                break;
            
            case "chartjs":
                $cssLink = "https://cdn.jsdelivr.net/npm/chart.js/dist/Chart.min.css";
                $jsLoadNow = "https://cdn.jsdelivr.net/npm/chart.js";
                break;
        }
        if ($cssLink != "") {
            echo '<link href="'.$cssLink.'" rel="stylesheet">'."\n";
        }
        if ($jsLoadNow != "") {
            echo '<script src="'.$jsLoadNow.'"></script>'."\n";
        }
        if ($jsLoadLater != "") {
            consoleLog("Library wird später geladen: ".$jsLoadLater);
            $globalRequiredLibrariesToLoadLater_JS[] = $jsLoadLater;
        }
    }

    /**
     * Log to the Browser Console
     *
     * @param  string $message
     * @return void
     * @author Urs Langmeier
     */
    function consoleLog($message) {
        echo "<script>console.log('$message');</script>";
    }

    /**
    * Diese Funktion wandelt einen HEX-Wert in einen RGBA-Wert um
    * @param string $hex Der HEX-Wert, der umgewandelt werden soll
    * @param float $alpha Der Alpha-Wert (Transparenz) des RGBA-Werts
    * @return string|null Der RGBA-Wert oder null, falls der HEX-Wert ungültig ist
    */
    function hex2Rgba($hex, $alpha = 0.4) {
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

    /** 
     *  Ersetzt alle <br> und <p> Tags durch Zeilenumbrüche
     *  @param string $string Der String, in dem die Tags ersetzt werden sollen
     *  @return string Der String mit den ersetzten Tags.
     */
    function br2nl($string){
        $string = preg_replace('/\<p(\s*)?\/?\>/i', "\n", $string);
        $string = replace($string, "</p>", "\n");
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }

    // ula, 02.10.2024
    /**
     * Funktion zum Umwandeln eines Arrays in einen String
     * 
     * @param array $arr        Das Array, das umgewandelt werden soll
     * @param boolean $blnJSON  Soll das Array als JSON umgewandelt werden?
     * @return string
     */
    function arr2String($arrayOrString, $blnJSON = false) {
        if ( is_array($arrayOrString) ) {
            // Das ist ein Array
            if ( $blnJSON ) {
                return json_encode($arrayOrString);
            } else {
                return print_r($arrayOrString, true);
            }
        
        } else {
            // Das ist kein Array
            // ->Variablenwert umwandeln in einen String
            return (string)$arrayOrString;
        }
    }

    /** Gibt den Wert eines Arrays zurück, wenn er existiert, sonst null.
     * 
     *  Parameter:
     *    $array      Das Array, in dem der Wert geprüft wird.
     *    $key        Der Schlüssel, dessen Wert geprüft wird.
     *   
     * Return:
     *    Der Wert des Schlüssels, wenn er existiert, sonst null.
     * 
    */
    function arrVal(&$array = null, $key) {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        } else {
            return null;
        }
    }

    /**
     * Gibt den Wert aus der $_POST-Variable zurück, wenn diese Variable existiert, sonst null.
     * 
     * Parameter:
     *   $key        Der Schlüssel, dessen Wert geprüft wird.
     * 
     * Return:
     *  Der Wert des Schlüssels, wenn er existiert, sonst null.
     */
    function _POST($key) {
        return arrVal($_POST, $key);
    }

    /**
     * Gibt den Wert aus der $_GET-Variable zurück, wenn diese Variable existiert, sonst null.
     * 
     * Parameter:
     *   $key        Der Schlüssel, dessen Wert geprüft wird.
     * 
     * Return:
     *  Der Wert des Schlüssels, wenn er existiert, sonst null.
     */
    function _GET($key) {
        return arrVal($_GET, $key);
    }

    /**
     * Gibt den Wert aus der $_SESSION-Variable zurück, wenn diese Variable existiert, sonst null.
     * 
     * Parameter:
     *   $key        Der Schlüssel, dessen Wert geprüft wird.
     * 
     * Return:
     *  Der Wert des Schlüssels, wenn er existiert, sonst null.
     */
    function _SESSION($key) {
        return arrVal($_SESSION, $key);
    }

    /**
     * Gibt den Wert aus der $_COOKIE-Variable zurück, wenn diese Variable existiert, sonst null.
     * 
     * Parameter:
     *   $key        Der Schlüssel, dessen Wert geprüft wird.
     * 
     * Return:
     *  Der Wert des Schlüssels, wenn er existiert, sonst null.
     */
    function _COOKIE($key) {
        return arrVal($_COOKIE, $key);
    }

    /**
     * Gibt den Wert aus der $_REQUEST-Variable zurück, wenn diese Variable existiert, sonst null.
     * 
     * Parameter:
     *   $key        Der Schlüssel, dessen Wert geprüft wird.
     * 
     * Return:
     *  Der Wert des Schlüssels, wenn er existiert, sonst null.
     */
    function _REQUEST($key) {
        return arrVal($_REQUEST, $key);
    }

    /**
     * Gibt den Wert aus der $_SERVER-Variable zurück, wenn diese Variable existiert, sonst null.
     * 
     * Parameter:
     *   $key        Der Schlüssel, dessen Wert geprüft wird.
     * 
     * Return:
     *  Der Wert des Schlüssels, wenn er existiert, sonst null.
     */
    function _SERVER($key) {
        return arrVal($_SERVER, $key);
    }

    /**
     * Gibt den Wert aus der $_FILES-Variable zurück, wenn diese Variable existiert, sonst null.
     * 
     * Parameter:
     *   $key        Der Schlüssel, dessen Wert geprüft wird.
     * 
     * Return:
     *  Der Wert des Schlüssels, wenn er existiert, sonst null.
     */
    function _FILES($key) {
        return arrVal($_FILES, $key);
    }
    
    /**
    * Gibt die Position zurück, in der $needle in $haystack ab der Position $lPos vorkommt.
    * Gibt 0 zurück, falls $needle nicht in $haystack vorkommt.
    * WICHTIG: 1-basierend!
    *
    * @param string $haystack Der String, in dem gesucht wird.
    * @param string $needle Der String, der gesucht wird.
    * @param int $lPos Die Position, ab der gesucht wird.
    * @return int
    */
    function instr($haystack, $needle, $lPos = 1)
    {	
        if ( $lPos > strlen($haystack) ) return 0;
        if ( $lPos < 1 ) $lPos = 1;
        $strpos = strpos($haystack, $needle, $lPos - 1);
        if ( $strpos !== false )
        {	// Kommt vor!
            return $strpos+1;
        } else
        {	// Kommt nicht vor!
            return 0;
        }
    }

    /**
     * Gibt die Position zurück, in der $needle in $haystack vorkommt.
     * Gibt 0 zurück, falls $needle nicht in $haystack vorkommt.
     * Ist im Gegensatz zu ->instr() rechts-basierend, das heisst es
     * es wird von rechts nach links gesucht.
     * WICHTIG: 1-basierend!
     * 
     * @param string $haystack Der String, in dem gesucht wird.
     * @param string $needle Der String, der gesucht wird.
     * @param int $lPos Die Position, ab der gesucht wird.
     * @return int
     */
    function instrRev($haystack, $needle, $lPos = 0)
    {	
        if ( $lPos == 0 ) $lPos = strlen($haystack);
        $strpos = strrpos(left($haystack, $lPos), $needle);
        if ( $strpos !== false )
        {	// Kommt vor!
            return $strpos+1;
        } else
        {	// Kommt nicht vor!
            return 0;
        }
    }

    /** Returns a string in which a specified substring has been replaced with another substring a specified number of times.
     *  Parameter:
     *      $exp            Die volle Zeichenkette
     *      $find           Die zu suchende Zeichenkette
     *      $replaceWith    Die zu ersetzende Zeichenkette
     *      $limit          Anzahl zu ersetzen (optional), Standard ist alles ersetzen.
     */
    function replace($exp, $find, $replacewith, $limit=null ) {
        if($limit) {
            return preg_replace('/'.preg_quote($find, '/').'/', $replacewith, $exp, $limit);
            
        } else {
            return str_replace($find, $replacewith, $exp);
        }
    }

    /** Gibt True zurück, wenn ein String $needle in einem anderen
     *  String $haystack vorkommt.
     * 
     *  Parameter:
     *    $haystack   Der String, in dem gesucht wird.
     *    $needle     Der String, der gesucht wird.
     * 
     *  Return:
     *  Gibt false zurück, falls $needle nicht in $haystack vorkommt.
     *  Gibt true zurück, falls $needle in $haystack vorkommt.
     */
    function contains($haystack, $needle)
    {	
        $strpos = strpos($haystack, $needle, 0);
        if ( $strpos !== false )
        {	// Kommt vor!
            return true;
        } else
        {	// Kommt nicht vor!
            return false;
        }
    }
    
    /**
     * Gibt eine Anzahl Zeichen $length im String $str zurück.
     *
     * @param  string $str
     * @param  int $length
     * @author Urs Langmeier
     */
    function left(string $str, int $length): string {
        return substr($str, 0, $length);
    }

    function right($str, $length) {
        // Gibt eine Anzahl Zeichen $length im String $str zurück, von rechts aus.
        return substr($str, -$length);
    }
    
    //VB6 Equivalent of strtolower
    //Returns a string or character converted to lowercase.
    function lcase($str) {
        return strtolower($str);
    }
    
    //VB6 Equivalent of strtoupper
    //Returns a string or character converted to uppercase.
    function ucase($str) {
        return strtoupper($str);
    }
    
    //Returns an Integer representing the ASCII character code corresponding to the first letter in a string.
    function asc($string) {
        $char = substr($string,0,1);
        return ord($char);
    }

    function mid($str, $pos, $len = 0)
    {   // Gibt aus dem String $str den Bereich ab der Position $pos zurück, mit einer Länge von
        // $len, falls $len nicht angegeben, dann bis ans Ende des Strings $str.
        if ( $len != 0 )
            return substr($str, $pos-1, $len);
        else
            return substr($str, $pos-1);

    }
    
    //VB6 Equivalent of strlen
    function len($str) {
        return strlen($str);
    }