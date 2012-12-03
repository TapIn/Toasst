<?php

namespace FSStack\Gruppe\Controllers\group;

use \FSStack\Gruppe\Models;

class New2 extends \CuteControllers\Base\Rest
{
    public function __post_index()
    {
        $name = $this->request->request('name');

        $group = Models\Group::create(array(
                    'name' => $name
                ));

        Models\User::current()->join_group($group, TRUE);

        return array('groupID' => $group->groupID);
    }
}
