<?php

$URL    = "https://goldpricez.com/api/rates/currency/usd/measure/all";
$apiKey = "ece41bb5c016b137d71cb874f895ab6aece41bb5"; // pon tu API key

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-KEY: ' . $apiKey]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$result = curl_exec($ch);

if ($result === false) {
    die('Error cURL: ' . curl_error($ch));
}

curl_close($ch);

// Muestra la respuesta cruda
echo "<h3>RAW \$result</h3><pre>";
echo htmlspecialchars($result);
echo "</pre>";

$data = json_decode($result, true);

echo "<h3>json_last_error_msg()</h3>";
var_dump(json_last_error_msg());

echo "<h3>var_dump(\$data)</h3><pre>";
var_dump($data);
echo "</pre>";

if (is_array($data)) {
    $ounceAsk   = isset($data['ounce_price_ask'])            ? (float)$data['ounce_price_ask']            : null;
    $ounceBid   = isset($data['ounce_price_bid'])            ? (float)$data['ounce_price_bid']            : null;
    $ounceHigh  = isset($data['ounce_price_usd_today_high']) ? (float)$data['ounce_price_usd_today_high'] : null;
    $ounceLow   = isset($data['ounce_price_usd_today_low'])  ? (float)$data['ounce_price_usd_today_low']  : null;
    $ounceLast  = isset($data['ounce_price_usd'])            ? (float)$data['ounce_price_usd']            : null;

    echo "<h3>Valores convertidos</h3><pre>";
    var_dump($ounceAsk, $ounceBid, $ounceHigh, $ounceLow, $ounceLast);
    echo "</pre>";

    echo "<h3>Echo directo</h3>";
    echo "Ask: " . ($ounceAsk ?? 'null') . "<br>";
} else {
    echo "<h3>Ojo: \$data NO es array</h3>";
}
