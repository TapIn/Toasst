<?php

namespace FSStack\Gruppe\Models\Group;

use \FSStack\Gruppe\Models;

/**
 * Tracks which posts are read
 */
class PostRead extends \TinyDb\Orm
{
    public static $table_name = 'groups_posts_read';
    public static $primary_key = array('groupID', 'postID', 'userID');

    /**
     * The ID of the group the post is in
     * @var number
     */
    protected $groupID;

    /**
     * The ID of the post in the group
     * @var number
     */
    protected $postID;

    /**
     * The ID of the user who created the member
     * @var number
     */
    protected $userID;

    /**
     * The time the post was created.
     * @var number
     */
    protected $created_at;

    /**
     * Creates the object
     * @param  Post        $group_post GroupPost
     * @param  Models\User $user       The user who is reading the GroupPost
     * @return PostRead                Post read mapping
     */
    public static function create(Post $group_post, Models\User $user)
    {
        return parent::create(array(
            'groupID' => $group_post->groupID,
            'postID' => $group_post->postID,
            'userID' => $user->userID
        ));
    }

    /**
     * The group the post is in. Magic getter for $groupPost->group
     * @return Models\Group The group the post is in
     */
    public function __get_group_post()
    {
        return new Post(array(
            'groupID' => $this->groupID,
            'postID' => $this->postID
        ));
    }

    /**
     * The user who read the post
     * @return Models\User The user who created the post
     */
    public function __get_user()
    {
        return new Models\User($this->userID);
    }
}
