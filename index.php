<?php
session_start();
ob_start();

header("Content-type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Initialize the class loader
require_once('lib/SplClassLoader.php');
$loader = new SplClassLoader(NULL, 'lib');
$loader->register();

ini_set('display_errors', 1);

// TODO: This should all be pulled out into a config file

// Initialize the database
\TinyDb\Db::set('mysqli://root:password@localhost/gruppe');

date_default_timezone_set('America/Los_Angeles');

// Set some defines
define('WEB_DIR', dirname(__FILE__));

define('INCLUDES_DIR', WEB_DIR . '/lib');
set_include_path(INCLUDES_DIR . PATH_SEPARATOR . get_include_path());

define('WEB_URI', \CuteControllers\Router::link('/', TRUE));

define('ASSETS_DIR', WEB_DIR . '/assets');
define('ASSETS_URI', WEB_URI . '/assets');

define('UPLOADS_DIR', WEB_DIR . '/uploads');
define('UPLOADS_URI', WEB_URI . '/uploads');

define('TEMPLATE_DIR', ASSETS_DIR . '/tpl');
define('TEMPLATE_URL', ASSETS_URI . '/tpl');

define ('TEMPLATE_TEMP_DIR', WEB_DIR . '/.tmp/tpl');

// Load the template engine
$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig_conifg = array(
    'debug' => TRUE
);
require_once(INCLUDES_DIR . '/Markdown.php');
require_once(INCLUDES_DIR . '/SmartyPants.php');

\Application::$twig = new Twig_Environment($loader, $twig_conifg);
\Application::$twig->addExtension(new Twig_Extension_Debug());

$current = explode('/', \CuteControllers\Request::current()->uri);
$current = '/' . $current[count($current) - 1];
\Application::$twig->addGlobal('current_page', $current);

\Application::$twig->addGlobal('is_logged_in', \FSStack\Gruppe\Models\User::is_logged_in());
if (\FSStack\Gruppe\Models\User::is_logged_in()) {
    \Application::$twig->addGlobal('current_user', \FSStack\Gruppe\Models\User::current());
}

include_once('twig_filters.php');

\CuteControllers\Router::rewrite('/g/(.*)/t/([^/\.]*)(.*)?', 'post/$3?__groupID=$1&__postID=$2');
\CuteControllers\Router::rewrite('/g/([^/\.]*)(.*)?', '/group/$2?__groupID=$1');
\CuteControllers\Router::rewrite('/u/([^/\.]*)(.*)?', '/user/$2?__userID=$1');

// Start routing
try {
    \CuteControllers\Router::start(INCLUDES_DIR . '/FSStack/Gruppe/Controllers');
} catch (\CuteControllers\HttpError $err) {
    if ($err->getCode() == 401) {
        \CuteControllers\Router::redirect('/user/login.bread');
    } else {
        Header("Status: " . $err->getCode() . " " . $err->getMessage());
        if ($err->getCode() == 404) {
            $error = "File not found, " . \FSStack\Gruppe\Models\User::current()->name . "!";
        } else {
            $error = "Error: " . $err->getMessage();
        }
        ob_clean();

    exit;
    echo $error;
    exit;
        \Application::$twig->render('error.html.twig', array('error' => $error));
    }
} catch (\Exception $ex) {
    $error = "Error:<br />" . nl2br($ex);
    ob_clean();
    echo $error;
    exit;
    \Application::$twig->render('error.html.twig', array('error' => $error));
}
