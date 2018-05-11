<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\Exception\Exception;
use PDOException as ORMException;
use Cascade\Cascade;

/**
 * tinyCampaign Rules Functions
 *
 * @license GPLv3
 *
 * @since 2.0.6
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();

try {
    /**
     * Creates rlde node if it does not exist.
     * 
     * @since 2.0.6
     */
    Node::dispense('rlde');
} catch (NodeQException $e) {
    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
} catch (Exception $e) {
    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
}

/**
 * Retrieve a list of rules.
 * 
 * @since 6.3.0
 * @param string $active
 */
function get_rules($active = null)
{
    try {
        $rlde = Node::table('rlde')->findAll();
        foreach ($rlde as $rule) {
            echo '<option value="' . _escape($rule->id) . '"' . selected(_escape($rule->id), $active, false) . '>' . '(' . _escape($rule->code) . ') - ' . _escape($rule->description) . '</option>';
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

/**
 * Retrieve rule data as object by the rule's unique code.
 * 
 * @since 2.0.6
 * @param string $code Rule's unique code.
 * @return object
 */
function get_rule_by_code($code)
{
    try {
        $rlde = Node::table('rlde')->where('code', '=', $code)->find();
        return $rlde;
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

/**
 * Retrieve rule data as object by the rule's unique id.
 * 
 * @since 2.0.6
 * @param string $id Rule's unique id.
 * @return object
 */
function get_rule_by_id($id)
{
    try {
        $rlde = Node::table('rlde')->where('id', '=', $id)->find();
        return $rlde;
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

/**
 * Retrieve a list of states.
 * 
 * @since 2.0.6
 */
function get_rlde_states()
{
    $app = \Liten\Liten::getInstance();
    try {
        $q = $app->db->state()
                ->find();
        foreach ($q as $r) {
            echo "'" . _escape($r->code) . "'" . ': ' . '"' . _escape($r->code) . " " . _escape($r->name) . '"' . ',' . "\n";
        }
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _tc_flash()->error(_tc_flash()->notice(409));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _tc_flash()->error(_tc_flash()->notice(409));
    }
}

/**
 * Retrieve a list of countries.
 * 
 * @since 2.0.6
 */
function get_rlde_countries()
{
    $app = \Liten\Liten::getInstance();
    try {
        $q = $app->db->country()
                ->find();
        foreach ($q as $r) {
            echo "'" . _escape($r->iso2) . "'" . ': ' . '"' . _escape($r->iso2) . " " . _escape($r->short_name) . '"' . ',' . "\n";
        }
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _tc_flash()->error(_tc_flash()->notice(409));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _tc_flash()->error(_tc_flash()->notice(409));
    }
}

/**
 * Retrieves all the tags from every subscriber
 * and removes duplicates.
 *
 * @since 2.0.6
 * @return mixed
 */
function get_rlde_subscriber_tags()
{
    $app = \Liten\Liten::getInstance();
    try {
        $tagging = $app->db->subscriber()
                ->select('tags');
        $q = $tagging->find(function ($data) {
            $array = [];
            foreach ($data as $d) {
                $array[] = $d;
            }
            return $array;
        });
        $tags = [];
        foreach ($q as $r) {
            $tags = array_merge($tags, explode(",", $r['tags']));
        }
        $tags = array_unique_compact($tags);
        foreach ($tags as $key => $value) {
            if ($value == "" || strlen($value) <= 0) {
                unset($tags[$key]);
            }
        }
        foreach ($tags as $tag) {
            echo "'" . _escape($tag) . "'" . ': ' . '"' . _escape($tag) . '"' . ',' . "\n";
        }
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _tc_flash()->error(_tc_flash()->notice(409));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
        _tc_flash()->error(_tc_flash()->notice(409));
    }
}
