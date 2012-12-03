<?php

/**
 * Used for global storage
 */
class Application
{
    static $twig;
    static $embedly;
}

\Application::$embedly = new \Embedly\Embedly(array(
    'key' => '77f86426103841fa9752ad904656096e'
));
