<?php

$proto = 'HTTP/1.1';
if (isset($_SERVER["SERVER_PROTOCOL"])) {
  $proto = $_SERVER["SERVER_PROTOCOL"];
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    header($proto . ' 304 Not Modified');
} else if (isset($_GET['mime']) && isset($_GET['url'])) {
  $url = $_GET['url'];
  $timeout = 20;
  ini_set('default_socket_timeout', $timeout);
  if ($stream = fopen($url, 'r')) {
    $mime = $_GET['mime'];
    header("Content-type: " . $mime);
    header("Cache-Control: public, max-age=2592000, stale-while-revalidate=2592000, s-maxage=2592000");
    header("Content-Transfer-Encoding: binary");
    header("Last-Modified: Tue, 30 Mar 2021 17:00:00 GMT");
    header("Expires: Fri, 30 Apr 2021 17:00:00 GMT");
    ob_end_clean();

    fpassthru($stream);
    fclose($stream);
  } else {
    header($proto . ' 503 Service Unavailable');
    header("Content-type: text/plain");
    echo 'Download failed';
  }
} else {
  header($proto . ' 400 Bad Request');
  header("Content-type: text/plain");
  echo 'Expected parameters: url, mime';
}
