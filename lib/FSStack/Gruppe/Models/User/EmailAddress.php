<?php

namespace FSStack\Gruppe\Models\User;

use \FSStack\Gruppe\Models;

/**
 * Stores a user's email
 */
class EmailAddress extends \TinyDb\Orm
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
     * Creates an email address for a user
     * @param  Models\User $user          The user who the email belongs to
     * @param  string      $email_address The email address
     * @return EmailAddress               User email mapping
     */
    public static function create(Models\User $user, $email_address)
    {
        return parent::create(array(
            'userID' => $user->userID,
            'email' => $email_address
        ));
    }

    /**
     * The user with the email address. Magic getter for $email->user
     * @return Models\User User with the email address
     */
    public function __get_user()
    {
        return new Models\User($this->userID);
    }
}
