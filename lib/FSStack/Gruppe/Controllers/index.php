<?php

namespace FSStack\Gruppe\Controllers;

class Index extends \CuteControllers\Base\Rest
{
    public function __get_index()
    {
        $this->redirect('/feed.bread');
    }
}
