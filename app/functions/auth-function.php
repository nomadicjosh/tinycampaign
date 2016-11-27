<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Core\Exception\NotFoundException;
use app\src\Core\Exception\UnauthorizedException;
use Cascade\Cascade;

/**
 * tinyCampaign Auth Helper
 *
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
function hasPermission($perm)
{
    $acl = new \app\src\ACL(get_userdata('id'));

    if ($acl->hasPermission($perm) && is_user_logged_in()) {
        return true;
    } else {
        return false;
    }
}

function get_userdata($field)
{
    $app = \Liten\Liten::getInstance();
    $user = get_secure_cookie_data('TC_COOKIENAME');
    $q = $app->db->user()
        ->where('user.id = ?', $user->id)->_and_()
        ->where('user.uname = ?', $user->uname)
        ->find();
    foreach ($q as $r) {
        return _h($r->{$field});
    }
}

/**
 * Checks if a visitor is logged in or not.
 * 
 * @since 2.00
 * @return boolean
 */
function is_user_logged_in()
{
    $app = \Liten\Liten::getInstance();

    $user = get_user_by('id', get_userdata('id'));

    if ('' != $user->id && $app->cookies->verifySecureCookie('TC_COOKIENAME')) {
        return true;
    }

    return false;
}

/**
 * Retrieve user info by a given field from the user's table.
 *
 * @since 2.0.0
 * @param string $field The field to retrieve the user with.
 * @param int|string $value A value for $field (userID, uname or email).
 */
function get_user_by($field, $value)
{
    $app = \Liten\Liten::getInstance();

    $user = $app->db->user()
        ->where("user.$field = ?", $value)
        ->findOne();

    return $user;
}

/**
 * Logs a user in after the login information has checked out.
 *
 * @since 2.0.0
 * @param string $login User's username or email address.
 * @param string $password User's password.
 * @param string $rememberme Whether to remember the user.
 */
function tc_authenticate($login, $password, $rememberme)
{
    $app = \Liten\Liten::getInstance();

    $user = $app->db->user()
        ->where('(user.uname = ? OR user.email = ?)', [$login, $login])
        ->findOne();

    if (false == $user) {
        $app->flash('error_message', _t('The account does not exist'));
        redirect($app->req->server['HTTP_REFERER']);
        return;
    }

    $ll = $app->db->user();
    $ll->LastLogin = $ll->NOW();
    $ll->where('id = ?', _h($user->id))->update();
    /**
     * Filters the authentication cookie.
     * 
     * @since 2.0.0
     * @param object $user Person data object.
     * @param string $rememberme Whether to remember the person.
     * @throws Exception If $user is not a database object.
     */
    try {
        $app->hook->{'apply_filter'}('tc_auth_cookie', $user, $rememberme);
    } catch (UnauthorizedException $e) {
        Cascade::getLogger('error')->error(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $e->getCode(), $e->getMessage()));
    }

    tc_logger_activity_log_write('Authentication', 'Login', get_name(_h($user->id)), _h($user->uname));
    redirect(get_base_url());
}

/**
 * Checks a user's login information.
 *
 * @since 2.0.0
 * @param string $login Person's username or email address.
 * @param string $password Person's password.
 * @param string $rememberme Whether to remember the person.
 */
