<?php

/*
 * Copyright Martijn van der Kleijn, 2015
 * Licensed under MIT license.
 */

namespace Rakko;

if (!defined('RAKKO_BASICAUTH_REALM')) {
  define('RAKKO_BASICAUTH_REALM', 'Rest API');
}

if (!defined('RAKKO_BASICAUTH_SRC')) {
  define('RAKKO_BASICAUTH_SRC', []);
}

/**
 *
 */
final class BasicAuth implements Auth {

  public function __construct() {
  }

  public static function require() {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
      header('WWW-Authenticate: Basic realm="'.RAKKO_BASICAUTH_REALM.'"');
      Response::error(401);
    }
    else {
      self::auth(RAKKO_BASICAUTH_SRC);
    }
  }

  /**
   * TODO allow string(?), array and PHP7 generators for source of username password
   */
  private static function auth(array $source) {
    $user = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    if (empty($source)) {
      Response::error(401);
    }

    if (! array_key_exists($user, $source)) {
      Response::error(401);
    }

    if ($source[$user] != $password) {
      Response::error(401);
    }
  }
}
