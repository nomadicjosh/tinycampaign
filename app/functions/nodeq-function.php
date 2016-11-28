<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use Cascade\Cascade;

/**
 * tinyCampaign Message Queue
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();

function set_queued_message_is_sent($node, $id)
{
    $now = Jenssegers\Date\Date::now();
    try {
        $queue = Node::table("$node")->where('timestamp_to_send', '>=', $now)->find($id);
        $queue->timestamp_sent = (string) $now;
        $queue->is_sent = (bool) true;
        $queue->save();
    } catch (app\src\Exception\Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (\Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}
