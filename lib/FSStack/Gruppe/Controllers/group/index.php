<?php

namespace FSStack\Gruppe\Controllers\group;

use \FSStack\Gruppe\Models;

class Index extends GroupController
{
    public function __get_index()
    {
        \Application::$twig->display('group.html.twig');
    }

    public function __post_follow()
    {
        Models\User\Group::create(array(
            'userID' => Models\User::current()->userID,
            'groupID' => $this->group->groupID
        ));
    }

    public function __post_unfollow()
    {
        $mapping = new Models\User\Group(array(
            'userID' => Models\User::current()->userID,
            'groupID' => $this->group->groupID
        ));

        $mapping->delete();
    }
}
