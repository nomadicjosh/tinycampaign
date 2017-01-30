<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

/**
 * User API: tc_User Class
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
final class tc_User
{

    /**
     * User ID.
     *
     * @var int
     */
    public $id;

    /**
     * The user's username.
     *
     * @var string
     */
    public $uname;

    /**
     * The user's first name.
     *
     * @var string
     */
    public $fname;

    /**
     * The user's last name.
     *
     * @var string
     */
    public $lname;

    /**
     * The user's email address.
     *
     * @var string
     */
    public $email;

    /**
     * The user's address1.
     *
     * @var string
     */
    public $address1;

    /**
     * The user's address2.
     *
     * @var string
     */
    public $address2;

    /**
     * The user's city of residence.
     *
     * @var int
     */
    public $city;

    /**
     * The user's state of residence.
     *
     * @var string
     */
    public $state;

    /**
     * The user's postal code.
     *
     * @var bool
     */
    public $postal_code;

    /**
     * The user's country.
     *
     * @var string
     */
    public $country;

    /**
     * The user's status.
     *
     * @var int
     */
    public $status;

    /**
     * Role ID of the user.
     *
     * @var int
     */
    public $roleID;

    /**
     * The timestamp of when record was created.
     *
     * @var string
     */
    public $date_added;

    /**
     * The user's last login timestamp.
     *
     * @var string
     */
    public $LastLogin;

    /**
     * The modified timestamp of user's record.
     *
     * @var string
     */
    public $LastUpdate;

    /**
     * Retrieve tc_User instance.
     *
     * @global app $app tinyCampaign application object.
     *        
     * @param int $user_id
     *            User ID.
     * @return tc_User|false User array, false otherwise.
     */
    public static function get_instance($user_id)
    {
        global $app;

        if (!$user_id) {
            return false;
        }

        try {
            $q = $app->db->user()
                ->where('id = ?', $user_id);

            $user = tc_cache_get($user_id, 'user');
            if (empty($user)) {
                $user = $q->find(function ($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add($user_id, $user, 'user');
            }

            $a = [];

            foreach ($user as $_user) {
                $a[] = $_user;
            }

            if (!$_user) {
                return false;
            }

            return $_user;
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    /**
     * Constructor.
     *
     * @param tc_User|object $user
     *            User object.
     */
    public function __construct($user)
    {
        foreach (get_object_vars($user) as $key => $value) {
            $this->$key = $value;
        }
    }
}
