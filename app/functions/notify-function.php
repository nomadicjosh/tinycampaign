<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;

/**
 * tinyCampaign Desktop Notification Functions
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Styles for desktop notification.
 * 
 * @since 2.0.0
 */
function tc_notify_style()
{
    $style = '<link href="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.css" rel="stylesheet" type="text/css" />' . "\n";
    $style .= '<link href="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.brighttheme.css" rel="stylesheet" type="text/css" />' . "\n";
    $style .= '<link href="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.buttons.css" rel="stylesheet" type="text/css" />' . "\n";
    $style .= '<link href="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.nonblock.css" rel="stylesheet" type="text/css" />' . "\n";
    $style .= '<link href="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.mobile.css" rel="stylesheet" type="text/css" />' . "\n";
    $style .= '<link href="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.history.css" rel="stylesheet" type="text/css" />' . "\n";
    echo $style;
}

/**
 * Scripts for desktop notification.
 * 
 * @since 2.0.0
 */
function tc_notify_script()
{
    $script = '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.animate.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.buttons.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.confirm.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.nonblock.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.mobile.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.desktop.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.history.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.callbacks.js"></script>' . "\n";
    $script .= '<script type="text/javascript" src="' . get_base_url() . 'static/assets/plugins/pnotify/src/pnotify.reference.js"></script>' . "\n";
    echo $script;
}

/**
 * Desktop Push Notification
 * 
 * Notifications that can be pushed at a delayed time.
 * 
 * @since 2.0.0
 * @param string $title Give title of notification.
 * @param string $message Message that should be displayed.
 */
function tc_push_notify($title, $message)
{
    $app = \Liten\Liten::getInstance();
    // Create a Notifier
    $notifier = NotifierFactory::create();

    // Create your notification
    $notification = (new Notification())
        ->setTitle($title)
        ->setBody($message)
        ->setIcon(BASE_PATH . 'static/assets/imgages/icon-success.png');

    // Send it
    return $app->hook->{'apply_filter'}('push_notify', $notifier->send($notification));
}
