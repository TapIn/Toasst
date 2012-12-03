<?php

namespace FSStack\Gruppe\Controllers\user;

use \FSStack\Gruppe\Models;

class Notifications extends \CuteControllers\Base\Rest
{
    public function __post_mark_read()
    {
        foreach (Models\User::current()->unread_notifications as $notification) {
            $notification->mark_read();
        }

        return array('success' => TRUE);
    }
}
