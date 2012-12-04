<?php

namespace FSStack\Gruppe\Controllers\user;

use \FSStack\Gruppe\Models;

class Invite extends \CuteControllers\Base\Rest
{
    public function __get_index($code)
    {
        $this->redirect('/user/login/fb.bread?invite=' . $code);
    }
}
