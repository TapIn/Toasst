<?php

namespace FSStack\Gruppe\Controllers;

use \FSStack\Gruppe\Models;

class Post extends \CuteControllers\Base\Rest
{
    public function __post_new()
    {
        $type = $this->request->post('type');
        $title = $this->request->post('title');
        $content = $this->request->post('content');
        $groupID = $this->request->post('groupID');

        $group = new Models\Group($groupID);

        $post = Models\Post::create($type, $title, $content, $group);

        // TODO: Render the post template and return it.
        $rendered_post = '';

        echo json_encode(array("success" => TRUE, "html" => $rendered_post));
    }

    public function __post_vote($groupID, $postID)
    {
        $vote = $this->request->post('vote');
        $group = new Models\Group($groupID);
        $post = new Models\Post($postID);

        Models\User::current()->vote($group, $post, $vote);
    }
}
