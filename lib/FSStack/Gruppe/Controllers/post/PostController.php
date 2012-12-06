<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

abstract class PostController extends \CuteControllers\Base\Rest
{
    public function __construct(\CuteControllers\Request $request, $action, $positional_args)
    {
        $this->post = new Models\Post($request->get('__postID'));
        $this->group = new Models\Group($request->get('__groupID'));
        $this->gpost = new Models\Group\Post(array(
            'groupID' => $this->group->groupID,
            'postID' => $this->post->postID
        ));

        if (!Models\User::is_logged_in()) {
            $this->redirect('/invite/studentrnd');
        }

        \Application::$twig->addGlobal('post', $this->post);
        \Application::$twig->addGlobal('gpost', $this->gpost);
        \Application::$twig->addGlobal('group', $this->group);

        parent::__construct($request, $action, $positional_args);
    }
}
