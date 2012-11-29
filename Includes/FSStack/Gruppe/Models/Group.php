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
     * Gets the members in the group. Magic getter for $group->members
     * @return \TinyDb\Collection[User] Collection of users in the group
     */
    public function __get_members()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\User', \TinyDb\Sql::create()
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
        if (isset($after)) {
            return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', \TinyDb\Sql::create()
                                      ->where('postID < ?', $after)
                                      ->limit(0, $count));
        } else {
            return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', \TinyDb\Sql::create()
                                      ->limit(0, $count));
        }
    }
}
