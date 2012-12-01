<?php

namespace FSStack\Gruppe\Controllers\Group;

use \FSStack\Gruppe\Models;

abstract class GroupController extends \CuteControllers\Base\Rest
{
    public function __construct(\CuteControllers\Request $request, $action, $positional_args)
    {
        $this->group = new Models\Group($request->get('__groupID'));
        \Application::$twig->addGlobal('group', $this->group);

        parent::__construct($request, $action, $positional_args);
    }
}
