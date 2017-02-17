<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\tc_QueueMessage as Message;
use Cascade\Cascade;

/**
 * tinyCampaign Queue
 *  
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class tc_Queue
{

    /**
     * Application object.
     * 
     * @var object
     */
    public $app;

    /**
     * Node where messages are saved.
     * 
     * @var type 
     */
    public $node = 'campaign_queue';

    /**
     * 
     * @param \Liten\Liten $liten
     */
    public function __construct(\Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();
    }

    public function getNode()
    {
        return $this->node;
    }

    /**
     * Return email count of emails not sent yet.
     * 
     * @since 2.0.0
     * @return int unsent email count
     */
    public function getUnsentEmailCount()
    {
        try {
            $count = Node::table($this->getNode())->where('is_sent', '=', 'false')->findAll()->count();
            return $count;
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (InvalidArgumentException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Return email count.
     * 
     * @since 2.0.0
     * @return int email count
     */
    public function getEmailCount()
    {
        try {
            $count = Node::table($this->getNode())->findAll()->count();
            return $count;
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (InvalidArgumentException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Returns emails from queue.
     * 
     * @return tc_QueueMessage[]
     */
    public function getEmails()
    {
        $now = \Jenssegers\Date\Date::now()->format('Y-m-d H:i:s');
        try {
            $node = Node::table($this->getNode())->where('is_sent', '=', 'false')->andWhere('timestamp_to_send', '<=', $now)->findAll();
            $result_array = [];

            foreach ($node as $row) {
                $message = new Message();
                $message->setId($row->id);
                $message->setListId($row->lid);
                $message->setMessageId($row->cid);
                $message->setSubscriberId($row->sid);
                $message->setToEmail($row->to_email);
                $message->setToName($row->to_name);
                $message->setTimestampCreated($row->timestamp_created);
                $message->setTimestampToSend($row->timestamp_to_send);
                $message->setTimestampSent($row->timestamp_sent);
                $message->setIsSent(($row->is_sent ? true : false));

                $result_array[] = $message;
            }

            return $result_array;
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (InvalidArgumentException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * sets is_sent value of the message record to true
     *
     * @param PHPEQMessage $message message to update
     * 
     * @return bool
     */
    public function setMessageIsSent($message)
    {
        if (!is_a($message, 'app\\src\\tc_QueueMessage')) {
            return false;
        }

        set_queued_message_is_sent($message);

        return true;
    }

    /**
     * saves the message record to queue
     *
     * @param PHPEQMessage $message message to save
     * 
     * @return bool
     */
    public function addMessage($message)
    {
        if (!is_a($message, 'app\\src\\tc_QueueMessage')) {
            return false;
        }

        try {
            $node = Node::table($this->getNode());
            $node->lid = (int) $message->getListId();
            $node->cid = (int) $message->getMessageId();
            $node->sid = (int) $message->getSubscriberId();
            $node->to_email = (string) $message->getToEmail();
            $node->to_name = (string) $message->getToName();
            $node->timestamp_created = (string) $message->getTimestampCreated();
            $node->timestamp_to_send = (string) $message->getTimeStampToSend();
            $node->timestamp_sent = (string) $message->getTimestampSent();
            $node->is_unsubscribed = (int) 0;
            $node->is_sent = (string) 'false';
            $node->save();
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (InvalidArgumentException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }

        return true;
    }
}
