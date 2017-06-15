<?php
namespace phorkie;

use Hybridauth\Provider\OpenID;

$noSecurityCheck = true;
require_once 'www-header.php';

if (isset($_REQUEST['logout'])) {
    unset($_SESSION);
    session_destroy();
    //delete last openid cookie.
    // if you deliberately log out, you do not want to be logged in
    // automatically on the next page reload.
    setcookie('lastopenid', '0', time() - 3600);

    header('Location: ' . Tools::fullUrl());
    exit();
}

$bAutoLogin = false;
if (isset($_GET['autologin']) && $_GET['autologin']
    && isset($_COOKIE['lastopenid'])
) {
    $bAutoLogin = true;
    // autologin=1: start openid autologin
    // autologin=2: response from openid server
    if ($_GET['autologin'] == 1) {
        $_POST['openid_url'] = $_COOKIE['lastopenid'];
    }
}

if (!count($_GET) && !count($_POST)) {
    render(
        'login',
        array(
            'openid' => isset($_COOKIE['lastopenid'])
                ? $_COOKIE['lastopenid']
                : $GLOBALS['phorkie']['auth']['openid_url']
                ?: 'http://'
        )
    );
    exit();
}

// Hackaround Non-Javascript Login Page
if (!count($_POST) && isset($_GET['openid_url'])) {
    $_POST = $_GET;
}

if (! empty($_POST['openid_url'])) {
    $openid_url = $_POST['openid_url'];
} else if (! empty($_SESSION['openid_url'])) {
    $openid_url = $_SESSION['openid_url'];
} else {
    $openid_url = $GLOBALS['phorkie']['auth']['openid_url'];
}

$realm    = Tools::fullUrl();
$returnTo = Tools::fullUrl('login.php');
if ($bAutoLogin) {
    $returnTo = Tools::fullUrl('login?autologin=2');
}

// Enable OpenID adapter on Hybrid_Auth config
$ha = new OpenID(array(
    'callback' => $returnTo,
    'openid_identifier' => $openid_url,
));

try {
    $ha->authenticate();

    $userProfile = $ha->getUserProfile();
    $_SESSION['email']    = $userProfile->email ?: $GLOBALS['phorkie']['auth']['anonymousEmail'];
    $_SESSION['name']     = $userProfile->displayName ?: $_SERVER['REMOTE_ADDR'];
    $_SESSION['identity'] = $userProfile->identifier;
} catch(\Exception $e) {
    throw new Exception($e->getMessage());
}

setcookie('tried-autologin', '0', time() - 3600);//delete
setcookie('lastopenid', $_SESSION['identity'], time() + 84600 * 60);

if ($bAutoLogin) {
    $alres = new Login_AutologinResponse('ok');
    $alres->name     = $_SESSION['name'];
    $alres->identity = $_SESSION['identity'];
    $alres->send();
    exit(0);
}


$url = '';
if (isset($_SESSION['REQUEST_URI'])) {
    $url = substr($_SESSION['REQUEST_URI'], 1);
}
$redirect = Tools::fullUrl($url);
header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
exit;
?>
