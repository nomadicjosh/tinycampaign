<?php namespace app\src;

use app\src\NodeQ\tc_NodeQ as Node;
use app\src\tc_QueueMessage as Message;
use Cascade\Cascade;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

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
     * 
     * @param \Liten\Liten $liten
     */
    public function __construct(\Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();
    }

    public function getNode()
    {
        $q = $this->app->db->message()
            ->select('message.node')
            ->where('message.sent = "0"')->_and_()
            ->whereNull('message.sent_date')
            ->findOne();

        return $q->node;
    }

    /**
     * Return email count of emails not sent yet.
     * 
     * @since 2.0.0
     * @return int unsent email count
     */
    public function getUnsentEmailCount()
    {
        $count = Node::table($this->getNode())->where('is_sent', '=', false)->findAll()->count();
        return $count;
    }
    
    /**
     * Return email count.
     * 
     * @since 2.0.0
     * @return int email count
     */
    public function getEmailCount()
    {
        $count = Node::table($this->getNode())->findAll()->count();
        return $count;
    }

    /**
     * Returns emails from queue.
     * 
     * @return tc_QueueMessage[]
     */
    public function getEmails()
    {
        $node = Node::table($this->getNode())->where('is_sent', '=', false)->findAll();

        $result_array = [];

        foreach ($node as $row) {
            $message = new Message();
            $message->setId($row->id);
            $message->setMessageId($row->mid);
            $message->setToEmail($row->to_email);
            $message->setToName($row->to_name);
            $message->setMessageHtml($row->message_html);
            $message->setMessagePlainText($row->message_plain_text);
            $message->setTimestampCreated($row->timestamp_created);
            $message->setTimestampToSend($row->timestamp_to_send);
            $message->setTimestampSent($row->timestamp_sent);
            $message->setIsSent(($row->is_sent ? true : false));
            $message->setHeaders(maybe_unserialize($row->headers));

            $result_array[] = $message;
        }

        return $result_array;
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
        if (!is_a($message, 'tc_QueueMessage')) {
            return false;
        }
        
        set_queued_message_is_sent($this->getNode(), $message->getId());

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
        if (!is_a($message, 'tc_QueueMessage')) {
            return false;
        }

        try {
            $node = Node::table($this->getNode());
            $node->mid = (int) $message->getMessageId();
            $node->to_email = (string) $message->getToEmail();
            $node->to_name = (string) $message->getToName();
            //$node->message_html = $message->getMessageHtml();
            //$node->message_plain_text = $message->getMessagePlainText();
            $node->message_html = (bool) true;
            $node->message_plain_text = (bool) false;
            $node->timestamp_created = (string) $message->getTimestampCreated();
            $node->timestamp_sent = (string) $message->getTimestampSent();
            $node->timestamp_to_send = (string) $message->getTimeStampToSend();
            $node->is_sent = (bool) $message->getIsSent();
            $node->serialized_headers = (string) $message->getSerializedHeaders();
            $node->save();
        } catch (app\src\Exception\Exception $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (\Exception $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }

        return true;
    }
}
