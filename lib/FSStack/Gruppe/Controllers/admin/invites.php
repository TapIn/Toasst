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
        \Application::$twig->display('admin/invites.html.twig', array('invites' => Models\Invite::all()));
    }

    public function __post_index()
    {
        $code = $this->request->post('code');
        $invite = new Models\Invite($code);

        if ($this->request->post('delete') == 'true') {
            $invite->delete();
        } else {
            $invite->to_join_groupIDs = $this->request->post('to_join_groupIDs');
        }

        $this->redirect('/admin/invites.bread');
    }

    public function __post_new()
    {
        Models\Invite::create(array(
            'code' => $this->request->post('code'),
            'to_join_groupIDs' => $this->request->post('to_join_groupIDs')
        ));

        $this->redirect('/admin/invites.bread');
    }
}
