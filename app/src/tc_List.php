<?php namespace app\src;

if (! defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * List API: tc_List Class
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
final class tc_List
{

    /**
     * List ID.
     *
     * @var int
     */
    public $id;

    /**
     * The list's unique code.
     *
     * @var string
     */
    public $code;

    /**
     * The list's name.
     *
     * @var string
     */
    public $name;

    /**
     * The list's description.
     *
     * @var string
     */
    public $description;

    /**
     * Date list created.
     *
     * @var string
     */
    public $created;

    /**
     * List owner.
     *
     * @var int
     */
    public $owner;

    /**
     * Custom redirect success url.
     *
     * @var string
     */
    public $redirect_success;

    /**
     * Custom redirect error url.
     *
     * @var string
     */
    public $redirect_error;

    /**
     * Double opt-in.
     *
     * @var int
     */
    public $optin;

    /**
     * List's status.
     *
     * @var string
     */
    public $status;

    /**
     * Modified datetime.
     *
     * @var bool
     */
    public $LastUpdate = '0000-00-00 00:00:00';

    /**
     * Retrieve tc_List instance.
     *
     * @global app $app tinyCampaign application object.
     *        
     * @param int $list_id
     *            List ID.
     * @return tc_List|false List array, false otherwise.
     */
    public static function get_instance($list_id)
    {
        global $app;
        
        if (! $list_id) {
            return false;
        }
        
        $q = $app->db->list()->where('id = ?', $list_id);
        
        $list = tc_cache_get($list_id, 'list');
        if (empty($list)) {
            $list = $q->find(function ($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            tc_cache_add($list_id, $list, 'list');
        }
        
        $a = [];
        
        foreach ($list as $_list) {
            $a[] = $_list;
        }
        
        if (! $_list) {
            return false;
        }
        
        return $_list;
    }

    /**
     * Constructor.
     *
     * @param tc_List|object $list
     *            List object.
     */
    public function __construct($list)
    {
        foreach (get_object_vars($list) as $key => $value) {
            $this->$key = $value;
        }
    }
}
