<?php

/*
 * Copyright Martijn van der Kleijn, 2015
 * Licensed under MIT license.
 */

namespace Rakko;

/**
 * The Inflector class allows for strings to be reformated.
 *
 * For example:
 *
 * A string using underscore syntax ("camel_case") could be reformatted to
 * use camelcase syntax ("CamelCase").
 *
 * Example usage:
 * <code>
 *      echo Inflector::humanize($string);
 * </code>
 */
final class Inflector {

    /**
     * Returns a camelized string from a string using underscore syntax.
     *
     * Example: "like_this_dear_reader" becomes "LikeThisDearReader"
     *
     * @param string $string    Word to camelize.
     * @return string           Camelized word.
     */
    public static function camelize($string, $separator = '_', $lowerFirst = true) {
        $string = str_replace(' ', '', ucwords(str_replace($separator, ' ', $string)));

        if ($lowerFirst === true) {
          return lcfirst($string);
        }

        return $string;
    }

    /**
     * Returns a string using underscore syntax from a camelized string.
     *
     * Example: "LikeThisDearReader" becomes "like_this_dear_reader"
     *
     * @param  string $string   CamelCased word
     * @return string           Underscored version of the $string
     */
    public static function underscore($string) {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }

    /**
     * Returns a humanized string from a string using underscore syntax.
     *
     * Example: "like_this_dear_reader" becomes "Like this dear reader"
     *
     * @param  string $string   String using underscore syntax.
     * @return string           Humanized version of the $string
     */
    public static function humanize($string) {
        return ucfirst(strtolower(str_replace('_', ' ', $string)));
    }
}
