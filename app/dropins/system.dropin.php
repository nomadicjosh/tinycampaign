<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\Exception\Exception;
use app\src\NodeQ\Helpers\Validate as Validate;
use PDOException as ORMException;

try {
    if (!Validate::table('php_encryption')->exists()) {
        Node::dispense('php_encryption');
    }
    if (!Validate::table('new_subscriber_notification')->exists()) {
        Node::dispense('new_subscriber_notification');
    }
    /**
     * Add fields to cronjob_handler, if it does not exist.
     * 
     * @since 2.0.3
     */
    $fields = Node::table('cronjob_handler')->fields();
    if (!in_array('status', $fields)) {
        $add_field = Node::table('cronjob_handler');
        $add_field->addFields(['status' => 'integer']);
    }
} catch (NodeQException $e) {
    Cascade\Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Unable to create Node: %s', $e->getCode(), $e->getMessage()));
} catch (Exception $e) {
    Cascade\Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Unable to create Node: %s', $e->getCode(), $e->getMessage()));
}

try {
    if (!Validate::table('campaign_queue')->exists()) {
        return false;
    }

    $node = Node::table('campaign_queue')->findAll();
    if ($node->count() <= 0) {
        return false;
    }

    $exist = app()->db->query("SHOW TABLES LIKE '%campaign_queue%'");
    if ($exist == 1) {
        $count = app()->db->campaign_queue()->count();
        if ($count <= 0) {
            $file = file_get_contents(app()->config('cookies.savepath') . 'nodes' . DS . 'tinyc' . DS . 'campaign_queue.data.node');
            $array = json_decode($file);

            foreach ($array as $key => $value) {
                $sql = '';
                foreach ($value as $key => $data) {
                    $sql .= "'" . $data . "',";
                }
                $sql = substr($sql, 0, strlen($sql) - 1);

                app()->db->query('INSERT INTO campaign_queue(tmp_id,lid,cid,sid,to_email,to_name,timestamp_created,timestamp_to_send,timestamp_sent,is_unsubscribed,timestamp_unsubscribed,is_sent) VALUES(' . $sql . ')');
            }

            app()->db->query('ALTER TABLE `campaign_queue` DROP COLUMN `tmp_id`');
            app()->db->query('UPDATE `campaign_queue` SET `timestamp_unsubscribed` = NULL WHERE `timestamp_unsubscribed` = "0000-00-00 00:00:00"');
        }
    }
} catch (NodeQException $e) {
    Cascade\Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Node error: %s', $e->getCode(), $e->getMessage()));
} catch (ORMException $e) {
    Cascade\Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: SQL error: %s', $e->getCode(), $e->getMessage()));
}
