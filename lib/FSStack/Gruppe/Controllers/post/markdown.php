<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

class Markdown extends \CuteControllers\Base\Rest
{
    public function before()
    {
        $this->post = new Models\Post($this->request->get('postID'));
    }

    public function __get_index()
    {
        return array('markdown' => $this->post->markdown);
    }

    public function __get_rendered()
    {
        return array('html' => $this->post->rendered_markdown);
    }
}
