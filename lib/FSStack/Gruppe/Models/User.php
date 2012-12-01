<?php

namespace FSStack\Gruppe\Models;

use \FSStack\Gruppe\Models\Group;
use \FSStack\Gruppe\Models\User;

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
        $email = new User\EmailAddress(array('email' => $lookup));
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
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Post', \TinyDb\Sql::create()
                                      ->where('userID = ?', $this->userID));
    }

    public function __get_unread_notifications()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Notification', \TinyDb\Sql::create()
                                                                ->where('is_read = 0')
                                                                ->where('userID = ?', $this->userID));
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
        return User\EmailAddress::create($this, $email_address);
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
        $collection = new \TinyDb\Collection('\FSStack\Gruppe\Models\User', \TinyDb\Sql::create()
                                             ->where('groupID = ?', $group->groupID)
                                             ->where('userID = ?', $this->userID)
                                             ->limit(1));
        return count($collection) > 0;
    }

    /**
     * Votes on a post in a group
     * @param  Models\Group $group           Group the vote is being cast in
     * @param  Models\Post  $post            Post the vote is being cast on
     * @param  number       $vote            Vote, either -1, 0, or 1
     * @param  string       $downvote_reason Reason for a downvote, required if $vote is -1, ignored otherwise.
     * @return Vote                          The vote object which was cast
     */
    public function vote(Group $group, Post $post, $vote, $reason)
    {
        return User\Vote::create($this, $group, $post, $vote, $reason);
    }
}
