<?php

namespace FSStack\Gruppe\Models\User;

use \FSStack\Gruppe\Models;

/**
 * Associates a user with a group.
 */
class Group extends \TinyDb\Orm
{
    public static $table_name = 'users_groups';
    public static $primary_key = array('userID', 'groupID');

    /**
     * The ID of the user in the group.
     * @var number
     */
    protected $userID;

    /**
     * The ID of the group the user is in.
     * @var number
     */
    protected $groupID;

    /**
     * The user in the group. Magic getter method for $userGroup->user
     * @return Models\User The user in the group
     */
    public function __get_user()
    {
        return new Models\User($this->userID);
    }

    /**
     * The group the user is in. Magic getter method for $userGroup->group
     * @return Models\Group The group the user is in
     */
    public function __get_group()
    {
        return new Models\Group($this->groupID);
    }
}
