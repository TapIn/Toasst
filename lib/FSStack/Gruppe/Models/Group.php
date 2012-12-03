<?php

namespace FSStack\Gruppe\Models;

/**
 * Stores information about a group
 */
class Group extends \TinyDb\Orm
{
    public static $table_name = 'groups';
    public static $primary_key = 'groupID';

    protected $groupID;
    /**
     * The group name
     * @var string
     */
    protected $name;
    /**
     * The group's description. Currently unused.
     * @var string
     */
    protected $description;

    /**
     * The group's image
     * @var string
     */
    protected $image;
    /**
     * The group's color - 6 hex digits
     * @var string
     */
    protected $color;
    /**
     * TRUE if the group is private. Currently unused.
     * @var boolean
     */
    protected $is_private;
    /**
     * TRUE if the group is closed. Currently unused.
     * @var boolean
     */
    protected $is_closed;

    /**
     * A short name used in generating URLs; optional
     * @var string
     */
    protected $short_name;

    public static function from_short_name_or_id($short_name_or_id)
    {
        if (is_numeric($short_name_or_id)) {
            return new self($short_name_or_id);
        } else {
            return new self(array(
                'short_name' => $short_name_or_id
            ));
        }
    }

    /**
     * Gets the short name if one exists, otherwise returns the groupID. Magic getter for $group->link_name
     * @return string The name of the group for use in URLs
     */
    public function __get_link_name()
    {
        if ($this->short_name) {
            return $this->short_name;
        } else {
            return $this->groupID;
        }
    }

    /**
     * Gets the members in the group. Magic getter for $group->members
     * @return \TinyDb\Collection[User] Collection of users in the group
     */
    public function __get_members()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\User', \TinyDb\Sql::create()
                                        ->join('users_groups ON (users.userID = users_groups.userID)')
                                        ->where('groupID = ?', $this->groupID));
    }

    /**
     * Checks if a user is in the group
     * @param  User    $user User to check
     * @return boolean       TRUE if the user is in the group, FALSE otherwise
     */
    public function has_member(User $user)
    {
        $collection = new \TinyDb\Collection('\FSStack\Gruppe\Models\User', \TinyDb\Sql::create()
                                             ->where('groupID = ?', $this->groupID)
                                             ->where('userID = ?', $user->userID)
                                             ->limit(1));
        return count($collection) > 0;
    }


    /**
     * Gets the post associations for this group's newsfeed
     * @param  number $count Number of posts to get
     * @param  number $after The ID of the previous post; will get posts chronologically older than this
     * @return \TinyDb\Collection[Group\Post]        Collection of post mappings in the newsfeed
     */
    public function get_newsfeed_posts($count = 15, $after = NULL)
    {
        $query = \TinyDb\Sql::create()
                  ->select('groups_posts.*')
                  ->from('groups_posts')
                  ->join('posts ON (posts.postID = groups_posts.postID)')
                  ->where('groupID = ?', $this->groupID)
                  ->where('posts.in_reply_to_postID IS NULL OR groups_posts.reposted_by_userID IS NOT NULL')
                  ->order_by('created_at DESC')
                  ->limit($count);

        if (isset($after)) {
            $query = $query->where('postID < ?', $after);
        }

        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', $query);
    }
}
