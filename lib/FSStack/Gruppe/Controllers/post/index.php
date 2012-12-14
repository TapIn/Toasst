<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

class Post extends PostController
{
    public function __get_index()
    {
        \Application::$twig->display('post.html.twig');
    }

    public function __post_delete()
    {
        if ($this->post->userID == Models\User::current()->userID || $this->gpost->reposted_by_userID == Models\User::current()->userID) {
            $this->gpost->delete();
        } else {
            throw new \CuteControllers\HttpError(403);
        }
    }

    public function __post_edit()
    {
        $title = $this->request->post('title');
        $content = $this->request->post('content');

        if ($this->post->userID == Models\User::current()->userID) {
            $this->post->title = $title;
            $this->post->markdown = $content;
            $this->post->markdown_cache = NULL;
        } else {
            throw new \CuteControllers\HttpError(403);
        }
    }

    public function __post_mark_read()
    {
        $this->gpost->mark_read(Models\User::current());
        return array('success' => true);
    }
}
