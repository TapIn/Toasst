<?php

namespace FSStack\Gruppe\Controllers\user;

use \FSStack\Gruppe\Models;

abstract class UserController extends \CuteControllers\Base\Rest
{
    public function __construct(\CuteControllers\Request $request, $action, $positional_args)
    {
        if (!$request->get('__userID')) {
            throw new \CuteControllers\HttpError(404);
        }

        $this->user = new Models\User($request->get('__userID'));
        \Application::$twig->addGlobal('user', $this->user);

        parent::__construct($request, $action, $positional_args);
    }
}
