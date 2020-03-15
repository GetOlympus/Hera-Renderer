<?php

namespace GetOlympus\Hera\Application;

use GetOlympus\Hera\Application\ApplicationInterface;
use GetOlympus\Hera\Exception\Exception;
use GetOlympus\Hera\Utils\Helpers;
use GetOlympus\Hermes\Hermes;
use Twig_Environment;
use Twig_Filter;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;

/**
 * Olympus Hera Renderer.
 *
 * @package    OlympusHeraRenderer
 * @subpackage Application
 * @author     Achraf Chouk <achrafchouk@gmail.com>
 * @since      0.0.1
 *
 */

class Application implements ApplicationInterface
{
    /**
     * @var string
     */
    protected $distpath;

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var array
     */
    protected $scripts = [];

    /**
     * @var array
     */
    protected $styles = [];

    /**
     * @var string
     */
    protected $template;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $vars;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var bool
     */
    protected $usecache;

    /**
     * Constructor.
     *
     * @param  string  $context
     * @param  string  $template
     * @param  array   $vars
     * @param  array   $assets
     * @param  bool    $usecache
     */
    public function __construct($context, $template, $vars, $assets = [], $usecache = false)
    {
        // Build all views folders to add
        $paths = $this->paths;

        // Iterate on fields to render html
        if (isset($vars['fields'])) {
            foreach ($vars['fields'] as $field) {
                $paths[$field['context']] = $field['path'];
            }
        }

        // Check control path
        if (isset($vars['control_path'])) {
            $paths[$context] = $vars['control_path'];
        }

        /**
         * Add your custom views folder with alias.
         *
         * @param  array   $paths
         *
         * @return array
         */
        $paths = apply_filters('ol_hera_render_views', $paths);

        // Check paths
        if (empty($paths)) {
            throw new Exception(Hermes::t('render.errors.no_render_paths_defined'));
        }

        // Check context
        if (!array_key_exists($context, $paths)) {
            throw new Exception(sprintf(Hermes::t('render.errors.context_does_not_exist'), $context));
        }

        // Check template
        if (!file_exists($paths[$context].S.$template)) {
            throw new Exception(sprintf(Hermes::t('render.errors.template_does_not_exist'), $template));
        }

        // Update internal vars
        $this->template = '@'.$context.'/'.$template;
        $this->vars = $vars;
        $this->usecache = $usecache;

        // Define Twig loaders
        $loader = new Twig_Loader_Filesystem();

        // Add core paths with alias
        foreach ($paths as $alias => $path) {
            $loader->addPath($path, $alias);
        }

        // Check cache
        $args = $this->usecache ? ['cache' => false, 'debug' => true] : [];

        // Build Twig renderer - no cache needed for twig rendering
        $this->twig = new Twig_Environment($loader, $args);

        // Add WordPress and Custom functions
        $this->addFunctions();

        // Enqueue scripts and styles
        $this->enqueue($assets);
    }

    /**
     * Add WordPress and Custom functions
     */
    public function addFunctions() : void
    {
        /**
         * WORDPRESS functions
         */

        // Author
        $this->twig->addFunction(new Twig_SimpleFunction('get_the_author_meta', function ($display, $id) {
            get_the_author_meta($display, $id);
        }));
        $this->twig->addFunction(new Twig_SimpleFunction('get_author_posts_url', function ($id) {
            get_author_posts_url($id);
        }));

        // Image
        $this->twig->addFunction(new Twig_SimpleFunction('has_post_thumbnail', function ($id) {
            has_post_thumbnail($id);
        }));
        $this->twig->addFunction(new Twig_SimpleFunction('get_post_thumbnail_id', function ($id) {
            get_post_thumbnail_id($id);
        }));
        $this->twig->addFunction(new Twig_SimpleFunction('wp_get_attachment_image_src', function ($id, $format) {
            wp_get_attachment_image_src($id, $format);
        }));

        // Permalink
        $this->twig->addFunction(new Twig_SimpleFunction('get_permalink', function ($id) {
            get_permalink($id);
        }));
        $this->twig->addFunction(new Twig_SimpleFunction('get_term_link', function ($id, $type) {
            get_term_link($id, $type);
        }));

        // Template
        $this->twig->addFunction(new Twig_SimpleFunction('get_footer', function ($file = '') {
            get_footer($file);
        }));
        $this->twig->addFunction(new Twig_SimpleFunction('get_header', function ($file = '') {
            get_header($file);
        }));

        // Terms
        $this->twig->addFunction(new Twig_SimpleFunction('get_the_term_list', function ($id, $type, $before, $inside, $after) {
            get_the_term_list($id, $type, $before, $inside, $after);
        }));

        // wpEditor
        $this->twig->addFunction(new Twig_SimpleFunction('wp_editor', function ($content, $editor_id, $settings = []) {
            wp_editor($content, $editor_id, $settings);
        }));


        /**
         * OLYMPUS functions
         */

        // Dump array
        $this->twig->addFunction(new Twig_SimpleFunction('dump', function ($array) {
            echo '<pre>';
            var_dump($array);
            echo '</pre>';
        }));

        // File inclusion
        $this->twig->addFunction(new Twig_SimpleFunction('include_file', function ($file) {
            include($file);
        }));


        /**
         * YOUR OWN functions
         */

        /**
         * Add your custom Twig functions.
         *
         * @param Twig_Environment $twig
         */
        do_action('ol_hera_render_functions', $this->twig);
    }

