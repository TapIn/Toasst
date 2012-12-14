<?php

namespace FSStack\Gruppe\Controllers;

use \FSStack\Gruppe\Models;

class Index extends \CuteControllers\Base\Rest
{
    public function __get_index()
    {
        if (Models\User::is_logged_in() || isset($_COOKIE['is_returning_user'])) {
            $this->redirect('/feed.bread');
        } else {
            \Application::$twig->display('splash.html.twig');
        }
    }

    public function __post_index()
    {
        $to = $this->request->request('to');

        if ($to === NULL) {
            $to = '/feed.bread';
        }

        $to = \CuteControllers\Router::link($to);

        echo '<!DOCTYPE html><head><title>Toasst</title></head><body><h1>Please wait...</h1><script type="text/javascript">top.location.href = "' . $to . '";</script></body></html>';
    }
}
