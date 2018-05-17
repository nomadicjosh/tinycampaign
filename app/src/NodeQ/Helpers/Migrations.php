<?php namespace TinyC\NodeQ\Helpers;

use \TinyC\NodeQ\tc_NodeQ as Node;
use TinyC\NodeQ\NodeQException;
use TinyC\Exception\Exception;
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
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
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
                'runned' => 'integer',
                'status' => 'integer'
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
            $q->status = (int) 1;
            $q->save();

            $q->name = 'Run Email Queue';
            $q->url = (string) $url . 'cron/runEmailQueue/';
            $q->time = (string) '';
            $q->each = (int) 300;
            $q->eachtime = (string) '';
            $q->lastrun = (string) '';
            $q->running = (boolean) false;
            $q->runned = (int) 0;
            $q->status = (int) 1;
            $q->save();

            $q->name = 'Purge Error Log';
            $q->url = (string) $url . 'cron/purgeErrorLog/';
            $q->time = (string) '';
            $q->each = (int) 1800;
            $q->eachtime = (string) '';
            $q->lastrun = (string) '';
            $q->running = (boolean) false;
            $q->runned = (int) 0;
            $q->status = (int) 1;
            $q->save();

            $q->name = 'Run Bounce Handler';
            $q->url = (string) $url . 'cron/runBounceHandler/';
            $q->time = (string) '';
            $q->each = (int) 86400;
            $q->eachtime = (string) '';
            $q->lastrun = (string) '';
            $q->running = (boolean) false;
            $q->runned = (int) 0;
            $q->status = (int) 1;
            $q->save();

            $q->name = 'Run NodeQ';
            $q->url = (string) $url . 'cron/runNodeQ/';
            $q->time = (string) '';
            $q->each = (int) 300;
            $q->eachtime = (string) '';
            $q->lastrun = (string) '';
            $q->running = (boolean) false;
            $q->runned = (int) 0;
            $q->status = (int) 1;
            $q->save();
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
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
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function queued_campaign()
    {
        try {
            Node::create('queued_campaign', [
                'node' => 'string',
                'mid' => 'integer',
                'sendstart' => 'string',
                'complete' => 'integer'
            ]);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function confirm_email()
    {
        try {
            Node::create('confirm_email', [
                'lcode' => 'string',
                'sid' => 'integer',
                'scode' => 'string',
                'sent' => 'integer'
            ]);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function subscribe_email()
    {
        try {
            Node::create('subscribe_email', [
                'lcode' => 'string',
                'sid' => 'integer',
                'scode' => 'string',
                'sent' => 'integer'
            ]);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function unsubscribe_email()
    {
        try {
            Node::create('unsubscribe_email', [
                'lcode' => 'string',
                'sid' => 'integer',
                'scode' => 'string',
                'sent' => 'integer'
            ]);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function new_subscriber_notification()
    {
        try {
            Node::create('new_subscriber_notification', [
                'lid' => 'integer',
                'sid' => 'integer',
                'sent' => 'integer'
            ]);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }

    public static function campaign_bounce()
    {
        try {
            Node::create('campaign_bounce', [
                'lid' => 'integer',
                'cid' => 'integer',
                'sid' => 'integer',
                'email' => 'string',
                'msgnum' => 'integer',
                'type' => 'string',
                'rule_no' => 'string',
                'rule_cat' => 'string',
                'date_added' => 'string'
            ]);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }
    
    public static function rlde()
    {
        try {
            Node::create('rlde', [
                'id' => 'integer',
                'owner' => 'integer',
                'description' => 'string',
                'code' => 'string',
                'comment' => 'string',
                'rule' => 'string',
                'adddate' => 'string',
                'lastupdate' => 'string'
            ]);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()), ['NodeQ' => 'rlde']);
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()), ['NodeQ' => 'rlde']);
        }
    }
    
    public static function bounce_definition()
    {
        try {
            Node::create('bounce_definition', [
                'rule_cat' => 'string',
                'rule_no' => 'string',
                'reason' => 'string'
            ]);
            
            $q = Node::table('bounce_definition');
            $q->rule_cat = (string) 'unrecognized';
            $q->rule_no = (string) '0000';
            $q->reason = (string) '';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0999';
            $q->reason = (string) 'Domain name not found.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0237';
            $q->reason = (string) 'Email address not found.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0998';
            $q->reason = (string) 'Delivery to recipient failed permanently.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '02361';
            $q->reason = (string) 'User doesn\'t exist.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0236';
            $q->reason = (string) 'User unknown.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0249';
            $q->reason = (string) 'Unknown User.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0157';
            $q->reason = (string) 'No mailbox.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0164';
            $q->reason = (string) 'Can\'t find mailbox.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0169';
            $q->reason = (string) 'Can\'t create output.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0174';
            $q->reason = (string) '????, ?????';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0179';
            $q->reason = (string) 'Unrouteable address.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0013';
            $q->reason = (string) 'Delivery failed.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0232';
            $q->reason = (string) 'Unknown local part.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0233';
            $q->reason = (string) 'Invalid recipient.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0234';
            $q->reason = (string) 'Could not deliver to recipient.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0254';
            $q->reason = (string) 'Email not unique.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0182';
            $q->reason = (string) 'Over quota.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0126';
            $q->reason = (string) 'Quota exceeded.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0158';
            $q->reason = (string) 'Quota exceeded.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0166';
            $q->reason = (string) 'Mailbox full.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0168';
            $q->reason = (string) 'Quota exceeded hard limit.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0253';
            $q->reason = (string) 'Not enough storage space.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0171';
            $q->reason = (string) 'User is inactive.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0201';
            $q->reason = (string) 'Email address is restricted.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0181';
            $q->reason = (string) 'Inactive account.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '124';
            $q->reason = (string) 'Mailbox unavailable.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '7770';
            $q->reason = (string) 'Eamil account does not exist.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0251';
            $q->reason = (string) 'Could not initiate SMTP communication.';
            $q->save();
            
            $q->rule_cat = (string) 'delayed';
            $q->rule_no = (string) '0252';
            $q->reason = (string) 'Could not initiate SMTP conversation with any hosts.';
            $q->save();
            
            $q->rule_cat = (string) 'delayed';
            $q->rule_no = (string) '0256';
            $q->reason = (string) 'Server did not accept request.';
            $q->save();
            
            $q->rule_cat = (string) 'internal_error';
            $q->rule_no = (string) '0172';
            $q->reason = (string) 'I/O error.';
            $q->save();
            
            $q->rule_cat = (string) 'internal_error';
            $q->rule_no = (string) '0173';
            $q->reason = (string) 'Cannot open new mail file.';
            $q->save();
            
            $q->rule_cat = (string) 'defer';
            $q->rule_no = (string) '0163';
            $q->reason = (string) 'Resources temporarily unavailable.';
            $q->save();
            
            $q->rule_cat = (string) 'autoreply';
            $q->rule_no = (string) '0167';
            $q->reason = (string) 'Autoreply.';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0250';
            $q->reason = (string) 'Your message was blocked (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'content_reject';
            $q->rule_no = (string) '0248';
            $q->reason = (string) 'Messages without To: fields are not accepted here.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0235';
            $q->reason = (string) 'This address no longer accepts mail.';
            $q->save();
            
            $q->rule_cat = (string) 'latin_only';
            $q->rule_no = (string) '0043';
            $q->reason = (string) 'Does not accept non-Western (non-Latin) character sets.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0044';
            $q->reason = (string) 'This user doesn\'t have an account.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0045';
            $q->reason = (string) 'Requested action not taken: mailbox unavailable.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0046';
            $q->reason = (string) 'Recipient address rejected.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0047';
            $q->reason = (string) 'in reply to end of DATA command.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0048';
            $q->reason = (string) 'in reply to RCPT TO command.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0247';
            $q->reason = (string) 'Unrouteable mail domain.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0161';
            $q->reason = (string) 'Quota exceeded.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0105';
            $q->reason = (string) 'Over quota.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0129';
            $q->reason = (string) 'Exceed quota.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0145';
            $q->reason = (string) 'This message is larger than the current system limit or the recipient\'s mailbox is full.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0134';
            $q->reason = (string) 'Insufficient system storage.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0998';
            $q->reason = (string) 'Recipient mailbox is full.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0997';
            $q->reason = (string) 'Exceeded storage allocation.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0996';
            $q->reason = (string) 'Mailbox quota usage exceeded.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0995';
            $q->reason = (string) 'User has exhausted allowed storage space.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0994';
            $q->reason = (string) 'User mailbox exceeds allowed size.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0246';
            $q->reason = (string) 'Not enough space.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0192';
            $q->reason = (string) 'File too large.';
            $q->save();
            
            $q->rule_cat = (string) 'oversize';
            $q->rule_no = (string) '0146';
            $q->reason = (string) 'Message larger than limit.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0103';
            $q->reason = (string) 'Recipient not listed in public Name & Address Book.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0106';
            $q->reason = (string) 'User path does not exist.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0108';
            $q->reason = (string) 'Relay access denied.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0185';
            $q->reason = (string) 'Sorry, no valid recipients.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0111';
            $q->reason = (string) 'Invalid email.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0114';
            $q->reason = (string) 'This account has been disabled or discontinued.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0127';
            $q->reason = (string) 'Email account does not exist.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0128';
            $q->reason = (string) 'Unknown or illegal alias.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0122';
            $q->reason = (string) 'Mailbox not available.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0123';
            $q->reason = (string) 'Sorry, no mailbox here by that name.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0125';
            $q->reason = (string) 'Addressee unknown.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0133';
            $q->reason = (string) 'Mailbox temporarily disabled.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0143';
            $q->reason = (string) 'Recipient address rejected: No such user.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0136';
            $q->reason = (string) 'Mailbox not found.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0138';
            $q->reason = (string) 'Mailbox deactivated.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0148';
            $q->reason = (string) 'Recipient rejected.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0151';
            $q->reason = (string) 'Message bounced by administrator.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0152';
            $q->reason = (string) 'Disabled with MTA service.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0154';
            $q->reason = (string) 'Not our customer.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0159';
            $q->reason = (string) 'Unknown address.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0160';
            $q->reason = (string) 'Unknown address error.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '01601';
            $q->reason = (string) 'Bad destination mailbox address.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0186';
            $q->reason = (string) 'Command RCPT recipient not ok.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0189';
            $q->reason = (string) 'Access denied.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0195';
            $q->reason = (string) 'Email lookup failed.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0198';
            $q->reason = (string) 'Recipient not a member of domain.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0202';
            $q->reason = (string) 'Recipient cannot be verified.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0203';
            $q->reason = (string) 'Unable to relay.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0205';
            $q->reason = (string) 'Sorry, that recipient doesn\'t exist.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0207';
            $q->reason = (string) 'Does not have an email account here.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0207';
            $q->reason = (string) 'Recipient does not have an account here.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0220';
            $q->reason = (string) 'This account is not allowed.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0255';
            $q->reason = (string) 'Recipient user name info not unique.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0135';
            $q->reason = (string) 'Inactive recipient.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0155';
            $q->reason = (string) 'Account Inactive.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0170';
            $q->reason = (string) 'Account closed due to inactivity.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0177';
            $q->reason = (string) 'Recipient account not activated.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0183';
            $q->reason = (string) 'Recipient account suspended.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0184';
            $q->reason = (string) 'Recipient no longer exists.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0196';
            $q->reason = (string) 'Deactivated due to abuse.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0209';
            $q->reason = (string) 'Mailbox is restricted.';
            $q->save();
            
            $q->rule_cat = (string) 'inactive';
            $q->rule_no = (string) '0228';
            $q->reason = (string) 'User status is locked.';
            $q->save();
            
            $q->rule_cat = (string) 'user_reject';
            $q->rule_no = (string) '0156';
            $q->reason = (string) 'User refused to receive this email.';
            $q->save();
            
            $q->rule_cat = (string) 'user_reject';
            $q->rule_no = (string) '0206';
            $q->reason = (string) 'Sender email is not in my domain.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0175';
            $q->reason = (string) 'Message refused.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0190';
            $q->reason = (string) 'No permit.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0191';
            $q->reason = (string) 'Domain is not in allowed rcpthosts.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0197';
            $q->reason = (string) 'AUTH FAILED.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0241';
            $q->reason = (string) 'Relaying not allowed.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0204';
            $q->reason = (string) 'Not local host, not a gateway.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0215';
            $q->reason = (string) 'Unauthorized relay msg rejected.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0221';
            $q->reason = (string) 'Transaction failed.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0223';
            $q->reason = (string) 'Invalid data in message.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0224';
            $q->reason = (string) 'Local user only.';
            $q->save();
            
            $q->rule_cat = (string) 'command_reject';
            $q->rule_no = (string) '0225';
            $q->reason = (string) 'Not permitted.';
            $q->save();
            
            $q->rule_cat = (string) 'content_reject';
            $q->rule_no = (string) '0165';
            $q->reason = (string) 'Content reject.';
            $q->save();
            
            $q->rule_cat = (string) 'content_reject';
            $q->rule_no = (string) '0212';
            $q->reason = (string) 'MIME/REJECT: Invalid structure.';
            $q->save();
            
            $q->rule_cat = (string) 'content_reject';
            $q->rule_no = (string) '0217';
            $q->reason = (string) 'Message with invalid header rejected.';
            $q->save();
            
            $q->rule_cat = (string) 'content_reject';
            $q->rule_no = (string) '0218';
            $q->reason = (string) 'Message with invalid header rejected.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0130';
            $q->reason = (string) 'Host unknown.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0180';
            $q->reason = (string) 'Specified domain not available.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0188';
            $q->reason = (string) 'No route to host.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0208';
            $q->reason = (string) 'Unrouteable address.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0238';
            $q->reason = (string) 'Host or domain name not found.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_loop';
            $q->rule_no = (string) '0245';
            $q->reason = (string) 'Mail loops back to myself.';
            $q->save();
            
            $q->rule_cat = (string) 'defer';
            $q->rule_no = (string) '0112';
            $q->reason = (string) 'System busy, try again later.';
            $q->save();
            
            $q->rule_cat = (string) 'defer';
            $q->rule_no = (string) '0116';
            $q->reason = (string) 'Resources temporarily unavailable..';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0101';
            $q->reason = (string) 'Sender is rejected (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0102';
            $q->reason = (string) 'Client host rejected: Access denied (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0104';
            $q->reason = (string) 'Connection refused(mx) (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0144';
            $q->reason = (string) 'Deny ip (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0242';
            $q->reason = (string) 'Client host blocked (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0147';
            $q->reason = (string) 'Mail rejected (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0162';
            $q->reason = (string) 'Spam message detected (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0216';
            $q->reason = (string) 'Rejected as spam (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0200';
            $q->reason = (string) 'Stopped by Spamtrap (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0210';
            $q->reason = (string) 'Verify mailfrom failed,blocked (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0226';
            $q->reason = (string) 'MAIL FROM is mismatched with message header from address (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0211';
            $q->reason = (string) 'Message scored too high on spam scale (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0229';
            $q->reason = (string) 'Client host bypassing service provider\'s mail relay (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0230';
            $q->reason = (string) 'Marked as junk mail (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0243';
            $q->reason = (string) 'Message filtered (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'antispam';
            $q->rule_no = (string) '0222';
            $q->reason = (string) 'Email subject considered spam (spam).';
            $q->save();
            
            $q->rule_cat = (string) 'internal_error';
            $q->rule_no = (string) '0142';
            $q->reason = (string) 'Temporary local problem.';
            $q->save();
            
            $q->rule_cat = (string) 'internal_error';
            $q->rule_no = (string) '0153';
            $q->reason = (string) 'System config error.';
            $q->save();
            
            $q->rule_cat = (string) 'delayed';
            $q->rule_no = (string) '0213';
            $q->reason = (string) 'Delivery temporarily suspended.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0107';
            $q->reason = (string) 'The recipient\'s address had permanent fatal errors.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0141';
            $q->reason = (string) 'Deferred: No such file or directory.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0194';
            $q->reason = (string) 'Mail receiving disabled.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '02441';
            $q->reason = (string) 'Bad destination mailbox.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0244';
            $q->reason = (string) 'Bad destination mailbox address.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0131';
            $q->reason = (string) 'Recipient over quota.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0150';
            $q->reason = (string) 'Recipient quota exceeded.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0187';
            $q->reason = (string) 'Recipient quota exceeded.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0132';
            $q->reason = (string) 'Can\'t create output.';
            $q->save();
            
            $q->rule_cat = (string) 'full';
            $q->rule_no = (string) '0219';
            $q->reason = (string) 'Not enough mailbox space.';
            $q->save();
            
            $q->rule_cat = (string) 'defer';
            $q->rule_no = (string) '0115';
            $q->reason = (string) 'Connection refused.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0239';
            $q->reason = (string) 'Invalid hostname.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0240';
            $q->reason = (string) 'Deferred: No route to host.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0140';
            $q->reason = (string) 'Host unknown.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0118';
            $q->reason = (string) 'Nameserver timedout.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0119';
            $q->reason = (string) 'Deferred: Connection timed out.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_unknown';
            $q->rule_no = (string) '0121';
            $q->reason = (string) 'Deferred: host name lookup failure.';
            $q->save();
            
            $q->rule_cat = (string) 'dns_loop';
            $q->rule_no = (string) '0199';
            $q->reason = (string) 'MX list for domiain. points back elsewhere.';
            $q->save();
            
            $q->rule_cat = (string) 'internal_error';
            $q->rule_no = (string) '0120';
            $q->reason = (string) 'I/O error.';
            $q->save();
            
            $q->rule_cat = (string) 'internal_error';
            $q->rule_no = (string) '0231';
            $q->reason = (string) 'Connection broken.';
            $q->save();
            
            $q->rule_cat = (string) 'other';
            $q->rule_no = (string) '0176';
            $q->reason = (string) 'Delivery to recipient failed.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0193';
            $q->reason = (string) 'User unknown.';
            $q->save();
            
            $q->rule_cat = (string) 'unknown';
            $q->rule_no = (string) '0214';
            $q->reason = (string) 'Service unavailable.';
            $q->save();
            
            $q->rule_cat = (string) 'delayed';
            $q->rule_no = (string) '0110';
            $q->reason = (string) 'Delayed.';
            $q->save();
            
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }
}
