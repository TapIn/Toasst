<?php

/**
 * Used for global storage
 */
class Application
{
    static $twig;
    static $embedly;

    private static $fb_app_token;
    public static function get_fb_app_token()
    {
        if (!isset(static::$fb_app_token)) {
            $response = file_get_contents('https://graph.facebook.com/oauth/access_token?client_id=375793235846832&client_secret=87fa6912c3369e6f11420dd23c11baff&grant_type=client_credentials');
            static::$fb_app_token = substr($response, 13);
        }

        return static::$fb_app_token;
    }
}

\Application::$embedly = new \Embedly\Embedly(array(
    'key' => '77f86426103841fa9752ad904656096e'
));
