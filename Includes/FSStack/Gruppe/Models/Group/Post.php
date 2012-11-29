<?php

namespace FSStack\Gruppe\Models\Group;

use \FSStack\Gruppe\Models;

/**
 * Associates a post with a group
 */
class Post extends \TinyDb\Orm
{
    public static $table_name = 'groups_posts';
    public static $primary_key = array('groupID', 'postID');

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
     * The ID of the user who reposted the post, or NULL if it's the original post.
     * @var number
     */
    protected $reposted_by_userID;

    /**
     * The score of the post, cached.
     * @var number
     */
    protected $score;

    /**
     * The group the post is in. Magic getter for $groupPost->group
     * @return Models\Group The group the post is in
     */
    public function __get_group()
    {
        return new Models\Group($this->groupID);
    }

    /**
     * The post in the group. Magic getter for $groupPost->post
     * @return Models\Post The post in the group
     */
    public function __get_post()
    {
        return new Models\Post($this->postID);
    }

    /**
     * Checks if the post was reposted. If FALSE, accessing reposted_by_user will throw an exception. Magic getter for
     * $groupPost->is_repost
     * @return boolean TRUE if the post is a repost, FALSE otherwise
     */
    public function __get_is_repost()
    {
        return isset($this->reposted_by_userID);
    }

    /**
     * The user who reposted the post. Will throw an exception if not reposted. Magic getter for
     * $groupPost->reposted_by_user
     * @return Models\User User who reposted the post
     */
    public function __get_reposted_by_user()
    {
        if ($this->is_repost) {
            return new Models\User($this->reposted_by_userID);
        } else {
            throw new \Exception("Not a repost");
        }
    }
}