function tc_authenticate_user($login, $password, $rememberme)
{
    $app = \Liten\Liten::getInstance();

    if (empty($login) || empty($password)) {

        if (empty($login)) {
            $app->flash('error_message', _t('<strong>ERROR</strong>: The username/email field is empty.'));
        }

        if (empty($password)) {
            $app->flash('error_message', _t('<strong>ERROR</strong>: The password field is empty.'));
        }

        redirect(get_base_url());
        return;
    }

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $user = get_user_by('email', $login);

        if (false == $user->email) {
            $app->flash('error_message', _t('<strong>ERROR</strong>: Invalid email address.'));

            redirect(get_base_url());
            return;
        }
    } else {
        $user = get_user_by('uname', $login);

        if (false == $user->uname) {
            $app->flash('error_message', _t('<strong>ERROR</strong>: Invalid username.'));

            redirect(get_base_url());
            return;
        }
    }

    if (!tc_check_password($password, $user->password, _h($user->id))) {
        $app->flash('error_message', _t('<strong>ERROR</strong>: The password you entered is incorrect.'));

        redirect(get_base_url());
        return;
    }

    /**
     * Filters log in details.
     * 
     * @since 2.0.0
     * @param string $login Person's username or email address.
     * @param string $password Person's password.
     * @param string $rememberme Whether to remember the person.
     */
    $user = $app->hook->{'apply_filter'}('tc_authenticate_user', $login, $password, $rememberme);

    return $user;
}

function tc_set_auth_cookie($user, $rememberme = '')
{

    $app = \Liten\Liten::getInstance();

    if (!is_object($user)) {
        throw new UnauthorizedException(_t('"$user" should be a database object.'), 4011);
    }

    if (isset($rememberme)) {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         * 
         * @since 2.0.0
         */
        $expire = $app->hook->{'apply_filter'}('auth_cookie_expiration', (_h(get_option('cookieexpire')) !== '') ? _h(get_option('cookieexpire')) : $app->config('cookies.lifetime'));
    } else {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         *
         * @since 2.0.0
         */
        $expire = $app->hook->{'apply_filter'}('auth_cookie_expiration', ($app->config('cookies.lifetime') !== '') ? $app->config('cookies.lifetime') : 86400);
    }

    $auth_cookie = [
        'key' => 'TC_COOKIENAME',
        'id' => _h($user->id),
        'uname' => _h($user->uname),
        'remember' => (isset($rememberme) ? $rememberme : _t('no')),
        'exp' => $expire + time()
    ];

    /**
     * Fires immediately before the secure authentication cookie is set.
     *
     * @since 2.0.0
     * @param string $auth_cookie Authentication cookie.
     * @param int    $expire  Duration in seconds the authentication cookie should be valid.
     */
    $app->hook->{'do_action'}('set_auth_cookie', $auth_cookie, $expire);

    $app->cookies->setSecureCookie($auth_cookie);
}

/**
 * Removes all cookies associated with authentication.
 * 
 * @since 2.0.0
 */
function tc_clear_auth_cookie()
{

    $app = \Liten\Liten::getInstance();

    /**
     * Fires just before the authentication cookies are cleared.
     *
     * @since 2.0.0
     */
    $app->hook->{'do_action'}('clear_auth_cookie');

    $vars1 = [];
    parse_str($app->cookies->get('TC_COOKIENAME'), $vars1);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file1 = $app->config('cookies.savepath') . 'cookies.' . $vars1['data'];
    try {
        if (tc_file_exists($file1)) {
            unlink($file1);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    $vars2 = [];
    parse_str($app->cookies->get('SWITCH_USERBACK'), $vars2);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file2 = $app->config('cookies.savepath') . 'cookies.' . $vars2['data'];
    if (tc_file_exists($file2, false)) {
        @unlink($file2);
    }

    /**
     * After the cookie is removed from the server,
     * we know need to remove it from the browser and
     * redirect the user to the login page.
     */
    $app->cookies->remove('TC_COOKIENAME');
    $app->cookies->remove('SWITCH_USERBACK');
}

/**
 * Shows error messages on login form.
 * 
 * @since 2.0.0
 */
function tc_login_form_show_message()
{
    $app = \Liten\Liten::getInstance();
    $flash = new \app\src\tc_Messages();
    echo $app->hook->{'apply_filter'}('login_form_show_message', $flash->showMessage());
}

/**
 * Retrieves data from a secure cookie.
 * 
 * @since 2.0.0
 * @param string $key COOKIE key.
 * @return mixed
 */
function get_secure_cookie_data($key)
{
    $app = \Liten\Liten::getInstance();
    $data = $app->cookies->getSecureCookie($key);
    return $data;
}
