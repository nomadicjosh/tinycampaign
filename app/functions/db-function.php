<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\Exception;
use PDOException as ORMException;
use Cascade\Cascade;

/**
 * tinyCampaign Database Related Functions
 *
 * For the most part, these are general purpose functions
 * that use the database to retrieve information.
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Table dropdown: pulls dropdown list from specified table
 * if $tableID is not NULL, shows the record attached
 * to a particular record.
 *
 * @since 2.0.0
 * @param string $table
 *            Name of database table that is being queried.
 * @param string $where
 *            Partial where clause (id = '1').
 * @param string $code
 *            Unique code from table.
 * @param string $name
 *            Name or title of record retrieving.
 * @param string $activeID
 *            Field to compare to.
 * @param string $bind
 *            Bind parameters to avoid SQL injection.
 * @return mixed
 */
function table_dropdown($table, $where = null, $id, $code, $name, $activeID = null, $bind = null)
{
    try {
        if ($where !== null && $bind == null) {
            $table = app()->db->query("SELECT $id, $code, $name FROM $table WHERE $where");
        } elseif ($bind !== null) {
            $table = app()->db->query("SELECT $id, $code, $name FROM $table WHERE $where", $bind);
        } else {
            $table = app()->db->query("SELECT $id, $code, $name FROM $table");
        }
        $q = $table->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });

        foreach ($q as $r) {
            echo '<option value="' . _escape($r[$code]) . '"' . selected($activeID, _escape($r[$code]), false) . '>' . _escape($r[$code]) . ' ' . _escape($r[$name]) . '</option>' . "\n";
        }
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Date dropdown
 * 
 * @since 2.0.0
 */
