<?php
function traccarLogin($url, $user, $password)
{
    $ch = curl_init(rtrim($url, '/') . '/api/session');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['email' => $user, 'password' => $password]), CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'], CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]);
    $r = curl_exec($ch);
    if ($r === false) {
        $e = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'mensaje' => $e];
    }
    $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $h = substr($r, 0, $hs);
    curl_close($ch);
    if ($http < 200 || $http >= 300) return ['ok' => false, 'mensaje' => "HTTP $http"];
    preg_match_all('/^Set-Cookie:\s*([^;\r\n]*)/mi', $h, $m);
    return ['ok' => true, 'cookies' => implode('; ', $m[1] ?? [])];
}
function traccarGet($url, $cookies)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Accept: application/json', 'Cookie: ' . $cookies], CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]);
    $r = curl_exec($ch);
    if ($r === false) {
        $e = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'mensaje' => $e];
    }
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http < 200 || $http >= 300) return ['ok' => false, 'mensaje' => "HTTP $http"];
    $d = json_decode($r, true);
    return is_array($d) ? ['ok' => true, 'datos' => $d] : ['ok' => false, 'mensaje' => 'JSON inválido.'];
}
function traccarPost($url, $data, $cookies)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json', 'Cookie: ' . $cookies], CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]);
    $r = curl_exec($ch);
    if ($r === false) {
        $e = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'http' => 0, 'mensaje' => $e];
    }
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['ok' => $http >= 200 && $http < 300, 'http' => $http, 'datos' => json_decode($r, true), 'respuesta' => $r];
}
