<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Yaml\Yaml;

/*
 * The hand-written openapi.yaml at the project root is the single source of truth.
 * We feed it to Nelmio as the base documentation and disable controller route
 * scanning (path pattern matches nothing) so the served spec equals the file.
 */
return static function (ContainerConfigurator $container): void {
    $spec = Yaml::parseFile(\dirname(__DIR__, 2) . '/openapi.yaml');

    // Escape "%" so the DI container doesn't treat "%20" etc. as parameter references.
    // The container resolves "%%" back to a single "%" in the served document.
    $escape = static function (array $data) use (&$escape): array {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $escape($value);
            } elseif (is_string($value)) {
                $data[$key] = str_replace('%', '%%', $value);
            }
        }
        return $data;
    };
    $spec = $escape($spec);

    $container->extension('nelmio_api_doc', [
        'documentation' => $spec,
        'areas' => [
            'default' => [
                'path_patterns' => ['^/__disable_route_scan__'],
            ],
        ],
    ]);
};
