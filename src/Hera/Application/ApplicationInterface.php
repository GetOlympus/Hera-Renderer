<?php

namespace GetOlympus\Hera\Application;

/**
 * Application interface
 *
 * @package    OlympusHeraRenderer
 * @subpackage Application
 * @author     Achraf Chouk <achrafchouk@gmail.com>
 * @since      0.0.1
 *
 */

interface ApplicationInterface
{
    /**
     * Add WordPress and Custom functions
     */
    public function addFunctions() : void;

    /**
     * Enqueue scripts and styles.
     *
     * @param  array   $assets
     */
    public function enqueue($assets = []) : void;

    /**
     * Render TWIG component.
     */
    public function view() : void;
}
