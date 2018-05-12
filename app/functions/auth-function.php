<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\NotFoundException;
use TinyC\Exception\UnauthorizedException;
use TinyC\Exception\Exception;
use PDOException as ORMException;
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
    $acl = new \TinyC\ACL(get_userdata('id'));

    if ($acl->hasPermission($perm) && is_user_logged_in()) {
        return true;
    } else {
        return false;
    }
}

function ae($perm)
{
    if (!hasPermission($perm)) {
        return ' style="display:none !important;"';
    }
}

function ie($perm)
{
    if (hasPermission($perm)) {
        return ' style="display:none !important;"';
    }
}

function get_userdata($field)
{
    try {
        $auth = get_secure_cookie_data('TC_COOKIENAME');
        $user = app()->db->user()
            ->where('user.id = ?', $auth->id)->_and_()
            ->where('user.uname = ?', $auth->uname)
            ->findOne();
        return _escape($user->{$field});
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
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
    $user = get_user_by('id', get_userdata('id'));

    if ('' != $user->id && app()->cookies->verifySecureCookie('TC_COOKIENAME')) {
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
    try {
        $user = app()->db->user()
            ->select('user.*,role.roleName,role.permission')
            ->_join('role', 'user.roleID = role.id')
            ->where("user.$field = ?", $value)
            ->findOne();

        return $user;
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
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
    try {
        $user = app()->db->user()
            ->where('(user.uname = ? OR user.email = ?)', [$login, $login])->_and_()
            ->where('user.status = "1"')
            ->findOne();

        if (false == $user) {
            _tc_flash()->error(_t('Your account is inactive.'), app()->req->server['HTTP_REFERER']);
            return;
        }

        $ll = app()->db->user();
        $ll->LastLogin = \Jenssegers\Date\Date::now();
        $ll->where('id = ?', _escape($user->id))
            ->update();
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
        return false;
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
        return false;
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
        return false;
    }
    /**
     * Filters the authentication cookie.
     * 
     * @since 2.0.0
     * @param object $user User data object.
     * @param string $rememberme Whether to remember the person.
     * @throws Exception If $user is not a database object.
     */
    try {
        app()->hook->{'apply_filter'}('tc_auth_cookie', $user, $rememberme);
    } catch (UnauthorizedException $e) {
        Cascade::getLogger('error')->error(sprintf('AUTHSTATE[%s]: Unauthorized: %s', $e->getCode(), $e->getMessage()));
    }

    tc_logger_activity_log_write('Authentication', 'Login', get_name(_escape($user->id)), _escape($user->uname));
    _tc_flash()->success(sprintf(_t('Login was successful. Welcome <strong>%s</strong> to your dashboard.'), get_name(_escape($user->id))), get_base_url() . 'dashboard/');
}

/**
 * Checks a user's login information.
 *
 * @since 2.0.0
 * @param string $login User's username or email address.
 * @param string $password User's password.
 * @param string $rememberme Whether to remember the person.
 */
function tc_authenticate_user($login, $password, $rememberme)
{
    if (empty($login) || empty($password)) {

        if (empty($login)) {
            _tc_flash()->error(_t('<strong>ERROR</strong>: The username/email field is empty.'), get_base_url());
        }

        if (empty($password)) {
            _tc_flash()->error(_t('<strong>ERROR</strong>: The password field is empty.'), get_base_url());
        }

        return;
    }

    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $user = get_user_by('email', $login);

        if (false == $user->email) {
            _tc_flash()->error(_t('<strong>ERROR</strong>: Invalid email address.'), get_base_url());

            return;
        }
    } else {
        $user = get_user_by('uname', $login);

        if (false == $user->uname) {
            _tc_flash()->error(_t('<strong>ERROR</strong>: Invalid username.'), get_base_url());

            return;
        }
    }

    if (!tc_check_password($password, $user->password, _escape($user->id))) {
        _tc_flash()->error(_t('<strong>ERROR</strong>: The password you entered is incorrect.'), get_base_url());

        return;
    }

    /**
     * Filters log in details.
     * 
     * @since 2.0.0
     * @param string $login User's username or email address.
     * @param string $password User's password.
     * @param string $rememberme Whether to remember the person.
     */
    $user = app()->hook->{'apply_filter'}('tc_authenticate_user', $login, $password, $rememberme);

    return $user;
}

function tc_set_auth_cookie($user, $rememberme = '')
{

    if (!is_object($user)) {
        throw new UnauthorizedException(_t('"$user" should be a database object.'), 4011);
    }

    if (isset($rememberme)) {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         * 
         * @since 2.0.0
         */
        $expire = app()->hook->{'apply_filter'}('auth_cookie_expiration', (_escape(get_option('cookieexpire')) !== '') ? _escape(get_option('cookieexpire')) : app()->config('cookies.lifetime'));
    } else {
        /**
         * Ensure the browser will continue to send the cookie until it expires.
         *
         * @since 2.0.0
         */
        $expire = app()->hook->{'apply_filter'}('auth_cookie_expiration', (app()->config('cookies.lifetime') !== '') ? app()->config('cookies.lifetime') : 86400);
    }

    $auth_cookie = [
        'key' => 'TC_COOKIENAME',
        'id' => _escape($user->id),
        'uname' => _escape($user->uname),
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
    app()->hook->{'do_action'}('set_auth_cookie', $auth_cookie, $expire);

    app()->cookies->setSecureCookie($auth_cookie);
}

/**
 * Removes all cookies associated with authentication.
 * 
 * @since 2.0.0
 */
function tc_clear_auth_cookie()
{
    /**
     * Fires just before the authentication cookies are cleared.
     *
     * @since 2.0.0
     */
    app()->hook->{'do_action'}('clear_auth_cookie');

    $vars1 = [];
    parse_str(app()->cookies->get('TC_COOKIENAME'), $vars1);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file1 = app()->config('cookies.savepath') . 'cookies.' . $vars1['data'];
    try {
        if (tc_file_exists($file1)) {
            unlink($file1);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    $vars2 = [];
    parse_str(app()->cookies->get('SWITCH_USERBACK'), $vars2);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file2 = app()->config('cookies.savepath') . 'cookies.' . $vars2['data'];
    if (tc_file_exists($file2, false)) {
        @unlink($file2);
    }

    /**
     * After the cookie is removed from the server,
     * we know need to remove it from the browser and
     * redirect the user to the login page.
     */
    app()->cookies->remove('TC_COOKIENAME');
    app()->cookies->remove('SWITCH_USERBACK');
}

/**
 * Shows error messages on login form.
 * 
 * @since 2.0.0
 */
function tc_login_form_show_message()
{
    echo app()->hook->{'apply_filter'}('login_form_show_message', _tc_flash()->showMessage());
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
    $data = app()->cookies->getSecureCookie($key);
    return $data;
}
