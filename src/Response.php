<?php

/*
 * Copyright Martijn van der Kleijn, 2015
 * Licensed under MIT license.
 */

namespace Rakko;

/**
 *
 */
class Response {
  private static $ERRORS = [
      200 => 'OK',
      201 => 'Created',
      204 => 'No Content',
      304 => 'Not Modified',
      400 => 'Bad Request',             // Server could not understand request
      401 => 'Unauthorized',            // Request might be allowed, Authentication is possible but failed or wasn't provided yet
      403 => 'Forbidden',               // Request is always refused, no matter if you're authenticated
      404 => 'Not Found',
      405 => 'Method Not Allowed',      // An HTTP method is being requested that isn't allowed for the authenticated user
      409 => 'Conflict',
      410 => 'Gone',                    // The resource at this end point is no longer available.
      415 => 'Unsupported Media Type',  // An incorrect content type was provided as part of the request
      422 => 'Unprocessable Entity',    // Used for validation errors
      429 => 'Too Many Requests',
      500 => 'Internal Server Error',   // Default error in case unknown error code is given
      501 => 'Not Implemented'
  ];

  public function __construct() {
  }

  public static function error($code) {
    self::send(['code'=>$code, 'message'=>self::$ERRORS[$code]], 'json', $code);
  }

  public static function send($content = null, $type = 'json', $code = 200) {
    header('HTTP/1.1 ' . $code . ' ' . self::$ERRORS[$code]);
    header('X-Powered-By: Rakko');
    self::cors();

    if ($content === null) {
      $type    = 'plain';
      $content = self::$ERRORS[$code];
    }

    if ($type === 'json') {
      $content = json_encode($content, JSON_PRETTY_PRINT);
      header('Content-type: application/json');
    }

    if ($type === 'plain') {
      header('Content-type: text/plain');
    }

    header('Content-Length: ' . strlen($content));

    if (Request::verb() != 'head') {
      echo $content;
    }

    exit();
  }

  public static function options($allowedMethods=[]) {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
      header("Access-Control-Allow-Methods: " . implode(', ', $allowedMethods));
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
      header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    exit();
  }

  public static function cors($allowedOrigins=[], $maxAge=86400) {
    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: '.$maxAge);
    }
  }

}
