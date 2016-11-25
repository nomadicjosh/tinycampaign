<?php namespace app\src;

if (! defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * User API: tc_UserClass
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
    public $userID;

    /**
     * The user's username.
     *
     * @var string
     */
    public $uname;

    /**
     * The user's prefix.
     *
     * @var string
     */
    public $prefix;

    /**
     * The user's user type.
     *
     * @var string
     */
    public $userType;

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
     * The user's middle initial.
     *
     * @var string
     */
    public $mname;

    /**
     * The user's email address.
     *
     * @var string
     */
    public $email;

    /**
     * The user's social security number.
     *
     * @var int
     */
    public $ssn;

    /**
     * The user's date of birth.
     *
     * @var string
     */
    public $dob;

    /**
     * The user's veteran status.
     *
     * @var bool
     */
    public $veteran;

    /**
     * The user's ethnicity.
     *
     * @var string
     */
    public $ethnicity;

    /**
     * The user's gender.
     *
     * @var string
     */
    public $gender;

    /**
     * The user's emergency contact user.
     *
     * @var string
     */
    public $emergency_contact;

    /**
     * The user's emergency contact user phone number.
     *
     * @var string
     */
    public $emergency_contact_phone;

    /**
     * The user's uploaded photo.
     *
     * @var string
     */
    public $photo;

    /**
     * The user's status.
     *
     * @var string
     */
    public $status;

    /**
     * The user's approved date.
     *
     * @var string
     */
    public $approvedDate = '0000-00-00';

    /**
     * The user's approval user.
     *
     * @var int
     */
    public $approvedBy = 1;

    /**
     * The user's last log in date and time.
     */
    public $LastLogin = '0000-00-00 00:00:00';

    /**
     * The user's modified date and time.
     */
    public $LastUpdate = '0000-00-00 00:00:00';

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
        
        //$user_id = (int) $user_id;
        
        if (! $user_id) {
            return false;
        }
        
        $q = $app->db->user()->where('userID = ?', $user_id);
        
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
        
        if (! $_user) {
            return false;
        }
        
        return $_user;
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
