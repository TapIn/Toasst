<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

class New2 extends \CuteControllers\Base\Rest
{
    public function __post_index()
    {
        $type = $this->request->post('type');
        $title = $this->request->post('title');
        $content = $this->request->post('content');
        $groupID = $this->request->post('groupID');
        $postID = $this->request->post('postID');

        $group = new Models\Group($groupID);

        $post = Models\Post::create(Models\User::current(), $type, $title, $content, $group);

        if ($postID) {
            Models\Notification::create('reply', new Models\Post($postID), $group, Models\User::current());
            $post->in_reply_to_postID = $postID;
            $post->update();
        }

        // TODO: Render the post template and return it.
        $rendered_post = '';

        echo json_encode(array("success" => TRUE, "html" => $rendered_post));
    }
}
