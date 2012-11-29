<?php

namespace FSStack\Gruppe\Models\User;

use \FSStack\Gruppe\Models;

/**
 * Stores a user's email
 */
class Email extends \TinyDb\Orm
{
    public static $table_name = 'users_emails';
    public static $primary_key = array('userID', 'email');

    /**
     * Email address
     * @var string
     */
    protected $email;       public static $__validate_email = 'email';

    /**
     * ID of the user with the email address
     * @var number
     */
    protected $userID;

    /**
     * The user with the email address. Magic getter for $email->user
     * @return Models\User User with the email address
     */
    public function __get_user()
    {
        return new Models\User($this->userID);
    }
}
