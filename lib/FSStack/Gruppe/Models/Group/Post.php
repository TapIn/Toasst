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
     * The time the post was created.
     * @var number
     */
    protected $created_at;

    /**
     * The time the post was last modified.
     * @var number
     */
    protected $modified_at;


    protected $parent_postID;

    public function __get_parent_post()
    {
        return new Models\Post($this->parent_postID);
    }

    public function __get_has_parent_post()
    {
        return $this->parent_postID ? TRUE : FALSE;
    }

    public function __get_parent_grouppost()
    {
        return new Post(array(
            'postID' => $this->parent_postID,
            'groupID' => $this->groupID
        ));
    }


    public function __get_nested_reply_count()
    {
        $sql = \TinyDb\Sql::create()
                ->select('COUNT(*)')
                ->from(static::$table_name)
                ->where('parent_postID = ?', $this->postID);
        $row = \TinyDb\Db::get_read()->getRow($sql->get_sql(), NULL, $sql->get_paramaters(), NULL, MDB2_FETCHMODE_ASSOC);

        if (\PEAR::isError($row)) {
            throw new \Exception($row->getMessage() . ', ' . $row->getDebugInfo());
        }

        return $row['COUNT(*)'];
    }

    public function __get_my_vote()
    {
        try {
            $model = new Models\User\Vote(array(
                'userID' => Models\User::current()->userID,
                'groupID' => $this->groupID,
                'postID' => $this->postID
            ));

            return $model->vote;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    public static function create(Models\Group $group, Models\Post $post, Models\User $reposted_by_user = NULL)
    {
        $model_data = array(
            'groupID' => $group->groupID,
            'postID' => $post->postID
        );

        if (isset($reposted_by_user)) {
            $model_data['reposted_by_userID'] = $reposted_by_user->userID;
        }

        return parent::create($model_data);
    }

    /**
     * Gets a collection of Group Post mappings which are replies to the current post in the current group. Magic getter
     * for $groupPost->replies
     * @return \TinyDb\Collection[Group\Post] Replies to post in group
     */
    public function __get_replies()
    {
        $collection = new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', \TinyDb\Sql::create()
                                      ->join('posts ON (groups_posts.postID = posts.postID)')
                                      ->where('posts.in_reply_to_postID = ?', $this->postID)
                                      ->order_by('created_at DESC'));
        return $collection;
    }

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

    /**
     * Checks if the post is read for the current user
     * @return boolean TRUE if the post is read, FALSE otherwise
     */
    public function __get_is_read()
    {
        return $this->is_read(Models\User::current());
    }

    /**
     * Checks if the post is read for the given user
     * @param  Models\User $user User to check for
     * @return boolean           TRUE if the post is read, FALSE otherwise
     */
    public function is_read(Models\User $user)
    {
        $sql = \TinyDb\Sql::create()
                ->select('COUNT(*)')
                ->from(PostRead::$table_name)
                ->where('postID = ?', $this->postID)
                ->where('groupID = ?', $this->groupID)
                ->where('userID = ?', $user->userID);
        $row = \TinyDb\Db::get_read()->getRow($sql->get_sql(), NULL, $sql->get_paramaters(), NULL, MDB2_FETCHMODE_ASSOC);

        if (\PEAR::isError($row)) {
            throw new \Exception($row->getMessage() . ', ' . $row->getDebugInfo());
        }

        return $row['COUNT(*)'] > 0;
    }

    /**
     * Marks the post as read
     * @param  Models\User $user User to read the post as
     */
    public function mark_read(Models\User $user)
    {
        PostRead::create($this, $user);
    }
}
