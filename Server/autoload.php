<?php
/**
 * Register given function as __autoload() implementation
 * @param callable $autoload_function - The autoload function being registered.
 * @param bool $throw = TRUE - Should this throw exceptions when the $autoload_function cannot be registered?
 * @param bool $prepend = FALSE - Prepend the autoloader on the autoload queue instead of appending it.
 * @return bool - Returns TRUE on success or FALSE on failure.
 */
spl_autoload_register(function (string $fullyQualifiedNamespace) {
    static $namespaces = [
        ['TTG'              ,__DIR__.'/php/'],
        ['Psr'              ,__DIR__.'/php/Psr/'],
        ['MimoCAD'          ,__DIR__.'/php/MimoCAD/'],
        ['MimoSDR'          ,__DIR__.'/php/MimoSDR/'],
        ['Microsoft'        ,__DIR__.'/php/Microsoft/'],
        ['Google'           ,__DIR__.'/php/Google/'],
    ];

    foreach ($namespaces as [$namespace, $fullyQualifiedPath])
    {
        # Just the length of the current namespace itself.
        $len = strlen($namespace);

        # Is the Fully Qualified Name Space Prefixed with the same Namespace?
        if (0 !== strncmp($namespace, $fullyQualifiedNamespace, $len))
        {
            continue; # If they are not the same, contiune onto the next namespace.
        }

        # Get the class from the FQNS by removing the namespace prefix's length from the start.
        $class = substr($fullyQualifiedNamespace, $len);

        # We then get the fully quailified path to the class file.
        # str_replace '\\' with '/' because of the call_user_func context.
        $file = $fullyQualifiedPath . str_replace('\\', '/', $class) . '.php';

        # str_replace '//' with '/' becaue of windows file systems.
        $file = str_replace('//', '/', $file);

        # If the file exists require it, otherwise try next $namespace.
        if (file_exists($file)) {
            require $file;
            return TRUE;
        }
    }

    return FALSE;
});
