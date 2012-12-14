<?php

namespace FSStack\Gruppe\Controllers\user;

use \FSStack\Gruppe\Models;

class Cron extends \CuteControllers\Base\Rest
{
    public function before()
    {
        header("Content-type: text/plain");
    }

    public function __get_email()
    {
        $to_send_users = new \TinyDb\Collection('\FSStack\Gruppe\Models\User', \TinyDb\Sql::create()
                                                  ->where('(select created_at from `users_logins`
                                                                where `users_logins`.`userID` = `users`.`userID`
                                                                order by `created_at` desc
                                                                limit 1) between date_sub(now(), interval 14 day) and date_sub(now(), interval 2 day)')
                                                   );
        foreach ($to_send_users as $user) {
            $email = $user->emails[0]->email;
            echo "Sending email to " . $email . "...\n";
        }
    }

    public function __get_fb()
    {
        $to_send_users = new \TinyDb\Collection('\FSStack\Gruppe\Models\User', \TinyDb\Sql::create()
                                                  // Look for users who haven't logged in in the last two days, but who
                                                  // haven't be inactive for more than two weeks
                                                  ->where('(select created_at from `users_logins`
                                                                where `users_logins`.`userID` = `users`.`userID`
                                                                order by `created_at` desc
                                                                limit 1) between date_sub(now(), interval 14 day) and date_sub(now(), interval 2 day)')
                                                  // Look for users we haven't sent a notification in the last week
                                                  ->where('(select count(*) from `users_notifications_sent`
                                                                where `users_notifications_sent`.`userID` = `users`.`userID`
                                                                and `users_notifications_sent`.`notification_type` = "fb_notification_digest"
                                                                and `created_at` > date_sub(now(), interval 7 day)
                                                                order by created_at desc
                                                                limit 1) = 0')
                                                   // Look for users with unread notifications
                                                   ->where('(select count(*) from `notifications`
                                                                where `notifications`.`userID` = `users`.`userID`
                                                                and `notifications`.`is_read` = 0) > 0')
                                                   );

        if (!$this->request->request('dry_run' == 'false')) {
            echo "Dry run. Pass ?dry_run=false to actually send.\n\n";
        }

        echo "Sending messages:\n";

        foreach ($to_send_users as $user)
        {
            echo "Sending message to " . $user->name . "...\n";
            $message = "";

            $notifications = $user->unread_notifications;

            $content = $notifications[0]->post->rendered_markdown;
            $content = strip_tags($content);

            $max_length = 60;
            if (strlen($content) > $max_length) {
                $content = substr($content, $max_length - 6) . '...';
            }

            $message = '@[' . $notifications[0]->source_user->fb_id . '] replied to your post in ' .
                        $notifications[0]->group->name . ': "' . $content . '"';

            if (count($notifications) > 1) {
                $message .= " and " . (count($notifications) - 1) . " more unread notification" . (count($notifications) > 2? 's' : '');
            }

            $message .= '.';

            if ($this->request->request('dry_run' == 'false')) {
                $user->send_facebook_push($message, '/g/' . $notifications[0]->group->groupID . '/t/' . $notifications[0]->post->in_reply_to_postID . '.bread#' . $notifications[0]->postID);
            }
        }

        echo "Done";
    }
}
