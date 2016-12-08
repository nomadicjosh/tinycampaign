<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
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
$app = \Liten\Liten::getInstance();

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
    $app = \Liten\Liten::getInstance();
    if ($where !== null && $bind == null) {
        $table = $app->db->query("SELECT $id, $code, $name FROM $table WHERE $where");
    } elseif ($bind !== null) {
        $table = $app->db->query("SELECT $id, $code, $name FROM $table WHERE $where", $bind);
    } else {
        $table = $app->db->query("SELECT $id, $code, $name FROM $table");
    }
    $q = $table->find(function ($data) {
        $array = [];
        foreach ($data as $d) {
            $array[] = $d;
        }
        return $array;
    });

    foreach ($q as $r) {
        echo '<option value="' . _h($r[$code]) . '"' . selected($activeID, _h($r[$code]), false) . '>' . _h($r[$code]) . ' ' . _h($r[$name]) . '</option>' . "\n";
    }
}

/**
 * Date dropdown
 * 
 * @since 2.0.0
 */
function date_dropdown($limit = 0, $name = '', $table = '', $column = '', $id = '', $field = '', $bool = '')
{
    $app = \Liten\Liten::getInstance();
    if ($id != '') {
        $date_select = $app->db->query("SELECT * FROM $table WHERE $column = ?", [
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
    $app = \Liten\Liten::getInstance();
    if ($where !== null) {
        $query = $app->db->query("SELECT * FROM $table WHERE $where");
    } else {
        $query = $app->db->query("SELECT * FROM $table");
    }
    $query->find();

    foreach ($query as $r) {
        return _h($r->{$field});
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
    $app = \Liten\Liten::getInstance();
    $roles = $app->db->role()
        ->find();

    foreach ($roles as $role) {
        echo '<option value="' . $role->id . '"' . selected($active, _h($role->id), false) . '>' . _h($role->roleName) . '</option>';
    }
}
