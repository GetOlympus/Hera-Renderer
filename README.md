# Olympus Hera Renderer
> **Olympus Hera Renderer** is a bundle used to render all components views through the TWIG template engine.

```sh
composer require getolympus/olympus-hera-renderer
```

---

[![Olympus Component][olympus-image]][olympus-url]
[![CodeFactor Grade][codefactor-image]][codefactor-url]
[![Packagist Version][packagist-image]][packagist-url]
[![MIT][license-image]][license-blob]

---

## Example

You can easily use the `Hera` renderer to display TWIG template:

```php
namespace MyCustomPackage;

use GetOlympus\Hera\Hera;

class MyPackage
{
    protected function displayContent()
    {
        /**
         * Sample extracted from the `Metabox` Zeus-Core component.
         * @see https://github.com/GetOlympus/Zeus-Core/blob/master/src/Zeus/Metabox/Metabox.php
         */

        // Prepare admin scripts and styles
        $assets = [
            'scripts' => [],
            'styles'  => [],
        ];

        $vars = [];

        // Display fields
        foreach ($fields as $field) {
            if (!$field) {
                continue;
            }

            // Update scripts and styles
            $fieldassets = $field->assets();

            if (!empty($fieldassets)) {
                $assets['scripts'] = array_merge($assets['scripts'], $fieldassets['scripts']);
                $assets['styles']  = array_merge($assets['styles'], $fieldassets['styles']);
            }

            $vars['fields'][] = $field->prepare('metabox', $post, 'post');
        }

        /**
         * Outside the loop, you'll get an array with these elements:
         *
         * $vars = [
         *     'fields' => [
         *         [
         *             'context' => 'wordpress',
         *             'path' => '/path/to/resources/views',
         *             'template' => 'wordpress.html.twig',
         *             'vars' => [-- vars used in the target template --]
         *         ],
         *         [-- more and more --],
         *     ]
         * ];
         */

        // Render view
        $render = new Hera('core', 'layouts'.S.'metabox.html.twig', $vars, $assets);
        $render->view();
    }
}
```

## Initialization

The `Hera` component needs to be initialized by this way:

```php
/**
 * Constructor.
 *
 * @param  string  $context
 * @param  string  $template
 * @param  array   $vars
 * @param  array   $assets
 * @param  bool    $usecache
 */
new Hera($context, $template, $vars, $assets, $usecache);
```

| Variable      | Type    | Default value if not set | Details |
| ------------- | ------- | ------------------------ | ------- |
| `context`     | String  | *mandatory* | Used by the TWIG template engine to retrieve wanted template |
| `template`    | String  | *mandatory* | TWIG template to load |
| `vars`        | Array   | *mandatory* | List of vars used in the TWIG loaded template |
| `assets`      | Array   | `[]` | List of assets files to load to render template |
| `usecache`    | Boolean | `false` | Define wether to use or not TWIG engine cache system |

## Customization

If you need to add your custom TWIG functions, you can use the `ol_hera_render_functions` action hook:

```php
add_action('ol.hera.render_functions', function ($twig) {
    // Example to use the WordPress `get_header()` function through TWIG: {{ get_header(file) }}
    $twig->addFunction(new \Twig\TwigFunction('get_header', function ($file = '') {
        get_header($file);
    }));
});
```

## Release History

See [**CHANGELOG.md**][changelog-blob] for all details.

## Contributing

1. Fork it (<https://github.com/GetOlympus/Hera-Renderer/fork>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request

---

**Built with ♥ by [Achraf Chouk](http://github.com/crewstyle "Achraf Chouk") ~ (c) since a long time.**

<!-- links & imgs dfn's -->
[olympus-image]: https://img.shields.io/badge/for-Olympus-44cc11.svg?style=flat-square
[olympus-url]: https://github.com/GetOlympus
[changelog-blob]: https://github.com/GetOlympus/Hera-Renderer/blob/master/CHANGELOG.md
[codefactor-image]: https://www.codefactor.io/repository/github/GetOlympus/Hera-Renderer/badge?style=flat-square
[codefactor-url]: https://www.codefactor.io/repository/github/getolympus/Hera-Renderer
[license-blob]: https://github.com/GetOlympus/Hera-Renderer/blob/master/LICENSE
[license-image]: https://img.shields.io/badge/license-MIT_License-blue.svg?style=flat-square
[packagist-image]: https://img.shields.io/packagist/v/getolympus/olympus-Hera-Renderer.svg?style=flat-square
[packagist-url]: https://packagist.org/packages/getolympus/olympus-Hera-Renderer