<?php

namespace Rakko;

interface Auth {

// idea for class
//
// if has resource && if resource needs Auth
//    if authenticated()
//    else 401
//
//    if resourceAllowed
//      if verbAllowed
//      else 403
//    else 403

  public static function authenticated(): bool;
  public static function resourceAllowed($url): bool;
  public static function verbAllowed($verb): bool;
}
