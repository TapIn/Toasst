<?php

namespace FSStack\Gruppe\Models;

use \FSStack\Gruppe\Models\Group;
use \FSStack\Gruppe\Models;

/**
 * Represents a user in the system.
 */
class User extends \TinyDb\Orm
{
    public static $table_name = 'users';
    public static $primary_key = 'userID';

    protected $userID;

    /**
     * The user's handle. Currently unused.
     * @var string
     */
    protected $handle;
    /**
     * The user's first name.
     * @var string
     */
    protected $first_name;
    /**
     * The user's middle name. Optional.
     * @var string
     */
    protected $middle_name;
    /**
     * The user's last name.
     * @var string
     */
    protected $last_name;

    /**
     * Facebook user ID
     * @var string
     */
    protected $fb_id;

    /**
     * Facebook auth token
     * @var string
     */
    protected $fb_access_token;

    protected $is_admin;

    /**
     * Gets the user's full name
     * @return string The user's full name
     */
    public function __get_name()
    {
        if (!isset($this->first_name)) {
            return "Anonymous";
        } else if (!isset($this->last_name)) {
            return $this->first_name;
        } else {
            if (isset($this->middle_name)) {
                return $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name;
            } else {
                return $this->first_name . ' ' . $this->last_name;
            }
        }
    }

    /**
     * Gets a user's short name, e.g. 'Tyler M.'
     * @return string Short name, one of 'Anonymous', First Name, or First Name Last Initial, whichever is most specific
     */
    public function __get_short_name()
    {
        if (!isset($this->first_name)) {
            return "Anonymous";
        } else if (!isset($this->last_name)) {
            return $this->first_name;
        } else {
            return $this->first_name . ' ' . substr(strtoupper($this->last_name), 0, 1) . '.';
        }
    }

    /**
     * The user's birthday timestamp. Optional.
     * @var number
     */
    protected $birthday;
    /**
     * The user's gender, one of: male, female, undefined. Currently unused.
     * @var string
     */
    protected $gender;
    /**
     * The user's location. Optional. Currently unused.
     * @var string
     */
    protected $location;
    /**
     * The user's "about me" text. Optional. Currently unused.
     * @var string
     */
    protected $about;

    /**
     * The user's display language ID. Defaults to "en-us".
     * @var string
     */
    protected $display_languageID;

    /**
     * The user's display language. Magic getter method for $user->display_language.
     * @return Language The user's display language
     */
    public function __get_display_language()
    {
        return new Language($this->display_languageID);
    }

    /**
     * Gets a user by email address
     * @param  string $lookup Email address to look for
     * @return User           User having the email
     */
    public static function get_from_email($lookup)
    {
        $email = new Models\User\EmailAddress(array('email' => $lookup));
        return $email->user;
    }

    /**
     * Creates a new user
     * @param  string $first_name The user's first name
     * @param  string $last_name  The user's last name
     * @param  string $email      The user's email address
     * @return User               The user
     */
    public static function create($first_name, $last_name, $email)
    {
        $model = parent::create(array(
            'first_name' => $first_name,
            'last_name' => $last_name
        ));

        $model->associate_email($email);
        return $model;
    }

    /**
     * Checks if the user is logged in
     * @return boolean TRUE if the user is logged in, FALSE otherwise
     */
    public static function is_logged_in()
    {
        return isset($_SESSION['current_userID']);
    }

    /**
     * Gets the currently logged in user
     * @return User The currently logged in user
     */
    public static function current()
    {
        if (self::is_logged_in()) {
            return new self($_SESSION['current_userID']);
        } else {
            throw new \CuteControllers\HttpError(401);
        }
    }

    /**
     * Logs the user in
     */
    public function login()
    {
        $_SESSION['current_userID'] = $this->userID;
    }

    /**
     * Logs the user out
     */
    public static function logout()
    {
        unset($_SESSION['current_userID']);
    }

