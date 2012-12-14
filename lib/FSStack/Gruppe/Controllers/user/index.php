<?php

namespace FSStack\Gruppe\Controllers\user;

use \FSStack\Gruppe\Models;

class Index extends UserController
{
    public function __get_index()
    {
        \Application::$twig->display('user.html.twig', array('user' => $this->user));
    }

    public function __get_me()
    {
        $this->redirect('/u/' . Models\User::current()->userID);
    }

    public function __get_email()
    {
        echo \Application::$twig->render('email/digest.html.twig', array('gposts' => $this->user->top_digest_posts));
    }
}
