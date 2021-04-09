<?PHP

//FONTOS!!!!
//HA logolni akarok:
//sudo ps aux | grep httpd parancsal a terminal altal kiirt elso oszlop a fontos
//Hozzak letre egy fajlt /home/odroid/Desktop/log.log es az elobb megallapitot tulajdonos kapjon hozza irasi jogot
//Egyszerubb ha ezt a fajt barki tudja szerkeszteni
ini_set('display_errors', 1);
$log_dir = "../../../../../logs";
$fp = fopen($log_dir.'/ESPUpdater.log', 'a');
ini_set("error_log", $log_dir.'/ESPUpdater.log');

header('Content-type: text/plain; charset=utf8', true);

function check_header($fp, $name, $value = false) {
    if(!isset($_SERVER[$name])) {
        fwrite($fp, $name);
        fwrite($fp, " is not set");
        return false;
    }
    if($value && $_SERVER[$name] != $value) {
        return false;
    }
    return true;
}

function sendFile($path) {
    header($_SERVER["SERVER_PROTOCOL"].' 200 OK', true, 200);
    header('Content-Type: application/octet-stream', true);
    header('Content-Disposition: attachment; filename='.basename($path));
    header('Content-Length: '.filesize($path), true);
    header('x-MD5: '.md5_file($path), true);
    readfile($path);
}

$date = date("y/m/d - H:i:s ");
fwrite($fp, $date);

if(!check_header($fp,'HTTP_USER_AGENT', 'ESP32-http-Update')) {
    header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden', true, 403);
    echo "only for ESP32 updater!\n";
	fwrite($fp, " Header:");
    fwrite($fp, $_SERVER["HTTP_USER_AGENT"]);
    fwrite($fp, "  only for ESP32 updater!\n");
	fclose($fp);
    exit();
}

if(
    !check_header($fp,'HTTP_X_ESP32_STA_MAC') ||
    !check_header($fp,'HTTP_X_ESP32_AP_MAC') ||
    !check_header($fp,'HTTP_X_ESP32_FREE_SPACE') ||
    !check_header($fp,'HTTP_X_ESP32_SKETCH_SIZE') ||
    !check_header($fp,'HTTP_X_ESP32_SKETCH_MD5') ||
    !check_header($fp,'HTTP_X_ESP32_CHIP_SIZE') ||
    !check_header($fp,'HTTP_X_ESP32_SDK_VERSION')
) {
    header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden', true, 403);
    echo "only for ESP32 updater! (header)\n";
    fwrite($fp, "Header:");
    fwrite($fp, $_SERVER["HTTP_USER_AGENT"]);
    fwrite($fp, "  aaaonly for ESP32 updater!\n");
    fclose($fp);
    exit();
}

$db = array(				//CASE SENSITIVE!!!!!!!!!!!!!!!!!
//-------------------------------------------------------------szelep
    "5C:CF:7F:28:8F:83" => "v1.68old.1",		//locsolo1 ez a régi
    "5C:CF:7F:79:50:41" => "v1.68.1",			//locsolo2
    "24:0A:C4:13:65:30" => "Testv2.02",			//locsolo2
//-------------------------------------------------------------szenzor
    "84:F3:EB:82:03:9A" => "v1.51.0"            //szenzor1
);

if(!isset($db[$_SERVER['HTTP_X_ESP32_STA_MAC']])) {
    header($_SERVER["SERVER_PROTOCOL"].' 500 ESP MAC not configured for updates', true, 500);
}

//$localBinary = "client.ino.generic.bin";
$localBinary = "bin/".$db[$_SERVER['HTTP_X_ESP32_STA_MAC']].".bin";

// Check if version has been set and does not match, if not, check if
// MD5 hash between local binary and ESP8266 binary do not match if not.
// then no update has been found.


fwrite($fp, "  ");
fwrite($fp, $_SERVER['HTTP_X_ESP32_STA_MAC']);
fwrite($fp, " HW version: ");
fwrite($fp, $_SERVER['HTTP_X_ESP32_VERSION']);
fwrite($fp, " desired version: ");
fwrite($fp, $db[$_SERVER['HTTP_X_ESP32_STA_MAC']]);
fwrite($fp, "   ");
fwrite($fp, "bin/".$db[$_SERVER['HTTP_X_ESP32_STA_MAC']].".bin");


if(/*!check_header('HTTP_X_ESP32_SDK_VERSION') &&*/ array_key_exists($_SERVER['HTTP_X_ESP32_STA_MAC'], $db) && ($db[$_SERVER['HTTP_X_ESP32_STA_MAC']] != $_SERVER['HTTP_X_ESP32_VERSION']) && ($_SERVER["HTTP_X_ESP32_SKETCH_MD5"] != md5_file($localBinary))){
//    || $_SERVER["HTTP_X_ESP32_SKETCH_MD5"] != md5_file($localBinary)) {
    fwrite($fp, "   UPDATE STARTED\n");
    fclose($fp);
    sendFile($localBinary);
} else {
    fwrite($fp, "   NO UPDATE\n");
    fclose($fp);    
    header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified', true, 304);
    exit();
}

header($_SERVER["SERVER_PROTOCOL"].' 500 no version for ESP MAC', true, 500);


