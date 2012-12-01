<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

class Vote extends \CuteControllers\Base\Rest
{
    public function __post_index($groupID, $postID)
    {
        $vote = $this->request->post('vote');
        $group = new Models\Group($groupID);
        $post = new Models\Post($postID);

        Models\User::current()->vote($group, $post, $vote);
    }
}
