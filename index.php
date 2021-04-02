<?php

if (file_exists('tileserve-config.php')) {
  require_once 'tileserve-config.php';
}
if (!isset($allowedUrl)) {
  $allowedUrl = "~^https?://~";
}
if (!isset($allowedUserAgent)) {
  $allowedUserAgent = "~~";
}

$proto = 'HTTP/1.1';
if (isset($_SERVER["SERVER_PROTOCOL"])) {
  $proto = $_SERVER["SERVER_PROTOCOL"];
}

$userAgent = "";
if (isset($_SERVER['HTTP_USER_AGENT'])) {
  $userAgent = $_SERVER['HTTP_USER_AGENT'];
}

if (preg_match($allowedUserAgent, $userAgent) !== 1) {
  header($proto . ' 403 Forbidden');
  header("Content-type: text/plain");
  echo 'Invalid User-Agent';
  exit();
}

$mime = "image/jpeg";
if (isset($_GET['mime']) && ($mime != $_GET['mime'])) {
  header($proto . ' 400 Bad Request');
  header("Content-type: text/plain");
  echo 'Invalid mime type';
  exit();
}

if (!isset($_GET['url'])) {
  header($proto . ' 400 Bad Request');
  header("Content-type: text/plain");
  echo 'Missing parameter: url. This is a temporary humanitarian service running until the 8th of April.';
  exit();
}

$url = $_GET['url'];
if (preg_match($allowedUrl, $url) !== 1) {
  header($proto . ' 400 Bad Request');
  header("Content-type: text/plain");
  echo 'Invalid url';
  exit();
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
  header($proto . ' 304 Not Modified');
  exit();
}

$timeout = 20;
ini_set('default_socket_timeout', $timeout);

if (!$stream = fopen($url, 'r')) {
  header($proto . ' 503 Service Unavailable');
  header("Content-type: text/plain");
  echo 'Download failed';
}

header("Content-type: " . $mime);
header("Cache-Control: public, max-age=2592000, stale-while-revalidate=2592000, s-maxage=2592000");
header("Content-Transfer-Encoding: binary");
header("Last-Modified: Fri, 02 Apr 2021 11:00:00 GMT");
header("Expires: Fri, 30 Apr 2021 17:00:00 GMT");
ob_end_clean();

fpassthru($stream);
fclose($stream);
