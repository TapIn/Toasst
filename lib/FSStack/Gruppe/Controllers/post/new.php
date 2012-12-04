<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

class New2 extends \CuteControllers\Base\Rest
{
    public function __post_index()
    {
        $title = $this->request->post('title');
        $content = $this->request->post('content');
        $groupID = $this->request->post('groupID');
        $postID = $this->request->post('postID');

        $group = new Models\Group($groupID);

        $post = Models\Post::create(Models\User::current(), $title, $content, $group);

        if ($postID) {
            $parent_post = new Models\Post($postID);
            Models\Notification::create('reply', $post, $group, Models\User::current(), $parent_post->user);
            $post->in_reply_to_postID = $postID;

            $parentgroupPost = new Models\Group\Post(array(
                'postID' => $postID,
                'groupID' => $groupID
            ));

            $groupPost = new Models\Group\Post(array(
                'postID' => $post->postID,
                'groupID' => $groupID
            ));

            if ($parentgroupPost->parent_postID) {
                $groupPost->parent_postID = $parentgroupPost->parent_postID;
            } else {
                $groupPost->parent_postID = $parentgroupPost->postID;
            }

            $groupPost->update();
            $post->update();
        }

        // TODO: Render the post template and return it.
        $rendered_post = '';

        echo json_encode(array("success" => TRUE, "html" => $rendered_post));
    }
}
