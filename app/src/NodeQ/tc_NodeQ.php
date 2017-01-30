<?php namespace app\src\NodeQ;

use \app\src\NodeQ\Database as NodeQ;
use \app\src\NodeQ\Helpers;

/**
 * NodeQ
 * 
 * A simple NoSQL library.
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class tc_NodeQ extends NodeQ
{

    public static function dispense($table)
    {
        Helpers\Migrations::dispense($table);

        return self::table($table);
    }
}
