<?php

namespace FSStack\Gruppe\Controllers\user;

use \FSStack\Gruppe\Models;

class Login extends \CuteControllers\Base\Rest
{
    public function __get_index()
    {
        $this->redirect('/user/login/fb.bread');
    }

    public function __get_fb()
    {
        $invite = $this->request->request('invite');
        if ($invite) {
            $redirect_uri = \CuteControllers\Router::link("/user/login/fb_callback.bread?invite=$invite", TRUE);
        } else {
            $redirect_uri = \CuteControllers\Router::link("/user/login/fb_callback.bread", TRUE);
        }
        $redirect_uri = urlencode($redirect_uri);

        $client_id = '375793235846832';
        $url = "https://www.facebook.com/dialog/oauth/?client_id=$client_id&redirect_uri=$redirect_uri&scope=email";
        $this->redirect($url);
    }

    public function __get_fb_callback()
    {
        $invite = $this->request->request('invite');
        if ($invite) {
            $redirect_uri = \CuteControllers\Router::link("/user/login/fb_callback.bread?invite=$invite", TRUE);
        } else {
            $redirect_uri = \CuteControllers\Router::link("/user/login/fb_callback.bread", TRUE);
        }
        $redirect_uri = urlencode($redirect_uri);

        $code = $this->request->get('code');
        $client_id = '375793235846832';
        $client_secret = '87fa6912c3369e6f11420dd23c11baff';

        $url = "https://graph.facebook.com/oauth/access_token?client_id=$client_id&client_secret=$client_secret&code=$code&redirect_uri=$redirect_uri";
        $response = file_get_contents($url);
        $params = null;
        parse_str($response, $params);

        $user->fb_access_token = $params['access_token'];
        $graph_url = "https://graph.facebook.com/me?access_token=" . $params['access_token'];
        $fb_user = json_decode(file_get_contents($graph_url));

        $redirect_group = NULL;
        try {
            $user = Models\User::get_from_email($fb_user->email);
            try {
                $invite_obj = new Models\Invite($invite);
                foreach ($invite_obj->to_join_groups as $group) {
                    try {
                        $user->join_group($group);
                    } catch (\Exception $ex) {}
                    if ($redirect_group === NULL) {
                        $redirect_group = $group;
                    }
                }
            } catch (\Exception $ex) {
            }
        } catch (\TinyDb\NoRecordException $ex) {
            if ($invite) {
                try {
                    $invite_obj = new Models\Invite($invite);
                    $user = Models\User::create($fb_user->first_name, $fb_user->last_name, $fb_user->email);
                    foreach ($invite_obj->to_join_groups as $group) {
                        $user->join_group($group);
                    }
                } catch (\Exception $ex) {
                    echo "Your code was invalid. $ex";
                }
            } else {
                echo "Sorry, you need an invite code to register.";
                exit;
            }
        }

        $user->first_name = $fb_user->first_name;
        $user->last_name = $fb_user->last_name;
        $user->fb_id = $fb_user->id;

        if (isset($fb_user->bio)) {
            $user->about = $fb_user->bio;
        }

        if (isset($fb_user->gender)) {
            $user->gender = $fb_user->gender;
        } else {
            $user->gender = 'undefined';
        }

        $user->image = 'http://graph.facebook.com/' . $fb_user->id . '/picture?type=large';

        if (isset($fb_user->username)) {
            $user->handle = $fb_user->username;
        }

        $user->fb_access_token = $params['access_token'];
        $user->associate_email($fb_user->email);
        $user->update();

        $user->login();
        setcookie('is_returning_user', 'yes', time() + (60 * 60 * 24 * 31), '/', 'toasst.com');

        if ($redirect_group !== NULL) {
            $this->redirect('/g/' . $redirect_group->link_name);
        } else {
            $this->redirect('/');
        }
    }
}
