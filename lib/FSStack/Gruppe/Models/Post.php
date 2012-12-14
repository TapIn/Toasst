<?php

namespace FSStack\Gruppe\Models;

use \FSStack\Gruppe\Models;

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
    protected $markdown_cache;
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
     * ID of the user who made the original post
     * @var string
     */
    protected $userID;

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

    protected $thumbnail_url;

    protected $embed_html;

    /**
     * Gets the user object for the user who made the original post. Magic getter for $post->user.
     * @return User User object for the user who made the original post.
     */
    public function __get_user()
    {
        return new User($this->userID);
    }

    /**
     * Creates a new post in the specified group
     * @param  string $type    Type of the content, either 'image', 'video', 'link', or 'text'
     * @param  string $title   Optional title of the post
     * @param  string $content Content, varies based on the type
     * @param  Group  $group   The group to post in
     * @return Post            Created post object
     */
    public static function create(Models\User $user, $title = NULL, $content, Group $group)
    {
        $model_data = array(
            'title' => $title,
            'userID' => $user->userID
        );

        $link = NULL;
        $lines = split("\n", $content);

        if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $content)) {
            $link = $content;
        } else if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $lines[0])) {
            $link = $lines[0];
            $content = substr($content, strlen($link) + 1);
        }

        if ($link) {
            $oembed = (Array)\Application::$embedly->oembed($link);

            $type = $oembed['type'];
            if ($type === 'photo') {
                $type = 'image';
            }

            if ($type === 'rich') {
                $type = 'video';
            }

            if (isset($oembed['thumbnail_url'])) {
                $model_data['thumbnail_url'] = $oembed['thumbnail_url'];
            }

            if (!isset($title)) {
                if (isset($oembed['title'])) {
                    $model_data['title'] = $oembed['title'];
                } else {
                    $model_data['title'] = $link;
                }
            }
        }

        switch ($type) {
            case 'image':
                $model_data['image'] = $link;
                break;
            case 'video':
                $model_data['video'] = $link;
                $model_data['embed_html'] = $oembed['html'];
                break;
            case 'link':
                $model_data['link'] = $link;
                break;
        }

        if ($content) {
            $model_data['markdown'] = $content;
            $model_data['markdown_cache'] = SmartyPants(Markdown($content));
        }

        $model = parent::create($model_data);
        Models\Group\Post::create($group, $model);
        $user->vote($group, $model, 1);

        return $model;
    }

    /**
     * Reposts the post to the specified group
     * @param  Group  $group            Group to repost to
     * @param  User   $reposted_by_user User who is reposting
     * @return Models\Group\Post               Mapping from group to post
     */
    public function repost(Group $group, User $reposted_by_user)
    {
        return Models\Group\Post::create($group, $this, $reposted_by_user);
    }

    /**
     * Gets the type of the post, either 'image', 'video', 'link', or 'text'. Magic getter for $post->type
     * @return string Type of the post
     */
    public function __get_type()
    {
        trigger_error('->type is no longer supported; posts can now have multiple types.', E_USER_DEPRECATED);
        if ($this->image) {
            return 'image';
        } else if ($this->video) {
            return 'video';
        } else if ($this->link) {
            return 'link';
        } else {
            return 'text';
        }
    }

    /**
     * Gets the content of the post, which varies depending on the type. Magic getter for $post->content
     * @return string Content of the post
     */
    public function __get_content( $original = FALSE )
    {
        trigger_error('Use rendered_markdown instead of ->content.', E_USER_DEPRECATED);
        switch ($this->type) {
            case 'image':
                return $this->image;
            case 'video':
                return $this->video;
            case 'link':
                return $this->link;
            case 'text':
                if ($original) {
                    return $this->markdown;
                } else {
                    return $this->rendered_markdown;
                }
        }
    }

    public function __get_rendered_markdown()
    {
        if (!$this->markdown_cache) {
            $this->markdown_cache = SmartyPants(Markdown($this->markdown));
            $this->invalidate('markdown_cache');
            $this->update();
        }

        return $this->markdown_cache;
    }

    public function __get_original_content()
    {
        return $this->__get_content(TRUE);
    }

    /**
     * Gets the rendered markdown. Magic getter for $post->html
     * @return string The html produced by the markdown
     */
    public function __get_html()
    {
        if ($this->type !== 'text') {
            throw new \Exception("Not a text post!");
        }

        // TODO
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
        return $this->in_reply_to_postID;
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
     * @return \TinyDb\Collection[Models\Group\Post] Instances of the post which are not the original
     */
    public function __get_reposts()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Models\Group\Post', \TinyDb\Sql::create()
                                      ->where('postID = ?', $this->postID)
                                      ->where('reposted_by_userID IS NOT NULL'));
    }

    /**
     * Gets all instances of this post, including the original. Magic getter for $post->all_post_instances
     * @return \TinyDb\Collection[Models\Group\Post] Instances of the post
     */
    public function __get_all_post_instances()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Models\Group\Post', \TinyDb\Sql::create()
                                      ->where('postID = ?', $this->postID));
    }
}
