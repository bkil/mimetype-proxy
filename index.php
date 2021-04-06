<?php

function parseHeaders(iterable $headers): array {
  $parsed = array();
  foreach ($headers as $i => $line) {
    $f = explode(':', $line, 2);
    if (isset($f[1])) {
      $k = strtoupper(trim($f[0]));
      $parsed[$k] = trim($f[1]);
    } else {
      $parsed['http_response_line'] = $line;
      if (preg_match("~^HTTP/[0-9\.]+\s+([0-9]+)\s+(.*)$~", $line, $out)) {
        $parsed['http_response_code'] = intval($out[1]);
        $parsed['http_response_message'] = $out[2];
      }
    }
  }
  return $parsed;
}

function asInt(string $str): int {
  $int = intval($str);
  if (($int === 0) && ($str !== "0")) {
    $int = -1;
  }
  return $int;
}

function exitFailure(string $statusLine, string $body = null): void {
    header($statusLine);
    if (isset($body)) {
      header("Content-Type: text/plain");
      echo $body;
    }
    exit();
}

function openForDownload(string $url, string $proto) {
  $timeout = 10;
  ini_set('default_socket_timeout', $timeout);

  if (isset($sentUserAgent)) {
    ini_set('user_agent', $sentUserAgent);
  }
  if (isset($adminEmail)) {
    ini_set('from', $adminEmail);
  }
  $context = stream_context_create(
    array(
      'http' => array(
        'protocol_version' => 1.1,
        'header'           => array(
          'Connection: close'
        ),
      ),
      'tcp' => array(
        'tcp_nodelay' => true
      )
    ));
  $stream = @fopen($url, 'r', false, $context);

  if (!isset($http_response_header[0])) {
    exitFailure($proto . ' 504 Gateway Timeout', 'Unknown failure');
  }

  $parsed = parseHeaders($http_response_header);

  if (!$stream) {
    $errorCode = isset($parsed['http_response_code']) ? $parsed['http_response_code'] : -1;
    $errorMessage = isset($parsed['http_response_message']) ? $parsed['http_response_message'] : '';
    if (($errorCode >= 400) && ($errorCode <= 599)) {
      $statusLine = $errorCode . ' ' . $errorMessage;
    } else {
      $statusLine = '502 Bad Gateway';
    }
    exitFailure($proto . ' ' . $statusLine, 'Download failed');
  }

  $contentLength = isset($parsed['CONTENT-LENGTH']) ? asInt($parsed['CONTENT-LENGTH']) : -1;
  if ($contentLength >= 0) {
    header("Content-Length: " . $contentLength);
  }
  return $stream;
}

$allowedUrl = "~^https?://~i";
if (file_exists('tileserve-config.php')) {
  require_once 'tileserve-config.php';
}

$proto = 'HTTP/1.0';
if (isset($_SERVER["SERVER_PROTOCOL"])) {
  $proto = $_SERVER["SERVER_PROTOCOL"];
}

$mime = "image/jpeg";
if (isset($_GET['mime']) && ($mime != $_GET['mime'])) {
  exitFailure($proto . ' 415 Unsupported Media Type', 'Invalid mime type');
}

if (!isset($_GET['url'])) {
  exitFailure($proto . ' 400 Bad Request', 'Missing parameter: url. This is a temporary humanitarian service running until the 8th of April.');
}

$url = $_GET['url'];
if (preg_match($allowedUrl, $url) !== 1) {
  exitFailure($proto . ' 451 Unavailable For Legal Reasons', 'Invalid url');
}

$url = preg_replace("~^https://~i", "http://", $url);

if (isset($allowedUserAgent)) {
  $userAgent = "";
  if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
  }

  if (preg_match($allowedUserAgent, $userAgent) !== 1) {
    exitFailure($proto . ' 403 Forbidden', 'Invalid User-Agent');
  }
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
  exitFailure($proto . ' 304 Not Modified');
}

$stream = openForDownload($url, $proto);

header("Content-type: " . $mime);
header("Cache-Control: public, max-age=2592000, stale-while-revalidate=2592000, s-maxage=2592000");
header("Content-Transfer-Encoding: binary");
header("Last-Modified: Fri, 04 Apr 2021 10:00:00 GMT");
header("Expires: Fri, 30 Apr 2021 17:00:00 GMT");
ob_end_clean();

fpassthru($stream);
fclose($stream);
