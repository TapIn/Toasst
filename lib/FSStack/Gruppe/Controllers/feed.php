<?php

namespace FSStack\Gruppe\Controllers;

use \FSStack\Gruppe\Models;

class Feed extends \CuteControllers\Base\Rest
{
    public function __get_index()
    {
        $feed = Models\User::current()->get_newsfeed_posts(10);
        \Application::$twig->display('feed.html.twig', array('feed' => $feed));
    }
}
