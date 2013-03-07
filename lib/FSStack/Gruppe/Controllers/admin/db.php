<?php

namespace FSStack\Gruppe\Controllers\invites;

use \FSStack\Gruppe\Models;

class Db extends \CuteControllers\Base\Rest
{
    public function before()
    {
        if (!Models\User::current()->is_admin) {
            throw new \CuteControllers\HttpError(403);
        }
    }

    public function __get_index()
    {
        echo <<<END

<!DOCTYPE html>
<html>
<head>
    <title>Redirecting you...</title>
</head>
<body onload="document.getElementById('loginForm').submit()">
    <h1>Please wait while you are logged in...</h1>
    <form id="loginForm" method="post" action="/myadmin/">
        <input type="hidden" name="pma_username" value="root" />
        <input type="hidden" name="pma_password" value="password" />
        <input type="submit" value="Continue..." />
    </form>
</body>
</html>

END;
    }
}
