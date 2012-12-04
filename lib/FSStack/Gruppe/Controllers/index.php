<?php

namespace FSStack\Gruppe\Controllers;

use \FSStack\Gruppe\Models;

class Index extends \CuteControllers\Base\Rest
{
    public function __get_index()
    {
        if (Models\User::is_logged_in()) {
            $this->redirect('/feed.bread');
        } else {
            \Application::$twig->display('splash.html.twig');
        }
    }
}
