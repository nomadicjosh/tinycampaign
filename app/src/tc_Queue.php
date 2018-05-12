<?php

namespace TinyC;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\Exception;
use PDOException as ORMException;
use TinyC\tc_QueueMessage as Message;
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
     * 
     * @param \Liten\Liten $liten
     */
    public function __construct(\Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();
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
            $count = $this->app->db->campaign_queue()->where('is_sent', 'false')->count();
            return $count;
        } catch (ORMException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
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
            $count = $this->app->db->campaign_queue()->count();
            return $count;
        } catch (ORMException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
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
            $node = $this->app->db->campaign_queue()->where('is_sent', 'false')->_and_()->whereLte('timestamp_to_send', $now)->find();
            $result_array = [];

            foreach ($node as $row) {
                if (!validate_email(_escape($row->to_email))) {
                    Cascade::getLogger('notice')->notice(sprintf(_t('Invalid subscriber email: %s'), _escape($row->to_email)));
                    is_cancelled($row);
                }

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
        } catch (ORMException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
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
        if (!is_a($message, 'TinyC\\tc_QueueMessage')) {
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
        if (!is_a($message, 'TinyC\\tc_QueueMessage')) {
            return false;
        }

        try {
            $node = $this->app->db->campaign_queue();
            $node->insert([
                'lid' => (int) $message->getListId(),
                'cid' => (int) $message->getMessageId(),
                'sid' => (int) $message->getSubscriberId(),
                'to_email' => (string) $message->getToEmail(),
                'to_name' => (string) $message->getToName(),
                'timestamp_created' => (string) $message->getTimestampCreated(),
                'timestamp_to_send' => (string) $message->getTimeStampToSend(),
                'timestamp_sent' => (string) $message->getTimestampSent(),
                'is_unsubscribed' => (int) 0,
                'is_sent' => (string) 'false'
            ]);
        } catch (ORMException $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }

        return true;
    }

}
