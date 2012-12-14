<?php

namespace FSStack\Gruppe\Models\User;

use \FSStack\Gruppe\Models;

/**
 * Represents a login.
 */
class Login extends \TinyDb\Orm
{
    public static $table_name = 'users_logins';
    public static $primary_key = 'loginID';

    protected $loginID;
    protected $userID;
    protected $created_at;

    public function __get_user()
    {
        return new Models\User($this->userID);
    }

    public static function create(Models\User $user)
    {
        return parent::create(array('userID' => $user->userID));
    }
}
