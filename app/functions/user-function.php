<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

/**
 * tinyCampaign User Functions
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
function role_perm($id = null)
{
    try {
        $role = app()->db->role()
                ->select('role.permission')
                ->where('role.id = ?', $id);
        $q1 = $role->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        $a = [];
        foreach ($q1 as $r1) {
            $a[] = $r1;
        }

        $permission = app()->db->permission();
        $q2 = $permission->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });

        foreach ($q2 as $r2) {
            $perm = maybe_unserialize($r1['permission']);
            echo '
				<tr>
					<td>' . $r2['permName'] . '</td>
					<td class="text-center">';
            if (is_array($perm) && in_array($r2['permKey'], $perm)) {
                echo '<input type="checkbox" name="permission[]" class="minimal" value="' . $r2['permKey'] . '" checked="checked" />';
            } else {
                echo '<input type="checkbox" name="permission[]" class="minimal" value="' . $r2['permKey'] . '" />';
            }
            echo '</td>
            </tr>';
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

function user_permission($id = null)
{
    try {
        $array = [];
        $pp = app()->db->query("SELECT permission FROM user_perms WHERE userID = ?", [
            $id
        ]);
        $q = $pp->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        foreach ($q as $r) {
            $array[] = $r;
        }
        $userPerm = maybe_unserialize($r['permission']);
        /**
         * Select the role(s) of the user who's
         * userID = $id
         */
        $array1 = [];
        $pr = app()->db->query("SELECT roleID from user WHERE id = ?", [
            $id
        ]);
        $q1 = $pr->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        foreach ($q1 as $r1) {
            $array1[] = $r1;
        }
        /**
         * Select all the permissions from the role(s)
         * that are connected to the selected user.
         */
        $array2 = [];
        $role = app()->db->query("SELECT permission from role WHERE id = ?", [
            _escape($r1['roleID'])
        ]);
        $q2 = $role->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        foreach ($q2 as $r2) {
            $array2[] = $r2;
        }
        $perm = maybe_unserialize($r2['permission']);
        $permission = app()->db->permission();
        $sql = $permission->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        foreach ($sql as $row) {
            echo '
            <tr>
                <td>' . $row['permName'] . '</td>
                <td class="text-center">';
            if (in_array($row['permKey'], $perm)) {
                echo '<input type="checkbox" name="permission[]" value="' . $row['permKey'] . '" class="minimal" checked="checked" disabled="disabled" />';
            } elseif ($userPerm != '' && in_array($row['permKey'], $userPerm)) {
                echo '<input type="checkbox" name="permission[]" value="' . $row['permKey'] . '" class="minimal" checked="checked" />';
            } else {
                echo '<input type="checkbox" name="permission[]" value="' . $row['permKey'] . '" class="minimal" />';
            }
            echo '</td>
            </tr>';
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Returns the name of a particular user.
 *
 * @since 2.0.0
 * @param int $id
 *            User id.
 * @return string
 */
function get_name($id)
{
    if ('' == _trim($id)) {
        $message = _t('Invalid user id: empty id given.');
        _incorrectly_called(__FUNCTION__, $message, '2.0.0');
        return;
    }

    if (!is_numeric($id)) {
        $message = _t('Invalid user id: user id must be numeric.');
        _incorrectly_called(__FUNCTION__, $message, '2.0.0');
        return;
    }

    $name = get_user_by('id', $id);

    return _escape($name->fname) . ' ' . _escape($name->lname);
}

/**
 * Shows selected user's initials instead of
 * his/her's full name.
 *
 * @since 2.0.0
 * @param int $id
 *            User id
 * @param int $initials
 *            Number of initials to show.
 * @return string
 */
function get_initials($id, $initials = 2)
{
    if ('' == _trim($id)) {
        $message = _t('Invalid user ID: empty ID given.');
        _incorrectly_called(__FUNCTION__, $message, '2.0.0');
        return;
    }

    if (!is_numeric($id)) {
        $message = _t('Invalid user ID: user id must be numeric.');
        _incorrectly_called(__FUNCTION__, $message, '2.0.0');
        return;
    }

    $name = get_user_by('id', $id);

    if ($initials == 2) {
        return mb_substr(_escape($name->fname), 0, 1, 'UTF-8') . '. ' . mb_substr(_escape($name->lname), 0, 1, 'UTF-8') . '.';
    } else {
        return _escape($name->lname) . ', ' . mb_substr(_escape($name->fname), 0, 1, 'UTF-8') . '.';
    }
}

/**
 * Retrieve requested field from user table
 * based on user's id.
 *
 * @since 2.0.0
 * @param int $id
 *            User ID.
 * @param mixed $field
 *            Data requested of particular user.
 * @return mixed
 */
function get_user_value($id, $field)
{
    $value = get_user_by('id', $id);

    return $value->{$field};
}

/**
 * Retrieves a list of roles from the roles table.
 *
 * @since 2.0.0
 * @return mixed
 */
function get_perm_roles()
{
    try {
        $role = app()->db->role()->find();
        foreach ($role as $r) {
            echo '<option value="' . _escape($r->id) . '">' . _escape($r->roleName) . '</option>' . "\n";
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve a list of available user roles.
 * 
 * @since 2.0.0
 * @param type $active
 */
function get_user_roles($active = null)
{
    try {
        $roles = app()->db->role()
                ->find();

        foreach ($roles as $role) {
            echo '<option value="' . $role->id . '"' . selected($active, _escape($role->id), false) . '>' . _escape($role->roleName) . '</option>';
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve email lists for subscribers.
 * 
 * @since 2.0.0
 * @param int $active Subscriber's id.
 */
function get_subscriber_lists($active = null)
{
    try {
        $in = "'" . implode("','", get_subscriber_list_id($active)) . "'";
        $lists = app()->db->list()
                ->where('list.owner = ?', get_userdata('id'))->_and_()
                ->where("(list.status = 'open' OR list.id IN($in))")
                ->find();

        foreach ($lists as $list) {
            if (in_array($list->id, get_subscriber_list_id($active))) {
                echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" checked="checked"/> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
            } else {
                echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" /> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
            }
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

function get_user_servers($active = null)
{
    try {
        $servers = app()->db->server()
                ->where('owner = ?', get_userdata('id'))
                ->find();
        foreach ($servers as $server) {
            echo '<option value="' . $server->id . '"' . selected($active, _escape($server->id), false) . '>' . _escape($server->name) . '</option>';
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieves user data given a user ID or user array.
 *
 * @since 2.0.0
 * @param int|tc_User|null $user
 *            User ID or user array.
 * @param bool $object
 *            If set to true, data will return as an object, else as an array.
 */
function get_user($user, $object = true)
{
    if ($user instanceof \app\src\tc_User) {
        $_user = $user;
    } elseif (is_array($user)) {
        if (empty($user['id'])) {
            $_user = new \app\src\tc_User($user);
        } else {
            $_user = \app\src\tc_User::get_instance($user['id']);
        }
    } else {
        $_user = \app\src\tc_User::get_instance($user);
    }

    if (!$_user) {
        return null;
    }

    if ($object == true) {
        $_user = array_to_object($_user);
    }

    return $_user;
}

/**
 * Checks whether the given username exists.
 *
 * @since 2.0.0
 * @param string $username
 *            Username to check.
 * @return int|false The user's ID on success, and false on failure.
 */
function username_exists($username)
{
    if ($user = get_user_by('uname', $username)) {
        return $user->id;
    }
    return false;
}

/**
 * Checks whether the given email exists.
 *
 * @since 2.0.0
 * @param string $email
 *            Email to check.
 * @return int|false The user's ID on success, and false on failure.
 */
function email_exists($email)
{
    if ($user = get_user_by('email', $email)) {
        return $user->id;
    }
    return false;
}

/**
 * Adds label to user's status.
 * 
 * @since 2.0.3
 * @param string $status
 * @return string
 */
function tc_user_status_label($status)
{
    $label = [
        1 => 'label-success',
        0 => 'label-danger'
    ];

    return $label[$status];
}
