<?php

namespace FSStack\Gruppe\Controllers\invites;

use \FSStack\Gruppe\Models;

class Invites extends \CuteControllers\Base\Rest
{
    public function before()
    {
        if (!Models\User::current()->is_admin) {
            throw new \CuteControllers\HttpError(403);
        }
    }

    public function __get_index()
    {
        \Application::$twig->display('admin/sendpush.html.twig', array('invites' => Models\Invite::all()));
    }

    public function __post_index()
    {
        $userIDs = explode(',', $this->request->request('userID'));
        foreach ($userIDs as $userID) {
            $user = new Models\User($userID);
            $message = $this->request->request('message');
            $to = $this->request->request('to');

            $user->send_facebook_push($message, $to);
        }
        $this->redirect('/admin/sendpush.bread');
    }
}
