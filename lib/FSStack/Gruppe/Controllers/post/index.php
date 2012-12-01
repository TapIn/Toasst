<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

class Post extends PostController
{
    public function __get_index()
    {
        \Application::$twig->display('post.html.twig');
    }
}
