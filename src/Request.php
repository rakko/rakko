<?php

/*
 * Copyright Martijn van der Kleijn, 2015
 * Licensed under MIT license.
 */

namespace Rakko;

/*
Request has

verb
body
headers


/**
 *
 */
final class Request {

  public function __construct() {

  }

  public static function verb() {
    $allowed    = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'];
    $disabled   = ['PATCH'];
    $disallowed = ['TRACE', 'CONNECT'];

    $verb = $_SERVER['REQUEST_METHOD'];

    if (in_array($verb, $disallowed)) {
      Response::error(403);
    }

    if (in_array($verb, $disabled)) {
      Response::error(501);
    }

    if ($verb === 'OPTIONS') {
      Response::options($allowed);
    }

    if (in_array($verb, $allowed)) {
      return strtolower($verb);
    }

    Response::error(500);
  }

  public static function input() {
    if (self::verb() === 'post' || self::verb() === 'put') {
      $data = json_decode(file_get_contents('php://input'));

      if ($data === null) {
        Response::error(400);
      }

      return $data;
    }

    return false;
  }
}
