<?php

namespace FSStack\Gruppe\Controllers\group;

use \FSStack\Gruppe\Models;

class Directory extends GroupController
{
    public function __get_index()
    {
        \Application::$twig->display('group/directory.html.twig');
    }
}
