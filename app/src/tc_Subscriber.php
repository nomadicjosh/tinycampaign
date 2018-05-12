<?php namespace TinyC;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\NotFoundException;
use TinyC\Exception\Exception;
use PDOException as ORMException;

/**
 * Subscriber API: tc_Subscriber Class
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
final class tc_Subscriber
{

    /**
     * Subscriber ID.
     *
     * @var int
     */
    public $id;

    /**
     * The subscriber's first name.
     *
     * @var string
     */
    public $fname;

    /**
     * The subscriber's last name.
     *
     * @var string
     */
    public $lname;

    /**
     * The subscriber's email address.
     *
     * @var string
     */
    public $email;

    /**
     * The subscriber's address1.
     *
     * @var string
     */
    public $address1;

    /**
     * The subscriber's address2.
     *
     * @var string
     */
    public $address2;

    /**
     * The subscriber's city of residence.
     *
     * @var int
     */
    public $city;

    /**
     * The subscriber's state of residence.
     *
     * @var string
     */
    public $state;

    /**
     * The subscriber's zip code.
     *
     * @var bool
     */
    public $zip;

    /**
     * The subscriber's country.
     *
     * @var string
     */
    public $country;

    /**
     * The subscriber's status.
     *
     * @var int
     */
    public $status;

    /**
     * Use who added subscriber.
     *
     * @var int
     */
    public $addedBy;

    /**
     * The timestamp of when record was created.
     *
     * @var string
     */
    public $dateAdded;

    /**
     * Retrieve tc_Subscriber instance.
     *
     * @global app $app tinyCampaign application object.
     *        
     * @param int $subscriber_id
     *            Subscriber ID.
     * @return tc_Subscriber|false Subscriber array, false otherwise.
     */
    public static function get_instance($subscriber_id)
    {
        global $app;

        if (!$subscriber_id) {
            return false;
        }

        try {
            $q = $app->db->subscriber()
                ->where('id = ?', $subscriber_id);

            $subscriber = tc_cache_get($subscriber_id, 'subscriber');
            if (empty($subscriber)) {
                $subscriber = $q->find(function ($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add($subscriber_id, $subscriber, 'subscriber');
            }

            $a = [];

            foreach ($subscriber as $_subscriber) {
                $a[] = $_subscriber;
            }

            if (!$_subscriber) {
                return false;
            }

            return $_subscriber;
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
     * @param tc_Subscriber|object $subscriber
     *            Subscriber object.
     */
    public function __construct($subscriber)
    {
        foreach (get_object_vars($subscriber) as $key => $value) {
            $this->$key = $value;
        }
    }
}
