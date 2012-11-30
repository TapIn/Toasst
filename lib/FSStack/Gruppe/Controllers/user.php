<?php

namespace FSStack\Gruppe\Controllers;

use \FSStack\Gruppe\Models;

class User extends \CuteControllers\Base\Rest
{
    public function __post_register()
    {

    }

    public function __post_login()
    {

    }

    public function __get_fb_login()
    {
        $redirect_uri = \CuteControllers\Router::link('/user/fb_callback', TRUE);
        $redirect_uri = urlencode($redirect_uri);

        $client_id = '375793235846832';
        $url = "https://www.facebook.com/dialog/oauth/?client_id=$client_id&redirect_uri=$redirect_uri&scope=email";
        $this->redirect($url);
    }

    public function __get_fb_callback()
    {
        $redirect_uri = \CuteControllers\Router::link('/user/fb_callback', TRUE);

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

        try {
            $user = Models\User::get_from_email($fb_user->email);
        } catch (\TinyDb\NoRecordException $ex) {
            $user = Models\User::create($fb_user->first_name, $fb_user->last_name, $fb_user->email);
        }

        $user->first_name = $fb_user->first_name;
        $user->last_name = $fb_user->last_name;
        $user->associate_email($fb_user->email);
        $user->update();

        $user->login();

        $this->redirect('/');
    }
}
