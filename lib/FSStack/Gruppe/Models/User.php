<?php

namespace FSStack\Gruppe\Models;

use \FSStack\Models\Gruppe\Models\User;

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
     * The user's middle name.
     * @var string
     */
    protected $middle_name;
    /**
     * The user's last name.
     * @var string
     */
    protected $last_name;

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
     * The user's birthday timestamp.
     * @var number
     */
    protected $birthday;
    /**
     * The user's gender, one of: male, female, undefined. Currently unused.
     * @var string
     */
    protected $gender;
    /**
     * The user's location. Currently unused.
     * @var string
     */
    protected $location;
    /**
     * The user's "about me" text. Currently unused.
     * @var string
     */
    protected $about;

    /**
     * The user's display language ID
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
     * Creates an instance of the class.
     * @param mixed     $lookup     * If the paramater is null, the object will be uninitialized. Otherwise:
     *                              * If the paramater is a string containing an @, a lookup will be performed
     *                                on the user's email address. The first result will be returned.
     *                              * If the paramater is an associative array, and a lookup will be performed
     *                                on the database for (WHERE `key` = 'val' AND `key` = 'val' ...). The
     *                                first result will be returned.
     *                              * If the paramater is a non-associative array, and the primary key is also
     *                                an array, the paramater will be treated as values for the primary keys,
     *                                and populated as specified above.
     *                              * If the paramater is not an array, or the table has a single primary key,
     *                                the paramater will be cast as a string, and be used as a match for the
     *                                primary key. The first result will populate the database.
     */
    public function __construct($lookup = NULL)
    {
        if (is_string($lookup) && strstr($lookup, '@')) { // Allow instantiation by email address
            try {
                $email = new User\EmailAddress($lookup);
                return $email->user;
            } catch (\TinyDb\NoRecordException $ex) {
                throw \TinyDb\NoRecordException(); // Rethrow to fix the stack trace
            }
        } else {
            return new self($lookup);
        }
    }

    /**
     * Gets all groups the user is a member of. Magic getter function for $user->groups.
     * @return \TinyDb\Collection[Group] TinyDb collection populated with the groups the user is a member of.
     */
    public function __get_groups()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group', \TinyDb\Sql::create()
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