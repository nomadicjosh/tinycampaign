<?php namespace app\src;

use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Event Logger for Errors and Activity.
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class tc_Logger
{
    /**
     * Application object.
     * @var type 
     */
    public $app;
    
    public function __construct()
    {
        $this->app = \Liten\Liten::getInstance();
    }

    /**
     * Writes a log to the log table in the database.
     * 
     * @since 2.0.0
     */
    public function writeLog($action, $process, $record, $uname)
    {
        $create = \Jenssegers\Date\Date::now()->format("Y-m-d H:i:s");
        $current_date = strtotime($create);
        /* 20 days after creation date */
        $expire = date("Y-m-d H:i:s", $current_date+=1728000);
        
        $expires_at = $this->app->hook->{'apply_filter'}('activity_log_expires', $expire);

        $log = $this->app->db->activity();
        $log->action = $action;
        $log->process = $process;
        $log->record = $record;
        $log->uname = $uname;
        $log->created_at = $create;
        $log->expires_at = $expires_at;

        $log->save();
    }

    /**
     * Purges audit trail logs that are older than 30 days old.
     * 
     * @since 2.0.0
     */
    public function purgeActivityLog()
    {
        try {
            $this->app->db->query("DELETE FROM activity WHERE expires_at <= ?", [\Jenssegers\Date\Date::now()->format("Y-m-d H:i:s")]);
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    /**
     * Purges system error logs that are older than 30 days old.
     * 
     * @since 2.0.0
     */
    public function purgeErrorLog()
    {
        $logs = glob(APP_PATH . 'tmp/logs/*.txt');
        if (is_array($logs)) {
            foreach ($logs as $log) {
                $filelastmodified = file_mod_time($log);
                if ((time() - $filelastmodified) >= 30 * 24 * 3600 && is_file($log)) {
                    unlink($log);
                }
            }
        }
    }

    public function logError($type, $string, $file, $line)
    {
        $date = new \DateTime();

        $log = $this->app->db->error();
        $log->time = $date->getTimestamp();
        $log->type = (int) $type;
        $log->string = (string) $string;
        $log->file = (string) $file;
        $log->line = (int) $line;

        $log->save();
    }

    public function error_constant_to_name($value)
    {
        $values = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            E_ALL => 'E_ALL'
        );

        return $values[$value];
    }
}
