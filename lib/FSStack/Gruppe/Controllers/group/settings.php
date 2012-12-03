<?php

namespace FSStack\Gruppe\Controllers\group;

use \FSStack\Gruppe\Models;

class Settings extends GroupController
{
    public function before()
    {
        if (!Models\User::current()->owns_group($this->group)) {
            throw new \CuteControllers\HttpError(403);
        }

        if ($this->request->post('userID')) {
            $this->action_user = new Models\User($this->request->post('userID'));
            $this->action_user_mapping = $this->action_user->get_group_mapping($this->group);
        }
    }

    public function __get_index()
    {
        \Application::$twig->display('group/settings.html.twig');
    }

    public function __post_index()
    {
        $name = $this->request->post('name');
        $description = $this->request->post('description');
        $short_name = $this->request->post('short_name');
        $color = $this->request->post('color');
        $is_private = $this->request->post('is_private') ? TRUE : FALSE;
        $is_closed = $this->request->post('is_closed') ? TRUE : FALSE;

        // We need AT LEAST a name
        if (!$name) {
            \Application::$twig->display('group/settings.html.twig', array('error' => 'Need a name!'));
        }

        // Don't let people change their short name because fuck them is why.
        if ($this->group->short_name) {
            $short_name = $this->group->short_name;
        }

        $this->group->name = $name;
        $this->group->description = $description;
        $this->group->short_name = $short_name;
        $this->group->color = $color;
        $this->group->is_private = $is_private;
        $this->group->is_closed = $is_closed;
        $this->group->update();

        $this->redirect('/g/' . $this->group->groupID . '/settings');
    }

    public function __post_image()
    {
        $image = $this->request->post('image');
        $icon = $this->request->post('icon');

        if ($image !== NULL) {
            $this->group->image = $image;
        } else if ($icon !== NULL) {
            $this->group->icon = $icon;
        }

        $this->group->update();
    }

    public function __post_invite()
    {
    }

    public function __post_demote_owner()
    {
        $this->action_user_mapping->is_owner = FALSE;
        $this->action_user_mapping->update();
        $this->redirect('/g/' . $this->group->groupID . '/settings');
    }

    public function __post_promote_owner()
    {
        $this->action_user_mapping->is_owner = TRUE;
        $this->action_user_mapping->update();
        $this->redirect('/g/' . $this->group->groupID . '/settings');
    }

    public function __post_kick()
    {
        $this->action_user_mapping->delete();
        $this->redirect('/g/' . $this->group->groupID . '/settings');
    }

    public function __post_delete()
    {
        $this->group->delete();
        $this->redirect('/');
    }
}
