<?php

namespace FSStack\Gruppe\Models\User;

use \FSStack\Gruppe\Models;

/**
 * Tracks a vote on a post in a group
 */
class Vote extends \TinyDb\Orm
{
    public static $table_name = 'users_votes';
    public static $primary_key = array('userID', 'groupID', 'postID'); // Posts have votes per-group, so off-topic posts
                                                                       // can be down-voted without harming rank
                                                                       // elsewhere. Score is also cached in Group\Post.

    /**
     * ID of the user who cast the vote
     * @var number
     */
    protected $userID;
    /**
     * ID of the group the vote was cast in
     * @var number
     */
    protected $groupID;
    /**
     * ID of the post the vote was cast on
     * @var number
     */
    protected $postID;
    /**
     * Vote, either -1, 0, or 1
     * @var number
     */
    protected $vote;
    /**
     * Reason for the downvote
     * @var string
     */
    protected $downvote_reason;

    /**
     * If the vote does not exist, create it. Else, update it.
     * @param  Models\User  $user            Use who is casting the vote
     * @param  Models\Group $group           Group the vote is being cast in
     * @param  Models\Post  $post            Post the vote is being cast on
     * @param  number       $vote            Vote, either -1, 0, or 1
     * @param  string       $downvote_reason Reason for a downvote. Currently unused.
     * @return Vote                          The vote object
     */
    public static function create(Models\User $user, Models\Group $group, Models\Post $post, $vote,
                                  $downvote_reason = NULL)
    {
        /*
            // Check that the user provided a downvote reason if they downvoted.
            // (If they upvoted, clear any provided reason.)
            if ($vote > 0) {
                $downvote_reason = NULL;
            } else if ($vote > 0 && !isset($downvote_reason)) {
                throw new \Exception('Downvote reason is required.');
            }
        */

        if ($vote > 1 || $vote < -1 || !is_integer($vote)) {
            throw new \Exception('Vote must be an integer from -1 to 1.');
        }

        $group_post = new Models\Group\Post(array(
            'groupID' => $group->groupID,
            'postID' => $post->postID
        ));

        if (static::exists(array(
                'userID' => $user->userID,
                'groupID' => $group->groupID,
                'postID' => $post->postID
            ))) {

            $model = new static(array(
                'userID' => $user->userID,
                'groupID' => $group->groupID,
                'postID' => $post->postID
            ));

            // Update the vote cache:
            $previous = $model->vote;
            $adjustment = ($previous * -1) + $vote;
            $group_post->vote += $adjustment;
            $group_post->update();

            $model->vote = $vote;
            $model->downvote_reason = $downvote_reason;
            $model->update();

            return $model;
        } else {
            // Update the vote cache
            $group_post->vote += $vote;
            $group_post->update();

            return parent::create(array(
                'userID' => $user->userID,
                'groupID' => $group->groupID,
                'postID' => $post->postID,
                'vote' => $vote,
                'downvote_reason' => $downvote_reason
            ));
        }
    }

    /**
     * The user who cast the vote. Magic getter for $vote->user
     * @return Models\User User who cast the vote
     */
    public function __get_user()
    {
        return new Models\User($this->userID);
    }

    /**
     * The group the vote was cast in. Magic getter for $vote->group
     * @return Models\Group Group the vote was cast in
     */
    public function __get_group()
    {
        return new Models\Group($this->groupID);
    }

    /**
     * The post the vote was cast on. Magic getter for $vote->post
     * @return Models\Post Post the vote was cast on
     */
    public function __get_post()
    {
        return new Models\Post($this->postID);
    }
}