    /**
     * Enqueue scripts and styles.
     *
     * @param  array   $assets
     */
    public function enqueue($assets = []) : void
    {
        // Works on assets
        $assets = array_merge(['scripts' => [], 'styles' => []], $assets);
        $assets['scripts'] = array_merge($assets['scripts'], $this->scripts);
        $assets['styles']  = array_merge($assets['styles'], $this->styles);

        // Check lists
        if (empty($assets['scripts']) && empty($assets['styles'])) {
            return;
        }

        // Used to make uniq enqueue
        $details = [];

        if (!empty($assets['scripts'])) {
            foreach ($assets['scripts'] as $script => $path) {
                $key = !is_string($script) ? $path : $script;

                if (array_key_exists($key, $details)) {
                    continue;
                }

                $details[$key] = [];

                // Media upload case
                if ('media' === $key) {
                    wp_enqueue_media();
                    continue;
                }

                // WordPress case
                if ($key === $path && !array_key_exists($key, $this->scripts)) {
                    wp_enqueue_script($path);
                    continue;
                }

                // Update path
                $path = array_key_exists($key, $this->scripts) ? $this->scripts[$key] : $path;

                // Update details
                $details[$key]['basename'] = basename($path);
                $details[$key]['fileuri']  = rtrim($this->uri, S).S.'js'.S.$details[$key]['basename'];
                $details[$key]['source']   = rtrim(dirname($path), S);
                $details[$key]['target']   = rtrim($this->distpath, S).S.'js';

                // Update script path on dist accessible folder
                Helpers::copyFile(
                    $details[$key]['source'],
                    $details[$key]['target'],
                    $details[$key]['basename'],
                    $this->usecache
                );

                // Default case
                wp_enqueue_script($script, esc_url($details[$key]['fileuri']), [], false, true);
            }
        }

        // Used to make uniq enqueue
        $details = [];

        if (!empty($assets['styles'])) {
            foreach ($assets['styles'] as $style => $path) {
                $key = !is_string($style) ? $path : $style;

                if (array_key_exists($key, $details)) {
                    continue;
                }

                $details[$key] = [];

                // WordPress case
                if ($key === $path) {
                    wp_enqueue_style($key);
                    continue;
                }

                // Update path
                $path = array_key_exists($key, $this->styles) ? $this->styles[$key] : $path;

                // Update details
                $details[$key]['basename'] = basename($path);
                $details[$key]['fileuri']  = rtrim($this->uri, S).S.'css'.S.$details[$key]['basename'];
                $details[$key]['source']   = rtrim(dirname($path), S);
                $details[$key]['target']   = rtrim($this->distpath, S).S.'css';

                // Update script path on dist accessible folder
                Helpers::copyFile(
                    $details[$key]['source'],
                    $details[$key]['target'],
                    $details[$key]['basename'],
                    $this->usecache
                );

                // Default case
                wp_enqueue_style($style, esc_url($details[$key]['fileuri']), [], false, 'all');
            }
        }

        unset($assets);
        unset($details);
    }

    /**
     * Render TWIG component.
     */
    public function view() : void
    {
        echo $this->twig->render($this->template, $this->vars);
    }
}
