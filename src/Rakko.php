<?php

/*
 * Copyright Martijn van der Kleijn, 2015
 * Licensed under MIT license.
 */

namespace Rakko;

final class Rakko {
  private $resources = [];
  private $params    = [];
  private $baseurl   = '';

  // baseurl can be something like /api/v1
  public function __construct($baseurl) {
    $this->baseurl = strtolower($baseurl);
  }

  // register resource
  public function addResource($resource) {
    if (!is_array($resource)) {
      $resource = [$resource];
    }

    $this->resources = array_merge($this->resources, $resource);

    return $this;
  }

  public function listResources() {
    return $this->resources;
  }

  // Dispatches in three distinct ways, set by developer preference.
  //
  // 1. dispatch to functions like <pathname><Verb>(), for example:
  //    booksGet($arg1)
  //    booksPost($arg1)
  //
  // 2. dispatch to static class methods like <class>::<verb>(), for example:
  //    Book::get($arg1);
  //
  // FUTURE
  // 3. dispatch to active record style model functions, for example:
  //    /books/:num + GET dispatches to Book::findById($num)
  //    /books/:num + PUT dispatches to Book::updateById($num, $content)
  public function dispatch() {
    // Determine clean path
    $path = strtolower($_SERVER['REQUEST_URI']);
    $path = preg_replace('/^' . preg_replace('/\//', '\/', $this->baseurl) . '/', '', $path);

    // determine resource exists
    // TODO make more complex / correct
    // if (! in_array($path, $this->resources)) {
    //   self::error(404);
    // }
    if (!$this->hasResource($path)) {
      Response::error(404);
    }

    // split url into resource chunks
    // split chunks into resource name and params
    // on multiple chunks, combine chunk names
    // combine params in single param array
    // call function

    // determine request verb and if verb is allowed
    $request = Request::verb();

    // determine if security is required
    // TODO later stage. In core or in plugin?

    // try to route to function
    // $function = ltrim(rtrim($path, '/'), '/').ucfirst($request);
    if ($request == 'head') {
      $function = $this->resource . 'Get';
    }
    else {
      $function = $this->resource . ucfirst($request);
    }

    if (!function_exists($function)) {
      Response::error(501);
    }
    else {
      if (count($this->params) > 0) {
        $function($this->params);
      }
      else {
        $function();
      }
    }

    Response::error(500);
  }

  /**
     * Checks if a route exists for a specified path.
     *
     * @param string $path      A path (for instance path/to/page)
     * @return boolean          Returns true when a route was found, otherwise false.
     */
    private function hasResource($requested_url) {
        if (!$this->resources || count($this->resources) === 0) {
            return false;
        }

        // Make sure we strip trailing slashes in the requested url
        $requested_url = rtrim($requested_url, '/');

        foreach ($this->resources as $route) {// => $action) {
            $pos = strpos($route, ':');
            if ($pos !== false) {
                $route = str_replace(':any', '([^/]+)', str_replace(':num', '([0-9]+)', str_replace(':all', '(.+)', $route)));
            }

            // Does the regex match?
            if (preg_match('#^' . $route . '$#', $requested_url)) {
                // Do we have a back-reference?
                // if (strpos($action, '$') !== false && strpos($route, '(') !== false) {
                //     $action = preg_replace('#^'.$route.'$#', $action, $requested_url);
                // }
                if ($pos !== false) {
                  $this->params   = self::splitUrl(substr($requested_url, $pos-1));
                  $this->resource = Inflector::camelize(substr($requested_url, 0, $pos-1), '/');
                }
                else {
                  $this->resource = Inflector::camelize($requested_url, '/');
                }

                // We found it, so we can break the loop now!
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a URL into an array of its components.
     *
     * @param string $url   A URL.
     * @return array        An array of URL components.
     */
    private static function splitUrl($url) {
        return preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);
    }

}
