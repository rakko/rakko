<?php

/*
 * Copyright Martijn van der Kleijn, 2015
 * Licensed under MIT license.
 */

namespace Rakko;

/**
 * The AutoLoader class is an OO hook into PHP's __autoload functionality.
 *
 * You can add use the AutoLoader class to add singe and multiple files as well
 * entire folders.
 *
 * Examples:
 *
 * Single Files   - AutoLoader::addFile('Blog','/path/to/Blog.php');
 * Multiple Files - AutoLoader::addFile(array('Blog'=>'/path/to/Blog.php','Post'=>'/path/to/Post.php'));
 * Whole Folders  - AutoLoader::addFolder('path');
 *
 * When adding an entire folder, each file should contain one class having the
 * same name as the file without ".php" (Blog.php should contain one class Blog)
 *
 */
class AutoLoader {
    protected static $files   = [];
    protected static $folders = [];

    /**
     * Register the AutoLoader on the SPL autoload stack.
     */
    public static function register()
    {
        spl_autoload_register(['\Rakko\AutoLoader', 'load'], true, true);
    }

    /**
     * Adds a (set of) file(s) for autoloading.
     *
     * Examples:
     * <code>
     *      AutoLoader::addFile('Blog','/path/to/Blog.php');
     *      AutoLoader::addFile(array('Blog'=>'/path/to/Blog.php','Post'=>'/path/to/Post.php'));
     * </code>
     *
     * @param mixed $class_name Classname or array of classname/path pairs.
     * @param mixed $file       Full path to the file that contains $class_name.
     */
    public static function addFile($class_name, $file=null) {
        if ($file == null && is_array($class_name)) {
            self::$files = array_merge(self::$files, $class_name);
        } else {
            self::$files[$class_name] = $file;
        }
    }

    /**
     * Adds an entire folder or set of folders for autoloading.
     *
     * Examples:
     * <code>
     *      AutoLoader::addFolder('/path/to/classes/');
     *      AutoLoader::addFolder(array('/path/to/classes/','/more/here/'));
     * </code>
     *
     * @param mixed $folder Full path to a folder or array of paths.
     */
    public static function addFolder($folder) {
        if (! is_array($folder)) {
            $folder = [$folder];
        }
        self::$folders = array_merge(self::$folders, $folder);
    }

    /**
     * Loads a requested class.
     *
     * @param string $class_name
     */
    public static function load($class_name) {
        if (isset(self::$files[$class_name])) {
            if (file_exists(self::$files[$class_name])) {
                require self::$files[$class_name];
                return;
            }
        } else {
            foreach (self::$folders as $folder) {
                $folder = rtrim($folder, DIRECTORY_SEPARATOR);
                $file   = $folder . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';

                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        }
        throw new \Exception("Rakko AutoLoader could not find file for '{$class_name}'.");
    }

}
