<?php

namespace FSStack\Gruppe\Models;

class Notification extends \TinyDb\Orm
{
    public static $table_name = 'notifications';
    public static $primary_key = 'notificationID';

    protected $notificationID;
    protected $userID;
    protected $is_read;

    public static function create($type, Post $post, Group $group, User $source_user, User $dest_user)
    {
        return parent::create(array(
            'type' => $type,
            'userID' => $dest_user->userID,
            'groupID' => $group->groupID,
            'postID' => $post->postID,
            'source_userID' => $source_user->userID
        ));
    }

    public function __get_user()
    {
        return new User($this->userID);
    }

    protected $type;
    protected $postID;
    public function __get_post()
    {
        return new Post($this->postID);
    }

    protected $groupID;
    public function __get_group()
    {
        return new Group($this->groupID);
    }

    public function __get_gpost()
    {
        return new Group\Post(array(
            'groupID' => $this->groupID,
            'postID' => $this->postID
        ));
    }

    public function mark_read()
    {
        $this->is_read = TRUE;
        $this->invalidate('is_read');
        $this->update();
    }

    protected $source_userID;
    public function __get_source_user()
    {
        return new User($this->source_userID);
    }
}