function date_dropdown($limit = 0, $name = '', $table = '', $column = '', $id = '', $field = '', $bool = '')
{
    try {
        if ($id != '') {
            $date_select = app()->db->query("SELECT * FROM $table WHERE $column = ?", [
                $id
            ]);
            $q = $date_select->find(function ($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            foreach ($q as $r) {
                $date = explode('-', $r[$field]);
            }
        }

        /* years */
        $html_output = '           <select name="' . $name . 'Year"' . $bool . ' class="selectpicker form-control" data-style="btn-info" data-size="10" data-live-search="true">' . "\n";
        $html_output .= '               <option value="">&nbsp;</option>' . "\n";
        for ($year = 2000; $year <= (date("Y") - $limit); $year ++) {
            $html_output .= '               <option value="' . sprintf("%04s", $year) . '"' . selected(sprintf("%04s", $year), $date[0], false) . '>' . sprintf("%04s", $year) . '</option>' . "\n";
        }
        $html_output .= '           </select>' . "\n";

        /* months */
        $html_output .= '           <select name="' . $name . 'Month"' . $bool . ' class="selectpicker form-control" data-style="btn-info" data-size="10" data-live-search="true">' . "\n";
        $html_output .= '               <option value="">&nbsp;</option>' . "\n";
        $months = array(
            "",
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        );
        for ($month = 1; $month <= 12; $month ++) {
            $html_output .= '               <option value="' . sprintf("%02s", $month) . '"' . selected(sprintf("%02s", $month), $date[1], false) . '>' . $months[$month] . '</option>' . "\n";
        }
        $html_output .= '           </select>' . "\n";

        /* days */
        $html_output .= '           <select name="' . $name . 'Day"' . $bool . ' class="selectpicker form-control" data-style="btn-info" data-size="10" data-live-search="true">' . "\n";
        $html_output .= '               <option value="">&nbsp;</option>' . "\n";
        for ($day = 1; $day <= 31; $day ++) {
            $html_output .= '               <option value="' . sprintf("%02s", $day) . '"' . selected(sprintf("%02s", $day), $date[2], false) . '>' . sprintf("%02s", $day) . '</option>' . "\n";
        }
        $html_output .= '           </select>' . "\n";

        return $html_output;
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Custom function to query any tinyCampaign
 * database table.
 *
 * @since 2.0.0
 * @param string $table            
 * @param mixed $field            
 * @param mixed $where            
 * @return mixed
 */
function qt($table, $field, $where = null)
{
    try {
        if ($where !== null) {
            $query = app()->db->query("SELECT * FROM $table WHERE $where");
        } else {
            $query = app()->db->query("SELECT * FROM $table");
        }
        $query->find();

        foreach ($query as $r) {
            return _escape($r->{$field});
        }
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve subscriber list id.
 * 
 * @since 2.0.0
 * @param int $id Subscriber's id.
 * @return int
 */
function get_subscriber_list_id($id)
{
    try {
        $q = app()->db->subscriber_list()
                ->where('sid = ?', $id)->_and_()
                ->where('unsubscribed = "0"');

        $slist = tc_cache_get($id, 'slist');
        if (empty($slist)) {
            $slist = $q->find(function ($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            tc_cache_add($id, $slist, 'slist');
        }

        $a = [];
        foreach ($slist as $r) {
            $a[] = $r['lid'];
        }
        return $a;
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve email lists of logged in user
 * to be used for campaigns.
 * 
 * @since 2.0.0
 * @param int $active Campaign's id.
 */
function get_campaign_lists($active = null)
{
    try {
        $lists = app()->db->list()
                ->where('list.owner = ?', get_userdata('id'))
                ->find();

        foreach ($lists as $list) {
            if (in_array($list->id, get_campaign_list_id($active))) {
                echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" checked="checked"/> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
            } else {
                echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" /> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
            }
        }
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve campaign list id.
 * 
 * @since 2.0.0
 * @param int $id Campaigns id.
 * @return int
 */
function get_campaign_list_id($id)
{
    try {
        $q = app()->db->campaign_list()
                ->where('cid = ?', $id);

        $cpgn = tc_cache_get($id, 'clist');
        if (empty($cpgn)) {
            $cpgn = $q->find(function ($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            tc_cache_add($id, $cpgn, 'clist');
        }

        $a = [];
        foreach ($cpgn as $r) {
            $a[] = $r['lid'];
        }
        return $a;
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieves server info based on unique id.
 * 
 * @since 2.0.1
 * @param int $id Server id.
 * @return object Server info.
 */
function get_server_info($id)
{
    try {
        $server = app()->db->server()
                ->where('id = ?', $id)
                ->findOne();

        return $server;
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Calculates response time between when the campaign was sent
 * and when the subscriber first opened his/her email.
 * 
 * @since 2.0.4
 * @param int $cid Campaign id.
 * @param int $sid Subscriber id.
 * @param string $left_operand When campaign was first opened by subscriber.
 * @return string
 */
function tc_response_time($cid, $sid, $left_operand)
{
    try {
        $resp = app()->db->campaign_queue()
                ->where('cid = ?', $cid)->_and_()
                ->where('sid = ?', $sid)
                ->findOne();

        $seconds = strtotime($left_operand) - strtotime(_escape($resp->timestamp_sent));
        return tc_seconds_to_time($seconds);
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve email lists of logged in user
 * to be used for campaigns.
 * 
 * @since 2.0.5
 * @param array $active RSS Campaign's unique id.
 */
function get_rss_campaign_lists($active = null)
{
    try {
        $lists = app()->db->list()
                ->where('list.owner = ?', get_userdata('id'))
                ->find();

        foreach ($lists as $list) {
            if (get_rss_campaign_list_id($active) == false) {
                echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" /> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
            } else {
                if (in_array($list->id, get_rss_campaign_list_id($active))) {
                    echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" checked="checked"/> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
                } else {
                    echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" /> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
                }
            }
        }
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve RSS campaign list id's.
 * 
 * @since 2.0.5
 * @param int $id RSS Campaigns id.
 * @return int|array
 */
function get_rss_campaign_list_id($id)
{
    try {
        $rss = app()->db->rss_campaign()->where('id = ?', $id)->findOne();

        return maybe_unserialize($rss->lid);
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve and list templates.
 * 
 * @since 2.0.5
 * @param int $active Active id.
 */
function get_template_list($active = null)
{
    try {
        $templates = app()->db->template()
                ->where('template.owner = ?', get_userdata('id'))
                ->find();

        foreach ($templates as $template) {
            echo '<option value="' . _escape($template->id) . '"' . selected($active, _escape($template->id), false) . '>' . _escape($template->name) . '</option>';
        }
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve a template by it's id.
 * 
 * @since 2.0.5
 * @access private
 * @param int $id Template's id.
 */
function get_template_by_id($id)
{
    try {
        $templates = app()->db->template()
                ->where('template.id = ?', $id)
                ->findOne();

        return $templates;
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Cancels queue record when email cannot be sent.
 * 
 * @since 2.0.5
 * @access private
 * @param array $data Array of queued data.
 */
function is_cancelled($data)
{
    $cancel = app()->db->campaign_queue();
    $cancel->set([
                'is_cancelled' => 'true'
            ])
            ->where('lid = ?', $data->lid)->_and_()
            ->where('cid = ?', $data->cid)->_and_()
            ->where('sid = ?', $data->sid)
            ->update();
}

/**
 * Retrieves error count of queued record.
 * 
 * @since 2.0.5
 * @access private
 * @param array $data Array of queued data.
 * @return array
 */
function get_error_count($data)
{
    $error_count = app()->db->campaign_queue()
            ->where('lid = ?', $data->xlistid)->_and_()
            ->where('cid = ?', $data->xcampaignid)->_and_()
            ->where('sid = ?', $data->xsubscriberid)
            ->findOne();

    return $error_count;
}

/**
 * Updates queued record with error count.
 * 
 * @access private
 * @since 2.0.5
 * @param array $data Array of queued data.
 */
function update_error_count($data)
{
    $error_count = get_error_count($data);

    if (_escape($error_count->error_count) == app()->hook->{'apply_filter'}('update_error_count', 2)) {
        $error_count->set([
                    'error_count' => _escape($error_count->error_count) + 1
                ])
                ->update();
        Cascade::getLogger('error')->{'error'}(sprintf(_t('Error while sending email to: %s. Cancelled: No more sending attempts allowed.'), _escape($error_count->to_email)));
        is_cancelled($data);
    } else {
        $error_count->set([
                    'error_count' => _escape($error_count->error_count) + 1
                ])
                ->update();
        Cascade::getLogger('error')->{'error'}(sprintf(_t('Error while sending email to: %s. Scheduled to try again.'), _escape($error_count->to_email)));
    }
}

/**
 * Updates sending count of queued record.
 * 
 * @since 2.0.5
 * @access private
 * @param array $data Array of queued data.
 */
function update_send_count($data)
{
    $send_count = app()->db->campaign_queue()
            ->where('lid = ?', $data->xlistid)->_and_()
            ->where('cid = ?', $data->xcampaignid)->_and_()
            ->where('sid = ?', $data->xsubscriberid)
            ->findOne();
    $send_count->set([
                'send_count' => $send_count->send_count + 1
            ])
            ->update();
}

/**
 * Checks if subscriber is already subscribed to an email list.
 * 
 * @since 2.0.6
 * @access private
 * @param int $lid Email list id to check against.
 * @param int $sid Subscriber list id to check against.
 * @return bool
 */
function is_subscribed_to_list($lid, $sid)
{
    $sub_list = app()->db->subscriber_list()
            ->where('lid = ?', $lid)->_and_()
            ->where('sid = ?', $sid)
            ->count();
    return $sub_list > 0 ? true : false;
}

/**
 * Retrieve email lists of logged in user
 * to be used for campaigns, rss campaigns, and subscribers.
 * 
 * @since 2.0.6
 * @param int $active Campaign's id.
 */
function get_subscription_email_lists($active = null)
{
    try {
        $lists = app()->db->list()
                ->where('list.owner = ?', get_userdata('id'))
                ->find();

        foreach ($lists as $list) {
            if (in_array($list->id, get_subscription_email_list_id($active))) {
                echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" checked="checked"/> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
            } else {
                echo '<li><input type="hidden" name="id[]" value="' . $list->id . '" /><input type="checkbox" name="lid[' . $list->id . ']" class="minimal" value="' . $list->id . '" /> ' . $list->name . ' (' . get_list_subscriber_count($list->id) . ')</li>';
            }
        }
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve subscriber lists list id's.
 * 
 * @since 2.0.6
 * @param int $id Subscriber's id.
 * @return int
 */
function get_subscription_email_list_id($id)
{
    try {
        $q = app()->db->subscriber_list()
                ->where('sid = ?', $id)->_and_()
                ->where('unsubscribed = "0"');

        $slist = tc_cache_get($id, 'slist');
        if (empty($slist)) {
            $slist = $q->find(function ($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            tc_cache_add($id, $slist, 'slist');
        }

        $a = [];
        foreach ($slist as $r) {
            $a[] = $r['lid'];
        }
        return $a;
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}