    /**
     * Gets all posts from the user, both replies and top-level (but not reposts)
     * @return \TinyDb\Collection[Post] All posts from the user
     */
    public function __get_posts()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', \TinyDb\Sql::create()
                                      ->join('posts ON (posts.postID = groups_posts.postID)')
                                      ->where('userID = ?', $this->userID)
                                      ->group_by('postID')
                                      ->order_by('created_at DESC'));
    }

    public function __get_recent_notifications()
    {
        $recent_unread = $this->unread_notifications;
        $recent_read = new \TinyDb\Collection('\FSStack\Gruppe\Models\Notification', \TinyDb\Sql::create()
                                                                ->where('userID = ?', $this->userID)
                                                                ->where('is_read = 1')
                                                                ->limit(5)
                                                                ->order_by('notificationID DESC'));

        $arr = array();
        foreach ($recent_unread as $notif) {
            $arr[] = $notif;
        }

        foreach ($recent_read as $notif) {
            if (count($arr) < 5) {
                $arr[] = $notif;
            }
        }

        return $arr;
    }

    public function __get_unread_notifications()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Notification', \TinyDb\Sql::create()
                                                                ->where('is_read = 0')
                                                                ->where('userID = ?', $this->userID)
                                                                ->order_by('notificationID DESC'));
    }

    public function get_newsfeed_posts($count, $after = NULL)
    {
        $sql = \TinyDb\Sql::create()
              ->select('groups_posts.*')
              ->from('groups_posts')
              ->join('posts ON (posts.postID = groups_posts.postID)')
              ->where('groupID IN (SELECT groupID FROM `users_groups` WHERE (userID = ?))', $this->userID)
              ->where('posts.in_reply_to_postID IS NULL OR groups_posts.reposted_by_userID IS NOT NULL')
              ->order_by('created_at DESC')
              ->limit($count);

        if (isset($after)) {
            $sql->where('posts.postID < ?', $after);
        }

        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', $sql);
    }

    /**
     * Associates an email address with this user
     * @param  string $email_address The email address to associate
     * @return EmailAddress          User email mapping
     */
    public function associate_email($email_address)
    {
        return Models\User\EmailAddress::create($this, $email_address);
    }

    /**
     * Gets all groups the user is a member of. Magic getter function for $user->groups.
     * @return \TinyDb\Collection[Group] TinyDb collection populated with the groups the user is a member of.
     */
    public function __get_groups()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group', \TinyDb\Sql::create()
                                      ->join('users_groups ON (users_groups.groupID = groups.groupID)')
                                      ->where('userID = ?', $this->userID));
    }

    /**
     * Checks if the user is a member of a given group
     * @param  Group   $group The group to check
     * @return boolean        True if the user is in the group, false otherwise
     */
    public function is_member(Group $group)
    {
        try {
            $this->get_group_mapping($group);
            return TRUE;
        } catch (\TinyDb\NoRecordException $ex) {
            return FALSE;
        }
    }

    /**
     * Checks if the user is an owner of the group
     * @param  Group  $group The group to check
     * @return boolean       TRUE if the user owns the group, FALSE otherwise
     */
    public function owns_group(Group $group)
    {
        if (Models\User::is_logged_in() && Models\User::current()->userID == $this->userID && $this->is_admin) {
            return TRUE;
        }

        if (!$this->is_member($group)) {
            return FALSE;
        }

        return $this->get_group_mapping($group)->is_owner;
    }

    /**
     * Gets the mapping between the user and the group. Throws an exception if the user is not in the group.
     * @param  Group  $group The group to get the mapping for
     * @return User\Group    User-group mapping
     */
    public function get_group_mapping(Group $group)
    {
        return new Models\User\Group(array(
            'userID' => $this->userID,
            'groupID' => $group->groupID
        ));
    }

    public function join_group(Group $group, $is_owner = FALSE)
    {
        Models\User\Group::create(array(
            'userID' => $this->userID,
            'groupID' => $group->groupID,
            'is_owner' => $is_owner
        ));
    }

    public function __get_all_unjoined_groups()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group', \TinyDb\Sql::create()
                                      ->where('(SELECT COUNT(*) FROM users_groups WHERE users_groups.groupID = groups.groupID AND userID = ?) = 0', $this->userID)
                                      ->where('is_private = 0'));
    }

    /**
     * Votes on a post in a group
     * @param  Models\Group $group           Group the vote is being cast in
     * @param  Models\Post  $post            Post the vote is being cast on
     * @param  number       $vote            Vote, either -1, 0, or 1
     * @param  string       $downvote_reason Reason for a downvote, required if $vote is -1, ignored otherwise.
     * @return Vote                          The vote object which was cast
     */
    public function vote(Group $group, Post $post, $vote, $reason = NULL)
    {
        return User\Vote::create($this, $group, $post, $vote, $reason);
    }
}
