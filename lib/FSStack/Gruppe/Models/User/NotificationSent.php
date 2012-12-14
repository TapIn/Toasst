<?php

namespace FSStack\Gruppe\Models\User;

use \FSStack\Gruppe\Models;

/**
 * Represents a user in the system.
 */
class NotificationSent extends \TinyDb\Orm
{
    public static $table_name = 'users_notifications_sent';
    public static $primary_key = 'notificationSentID';

    protected $notificationSentID;
    protected $userID;
    protected $notification_type;
    protected $created_at;

    public static function create(Models\User $user, $notification_type)
    {
        return parent::create(array(
            'userID' => $user->userID,
            'notification_type' => $notification_type
        ));
    }
}
