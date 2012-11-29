<?php

namespace FSStack\Gruppe\Models;

/**
 * Stores information about a post
 */
class Post extends \TinyDb\Orm
{
    public static $table_name = 'posts';
    public static $primary_key = 'postID';

    protected $postID;

    /**
     * Optional title of the post
     * @var string
     */
    protected $title;
    /**
     * Markdown content of the post
     * @var string
     */
    protected $markdown;
    /**
     * Link to the image, if one exists
     * @var string
     */
    protected $image;
    /**
     * Link to the video, if one exists
     * @var string
     */
    protected $video;
    /**
     * Link to the content, if provided
     * @var string
     */
    protected $link;
    /**
     * Optional caption, if the post is not a text post
     * @var string
     */
    protected $caption;

    /**
     * Gets the type of the post, either 'image', 'video', 'link', or 'text'. Magic getter for $post->type
     * @return string Type of the post
     */
    public function __get_type()
    {
        if (isset($this->image)) {
            return 'image';
        } else if (isset($this->video)) {
            return 'video';
        } else if (isset($this->link)) {
            return 'link';
        } else {
            return 'text';
        }
    }

    /**
     * ID of the post the post is in reply to
     * @var number
     */
    protected $in_reply_to_postID;

    /**
     * Checks if the current post is a reply. Magic getter for $post->is_reply
     * @return boolean TRUE if the post is a reply, FALSE otherwise
     */
    public function __get_is_reply()
    {
        return isset($this->in_reply_to_postID);
    }

    /**
     * The post the post is in reply to. Magic getter for $post->in_reply_to_post
     * @return Post The post which the current post is in reply to
     */
    public function __get_in_reply_to_post()
    {
        if ($this->is_reply) {
            return new Post($this->in_reply_to_postID);
        } else {
            throw new \Exception("Not a reply");
        }
    }

    /**
     * Gets the replies to this post. Magic getter for $post->replies
     * @return \TinyDb\Collection[Post] Collection of posts in reply
     */
    public function __get_replies()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Post', \TinyDb\Sql::create()
                                      ->where('in_reply_to_postID = ?', $this->postID));
    }

    /**
     * Gets the reposts of this post. Magic getter for $post->reposts
     * @return \TinyDb\Collection[Group\Post] Instances of the post which are not the original
     */
    public function __get_reposts()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', \TinyDb\Sql::create()
                                      ->where('postID = ?', $this->postID)
                                      ->where('reposted_by_userID IS NOT NULL'));
    }

    /**
     * Gets all instances of this post, including the original. Magic getter for $post->all_post_instances
     * @return \TinyDb\Collection[Group\Post] Instances of the post
     */
    public function __get_all_post_instances()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Group\Post', \TinyDb\Sql::create()
                                      ->where('postID = ?', $this->postID));
    }
}
