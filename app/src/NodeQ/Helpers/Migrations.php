<?php namespace app\src\NodeQ\Helpers;

use \app\src\NodeQ\tc_NodeQ as Node;
use Cascade\Cascade;

/**
 * Data managing class
 * 
 * @since 6.2.11
 */
class Migrations
{

    public static function dispense($table)
    {
        if (!Validate::table($table)->exists()) {
            return self::$table();
        }
        return true;
    }

    public static function create_node()
    {
        try {
            Node::create('create_node', [
                'mid' => 'integer',
                'to_email' => 'string',
                'to_name' => 'string',
                'message_html' => 'boolean',
                'message_plain_text' => 'boolean',
                'timestamp_created' => 'string',
                'timestamp_to_send' => 'string',
                'timestamp_sent' => 'string',
                'is_sent' => 'boolean',
                'headers' => 'string'
            ]);
        } catch (\Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function login_details()
    {
        try {
            Node::create('login_details', [
                'userid' => 'integer',
                'uname' => 'string',
                'email' => 'string',
                'fname' => 'string',
                'lname' => 'string',
                'password' => 'string',
                'sent' => 'integer'
            ]);
        } catch (\Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function cronjob_setting()
    {
        try {
            Node::create('cronjob_setting', [
                'cronjobpassword' => 'string',
                'timeout' => 'integer'
            ]);

            $q = Node::table('cronjob_setting');
            $q->cronjobpassword = (string) 'changeme';
            $q->timeout = (int) 30;
            $q->save();
        } catch (\Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function cronjob_handler()
    {
        try {
            $url = get_base_url();
            Node::create('cronjob_handler', [
                'name' => 'string',
                'url' => 'string',
                'time' => 'string',
                'each' => 'integer',
                'eachtime' => 'string',
                'lastrun' => 'string',
                'running' => 'boolean',
                'runned' => 'integer'
            ]);

            $q = Node::table('cronjob_handler');
            $q->name = (string) 'Purge Activity Log';
            $q->url = (string) $url . 'cron/purgeActivityLog/';
            $q->time = (string) '';
            $q->each = (int) 3600;
            $q->eachtime = (string) '';
            $q->lastrun = (string) '';
            $q->running = (boolean) false;
            $q->runned = (int) 0;
            $q->save();

            $q->name = 'Run Email Queue';
            $q->url = (string) $url . 'cron/runEmailQueue/';
            $q->time = (string) '';
            $q->each = (int) 300;
            $q->eachtime = (string) '';
            $q->lastrun = (string) '';
            $q->running = (boolean) false;
            $q->runned = (int) 0;
            $q->save();

            $q->name = 'Purge Error Log';
            $q->url = (string) $url . 'cron/purgeErrorLog/';
            $q->time = (string) '';
            $q->each = (int) 1800;
            $q->eachtime = (string) '';
            $q->lastrun = (string) '';
            $q->running = (boolean) false;
            $q->runned = (int) 0;
            $q->save();
        } catch (\Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }
    
    public static function php_encryption()
    {
        $key = \Defuse\Crypto\Key::createNewRandomKey();
        try {
            Node::create('php_encryption', [
                'key' => 'string',
                'created_at' => 'string'
            ]);

            $q = Node::table('php_encryption');
            $q->key = (string) $key->saveToAsciiSafeString();
            $q->created_at = (string) \Jenssegers\Date\Date::now();
            $q->save();
        } catch (\Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }
}
