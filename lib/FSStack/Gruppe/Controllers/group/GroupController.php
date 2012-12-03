<?php

namespace FSStack\Gruppe\Controllers\Group;

use \FSStack\Gruppe\Models;

abstract class GroupController extends \CuteControllers\Base\Rest
{
    public function __construct(\CuteControllers\Request $request, $action, $positional_args)
    {
        $this->group = Models\Group::from_short_name_or_id($request->get('__groupID'));
        \Application::$twig->addGlobal('group', $this->group);

        if ($this->group->is_closed && !Models\User::current()->is_member($this->group)) {
            throw new \CuteControllers\HttpError(404);
        }

        parent::__construct($request, $action, $positional_args);
    }
}
