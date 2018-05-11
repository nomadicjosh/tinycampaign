<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;
use Cascade\Cascade;

/**
 * tinyCampaign Hooks Helper & Wrapper
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Wrapper function for Hooks::register_admin_page() and
 * register's a plugin administration page.
 *
 * @see Hooks::register_admin_page()
 *
 * @since 2.0.0
 * @param string $slug
 *            Plugin's slug.
 * @param string $title
 *            Title that is show for the plugin's link.
 * @param string $function
 *            The function which prints the plugin's page.
 */
function register_admin_page($slug, $title, $function)
{
    return app()->hook->register_admin_page($slug, $title, $function);
}

/**
 * Wrapper function for Hooks::activate_plugin() and
 * activates plugin based on $_GET['id'].
 *
 * @see Hooks::activate_plugin()
 *
 * @since 2.0.0
 * @param string $id
 *            ID of the plugin to be activated.
 * @return mixed Activates plugin if it exists.
 */
function activate_plugin($id)
{
    return app()->hook->activate_plugin($id);
}

/**
 * Wrapper function for Hooks::deactivate_plugin() and
 * deactivates plugin based on $_GET['id'].
 *
 * @see Hooks::deactivate_plugin()
 *
 * @since 2.0.0
 * @param string $id
 *            ID of the plugin to be deactivated.
 * @return mixed Deactivates plugin if it exists and is active.
 */
function deactivate_plugin($id)
{
    return app()->hook->deactivate_plugin($id);
}

/**
 * Wrapper function for Hooks::load_activated_plugins() and
 * loads all activated plugins for inclusion.
 *
 * @see Hooks::load_activated_plugins()
 *
 * @since 2.0.0
 * @param string $plugins_dir
 *            Loads plugins from specified folder
 * @return mixed
 */
function load_activated_plugins($plugins_dir = '')
{
    return app()->hook->load_activated_plugins($plugins_dir);
}

/**
 * Wrapper function for Hooks::is_plugin_activated() and
 * checks if a particular plugin is activated
 *
 * @see Hooks::is_plugin_activated()
 *
 * @since 2.0.0
 * @param string $plugin
 *            Name of plugin file.
 * @return bool False if plugin is not activated and true if it is activated.
 */
function is_plugin_activated($plugin)
{
    return app()->hook->is_plugin_activated($plugin);
}

/**
 * Wrapper function for Hooks::get_option() method and
 * reads an option from options_meta table.
 *
 * @see Hooks::get_option()
 *
 * @since 2.0.0
 * @param string $meta_key
 *            Name of the option to retrieve.
 * @param mixed $default
 *            The default value.
 * @return mixed Returns value of default if not found.
 */
function get_option($meta_key, $default = false)
{
    return app()->hook->{'get_option'}($meta_key, $default);
}

/**
 * Wrapper function for Hooks::update_option() method and
 * updates (add if doesn't exist) an option to options_meta table.
 *
 * @see Hooks::update_option()
 *
 * @since 2.0.0
 * @param string $meta_key
 *            Name of the option to update/add.
 * @param mixed $newvalue
 *            The new value to update with or add.
 * @return bool False if not updated or true if updated.
 */
function update_option($meta_key, $newvalue)
{
    return app()->hook->{'update_option'}($meta_key, $newvalue);
}

/**
 * Wrapper function for Hooks::add_option() method and
 * adds a new option to the options_meta table.
 *
 * @see Hooks::add_option()
 *
 * @since 2.0.0
 * @param string $name
 *            Name of the option to add.
 * @param mixed $value
 *            The option value.
 * @return bool False if not added or true if added.
 */
function add_option($name, $value = '')
{
    return app()->hook->{'add_option'}($name, $value);
}

/**
 * Wrapper function for Hooks::delete_option() method and
 * deletes an option for the options_meta table.
 *
 * @see Hooks::delete_option()
 *
 * @since 2.0.0
 * @param string $name
 *            Name of the option to delete.
 * @return bool False if not deleted or true if deleted.
 */
function delete_option($name)
{
    return app()->hook->{'delete_option'}($name);
}

/**
 * Mark a function as deprecated and inform when it has been used.
 *
 * There is a hook deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if APP_ENV is DEV.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @since 2.0.0
 *       
 * @param string $function_name
 *            The function that was called.
 * @param string $release
 *            The release of tinyCampaign that deprecated the function.
 * @param string $replacement
 *            Optional. The function that should have been called. Default null.
 */
function _deprecated_function($function_name, $release, $replacement = null)
{
    /**
     * Fires when a deprecated function is called.
     *
     * @since 2.0.0
     *       
     * @param string $function_name
     *            The function that was called.
     * @param string $replacement
     *            The function that should have been called.
     * @param string $release
     *            The release of tinyCampaign that deprecated the function.
     */
    app()->hook->{'do_action'}('deprecated_function_run', $function_name, $replacement, $release);

    /**
     * Filter whether to trigger an error for deprecated functions.
     *
     * @since 2.0.0
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated functions. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_function_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($replacement)) {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />'), $function_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />'), $function_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($replacement)) {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />', $function_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $function_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Mark a class as deprecated and inform when it has been used.
 *
 * There is a hook deprecated_class_run that will be called that can be used
 * to get the backtrace up to what file, function/class called the deprecated
 * class.
 *
 * The current behavior is to trigger a user error if APP_ENV is DEV.
 *
 * This function is to be used in every class that is deprecated.
 *
 * @since 2.0.0
 *       
 * @param string $class_name
 *            The class that was called.
 * @param string $release
 *            The release of tinyCampaign that deprecated the class.
 * @param string $replacement
 *            Optional. The class that should have been called. Default null.
 */
function _deprecated_class($class_name, $release, $replacement = null)
{
    /**
     * Fires when a deprecated class is called.
     *
     * @since 2.0.0
     *       
     * @param string $class_name
     *            The class that was called.
     * @param string $replacement
     *            The class that should have been called.
     * @param string $release
     *            The release of tinyCampaign that deprecated the class.
     */
    app()->hook->{'do_action'}('deprecated_class_run', $class_name, $replacement, $release);

    /**
     * Filter whether to trigger an error for deprecated classes.
     *
     * @since 2.0.0
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated classes. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_class_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($replacement)) {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s instead. <br />'), $class_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />'), $class_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($replacement)) {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s instead. <br />', $class_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $class_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Mark a class's method as deprecated and inform when it has been used.
 *
 * There is a hook deprecated_class_method_run that will be called that can be used
 * to get the backtrace up to what file, function/class called the deprecated
 * method.
 *
 * The current behavior is to trigger a user error if APP_ENV is DEV.
 *
 * This function is to be used in every class's method that is deprecated.
 *
 * @since 2.0.0
 *       
 * @param string $method_name
 *            The class method that was called.
 * @param string $release
 *            The release of tinyCampaign that deprecated the class's method.
 * @param string $replacement
 *            Optional. The class method that should have been called. Default null.
 */
function _deprecated_class_method($method_name, $release, $replacement = null)
{
    /**
     * Fires when a deprecated class method is called.
     *
     * @since 2.0.0
     *       
     * @param string $method_name
     *            The class's method that was called.
     * @param string $replacement
     *            The class method that should have been called.
     * @param string $release
     *            The release of tinyCampaign that deprecated the class's method.
     */
    app()->hook->{'do_action'}('deprecated_class_method_run', $method_name, $replacement, $release);

    /**
     * Filter whether to trigger an error for deprecated class methods.
     *
     * @since 2.0.0
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated class methods. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_class_method_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($replacement)) {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />'), $method_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />'), $method_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($replacement)) {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s! Use %3$s() instead. <br />', $method_name, $release, $replacement), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $method_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Mark a function argument as deprecated and inform when it has been used.
 *
 * This function is to be used whenever a deprecated function argument is used.
 * Before this function is called, the argument must be checked for whether it was
 * used by comparing it to its default value or evaluating whether it is empty.
 * For example:
 *
 * if ( ! empty( $deprecated ) ) {
 * _deprecated_argument( __FUNCTION__, '6.1.00' );
 * }
 *
 *
 * There is a hook deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function used the deprecated
 * argument.
 *
 * The current behavior is to trigger a user error if APP_ENV is set to DEV.
 *
 * @since 2.0.0
 *       
 * @param string $function_name
 *            The function that was called.
 * @param string $release
 *            The release of tinyCampaign that deprecated the argument used.
 * @param string $message
 *            Optional. A message regarding the change. Default null.
 */
function _deprecated_argument($function_name, $release, $message = null)
{
    /**
     * Fires when a deprecated argument is called.
     *
     * @since 2.0.0
     *       
     * @param string $function_name
     *            The function that was called.
     * @param string $message
     *            A message regarding the change.
     * @param string $release
     *            The release of tinyCampaign that deprecated the argument used.
     */
    app()->hook->{'do_action'}('deprecated_argument_run', $function_name, $message, $release);
    /**
     * Filter whether to trigger an error for deprecated arguments.
     *
     * @since 2.0.0
     *       
     * @param bool $trigger
     *            Whether to trigger the error for deprecated arguments. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('deprecated_argument_trigger_error', true)) {
        if (function_exists('_t')) {
            if (!is_null($message)) {
                _trigger_error(sprintf(_t('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s! %3$s. <br />'), $function_name, $release, $message), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf(_t('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s with no alternative available. <br />'), $function_name, $release), E_USER_DEPRECATED);
            }
        } else {
            if (!is_null($message)) {
                _trigger_error(sprintf('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s! %3$s. <br />', $function_name, $release, $message), E_USER_DEPRECATED);
            } else {
                _trigger_error(sprintf('%1$s() was called with an argument that is <strong>deprecated</strong> since release %2$s with no alternative available. <br />', $function_name, $release), E_USER_DEPRECATED);
            }
        }
    }
}

/**
 * Mark something as being incorrectly called.
 *
 * There is a hook incorrectly_called_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if APP_ENV is set to DEV.
 *
 * @since 2.0.0
 *       
 * @param string $function_name
 *            The function that was called.
 * @param string $message
 *            A message explaining what has been done incorrectly.
 * @param string $release
 *            The release of tinyCampaign where the message was added.
 */
function _incorrectly_called($function_name, $message, $release)
{
    /**
     * Fires when the given function is being used incorrectly.
     *
     * @since 2.0.0
     *       
     * @param string $function_name
     *            The function that was called.
     * @param string $message
     *            A message explaining what has been done incorrectly.
     * @param string $release
     *            The release of tinyCampaign where the message was added.
     */
    app()->hook->{'do_action'}('incorrectly_called_run', $function_name, $message, $release);

    /**
     * Filter whether to trigger an error for _incorrectly_called() calls.
     *
     * @since 2.0.0
     *       
     * @param bool $trigger
     *            Whether to trigger the error for _incorrectly_called() calls. Default true.
     */
    if (APP_ENV == 'DEV' && app()->hook->{'apply_filter'}('incorrectly_called_trigger_error', true)) {
        if (function_exists('_t')) {
            $release = is_null($release) ? '' : sprintf(_t('(This message was added in release %s.) <br /><br />'), $release);
            /* translators: %s: Codex URL */
            $message .= ' ' . sprintf(_t('Please see <a href="%s">Debugging in tinyCampaign</a> for more information.'), 'https://developer.edutracsis.com/codex/debugging-edutrac-sis/');
            _trigger_error(sprintf(_t('%1$s() was called <strong>incorrectly</strong>. %2$s %3$s <br />'), $function_name, $message, $release));
        } else {
            $release = is_null($release) ? '' : sprintf('(This message was added in release %s.) <br /><br />', $release);
            $message .= sprintf(' Please see <a href="%s">Debugging in tinyCampaign</a> for more information.', 'https://developer.edutracsis.com/codex/debugging-edutrac-sis/');
            _trigger_error(sprintf('%1$s() was called <strong>incorrectly</strong>. %2$s %3$s <br />', $function_name, $message, $release));
        }
    }
}

/**
 * Prints copyright in the dashboard footer.
 *
 * @since 2.0.0
 */
function tc_dashboard_copyright_footer()
{
    $copyright = '<!--  Copyright Line -->' . "\n";
    $copyright .= '<div class="copy">' . _t('&copy; 2013') . ' - ' . foot_release() . ' &nbsp; <a href="http://www.litenframework.com/"><img src="' . get_base_url() . 'static/assets/images/button.png" alt="Built with Liten Framework"/></a></div>' . "\n";
    $copyright .= '<!--  End Copyright Line -->' . "\n";

    return app()->hook->{'apply_filter'}('dashboard_copyright_footer', $copyright);
}

/**
 * Fires the tc_dashboard_head action.
 *
 * @since 2.0.0
 */
function tc_dashboard_head()
{
    /**
     * Registers & enqueues a stylesheet to be printed in dashboard head section.
     *
     * @since 2.0.6
     */
    app()->hook->{'do_action'}('dashboard_enqueue_css');
    /**
     * Prints scripts and/or data in the head tag of the dashboard.
     *
     * @since 2.0.0
     */
    app()->hook->{'do_action'}('tc_dashboard_head');
}

/**
 * Fires the tc_dashboard_footer action via the dashboard.
 *
 * @since 2.0.0
 */
function tc_dashboard_footer()
{
    /**
     * Registers & enqueues javascript to be printed in dashboard footer section.
     *
     * @since 2.0.6
     */
    app()->hook->{'do_action'}('dashboard_enqueue_js');
    /**
     * Prints scripts and/or data before the ending body tag
     * of the dashboard.
     *
     * @since 2.0.0
     */
    app()->hook->{'do_action'}('tc_dashboard_footer');
}

/**
 * Includes and loads all activated plugins.
 *
 * @since 2.0.0
 */
load_activated_plugins(APP_PATH . 'plugins' . DS);

/**
 * An action called to add the plugin's link
 * to the menu structure.
 *
 * @since 2.0.0
 * @uses app()->hook->{'do_action'}() Calls 'admin_menu' hook.
 */
app()->hook->{'do_action'}('admin_menu');

/**
 * An action called to add custom page links
 * to menu structure.
 *
 * @since 4.2.0
 * @uses app()->hook->{'do_action'}() Calls 'custom_plugin_page' hook.
 */
app()->hook->{'do_action'}('custom_plugin_page');

/**
 * Fires once activated plugins have loaded.
 *
 * @since 2.0.0
 */
app()->hook->{'do_action'}('plugin_loaded');

/**
 * Fires after tinyCampaign has finished loading but before any headers are sent.
 *
 * @since 2.0.0
 */
app()->hook->{'do_action'}('init');

/**
 * Fires the admin_head action.
 *
 * @since 2.0.0
 */
function admin_head()
{
    /**
     * Prints scripts and/or data in the head tag of the dashboard.
     *
     * @since 2.0.0
     */
    app()->hook->{'do_action'}('admin_head');
}

/**
 * Fires the footer action via the dashboard.
 *
 * @since 2.0.0
 */
function footer()
{
    /**
     * Prints scripts and/or data before the ending body tag
     * of the dashboard.
     *
     * @since 2.0.0
     */
    app()->hook->{'do_action'}('footer');
}

/**
 * Fires the release action.
 *
 * @since 2.0.0
 */
function release()
{
    /**
     * Prints tinyCampaign release information.
     *
     * @since 2.0.0
     */
    app()->hook->{'do_action'}('release');
}

/**
 * Fires the dashboard_top_widgets action.
 *
 * @since 2.0.0
 */
function dashboard_top_widgets()
{
    /**
     * Prints widgets at the top portion of the dashboard.
     *
     * @since 2.0.0
     */
    app()->hook->{'do_action'}('dashboard_top_widgets');
}

/**
 * Shows number of active email lists.
 *
 * @since 2.0.0
 */
function dashboard_email_list_count()
{
    // Number of overall email lists.
    try {
        $lists = app()->db->list()
                ->where('list.owner = ?', get_userdata('id'))
                ->count('id');
    } catch (ORMException $e) {
        _tc_flash()->{'error'}($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->{'error'}($e->getMessage());
    }

    $count = '<div class="col-lg-3 col-xs-6">';
    $count .= '<div class="small-box bg-aqua">';
    $count .= '<div class="inner">';
    $count .= '<h3>' . _escape($lists) . '</h3>';
    $count .= '<p>' . _t('Email Lists') . '</p>';
    $count .= '</div>';
    $count .= '<div class="icon"><i class="ion ion-ios-list"></i></div>';
    $count .= '<a href="' . get_base_url() . 'list/" class="small-box-footer">' . _t('More info') . '<i class="fa fa-arrow-circle-right"></i></a>';
    $count .= '</div></div>';
    echo app()->hook->{'apply_filter'}('dashboard_email_list_count', $count);
}

/**
 * Shows number of sent campaigns.
 *
 * @since 2.0.0
 */
function dashboard_campaign_count()
{
    // Number of overall emails sent
    try {
        $emails = app()->db->campaign()
                ->where('campaign.owner = ?', get_userdata('id'))->_and_()
                ->where('campaign.status = "sent"')
                ->count('campaign.id');
    } catch (ORMException $e) {
        _tc_flash()->{'error'}($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->{'error'}($e->getMessage());
    }

    $count = '<div class="col-lg-3 col-xs-6">';
    $count .= '<div class="small-box bg-green">';
    $count .= '<div class="inner">';
    $count .= '<h3>' . _escape($emails) . '</h3>';
    $count .= '<p>' . _t('Campaigns') . '</p>';
    $count .= '</div>';
    $count .= '<div class="icon"><i class="ion ion-email"></i></div>';
    $count .= '<a href="' . get_base_url() . 'campaign/" class="small-box-footer">' . _t('More info') . '<i class="fa fa-arrow-circle-right"></i></a>';
    $count .= '</div></div>';
    echo app()->hook->{'apply_filter'}('dashboard_campaign_count', $count);
}

/**
 * Shows number of active subscribers.
 *
 * @since 2.0.0
 */
function dashboard_subscriber_count()
{
    // Number of overall subscribers
    try {
        $subs = app()->db->subscriber_list()
                ->_join('list', 'subscriber_list.lid = list.id')
                ->where('list.owner = ?', get_userdata('id'))->_and_()
                ->where('subscriber_list.confirmed = "1"')->_and_()
                ->where('subscriber_list.unsubscribed = "0"')
                ->count('subscriber_list.id');
    } catch (ORMException $e) {
        _tc_flash()->{'error'}($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->{'error'}($e->getMessage());
    }

    $count = '<div class="col-lg-3 col-xs-6">';
    $count .= '<div class="small-box bg-yellow">';
    $count .= '<div class="inner">';
    $count .= '<h3>' . _escape($subs) . '</h3>';
    $count .= '<p>' . _t('Active Subscribers') . '</p>';
    $count .= '</div>';
    $count .= '<div class="icon"><i class="ion ion-ios-people"></i></div>';
    $count .= '<a href="' . get_base_url() . 'subscriber/" class="small-box-footer">' . _t('More info') . '<i class="fa fa-arrow-circle-right"></i></a>';
    $count .= '</div></div>';
    echo app()->hook->{'apply_filter'}('dashboard_subscriber_count', $count);
}

/**
 * Shows number of emails sent.
 *
 * @since 2.0.0
 */
function dashboard_email_sent_count()
{
    try {
        $emails = app()->db->campaign_queue()
                ->_join('campaign', 'campaign_queue.cid = campaign.id')
                ->where('campaign.owner = ?', get_userdata('id'))->_and_()
                ->where('campaign_queue.is_sent = "true"')
                ->count('campaign_queue.id');
    } catch (ORMException $e) {
        _tc_flash()->{'error'}($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->{'error'}($e->getMessage());
    }

    $count = '<div class="col-lg-3 col-xs-6">';
    $count .= '<div class="small-box bg-red">';
    $count .= '<div class="inner">';
    $count .= '<h3>' . _escape($emails) . '</h3>';
    $count .= '<p>' . _t('Emails Sent') . '</p>';
    $count .= '</div>';
    $count .= '<div class="icon"><i class="ion ion-paper-airplane"></i></div>';
    $count .= '<a href="' . get_base_url() . 'campaign/" class="small-box-footer">' . _t('More info') . '<i class="fa fa-arrow-circle-right"></i></a>';
    $count .= '</div></div>';
    echo app()->hook->{'apply_filter'}('dashboard_email_sent_count', $count);
}

/**
 * Retrieve javascript directory uri.
 *
 * @since 2.0.0
 * @uses app()->hook->{'apply_filter'}() Calls 'javascript_directory_uri' filter.
 *      
 * @return string eduTrac javascript url.
 */
function get_javascript_directory_uri()
{
    $directory = 'static/assets/components';
    $javascript_root_uri = get_base_url();
    $javascript_dir_uri = "$javascript_root_uri$directory/";
    return app()->hook->{'apply_filter'}('javascript_directory_uri', $javascript_dir_uri, $javascript_root_uri, $directory);
}

/**
 * Retrieve less directory uri.
 *
 * @since 2.0.0
 * @uses app()->hook->{'apply_filter'}() Calls 'less_directory_uri' filter.
 *      
 * @return string eduTrac less url.
 */
function get_less_directory_uri()
{
    $directory = 'static/assets/less';
    $less_root_uri = get_base_url();
    $less_dir_uri = "$less_root_uri$directory/";
    return app()->hook->{'apply_filter'}('less_directory_uri', $less_dir_uri, $less_root_uri, $directory);
}

/**
 * Retrieve css directory uri.
 *
 * @since 2.0.0
 * @uses app()->hook->{'apply_filter'}() Calls 'css_directory_uri' filter.
 *      
 * @return string eduTrac css url.
 */
function get_css_directory_uri()
{
    $directory = 'static/assets/css';
    $css_root_uri = get_base_url();
    $css_dir_uri = "$css_root_uri$directory/";
    return app()->hook->{'apply_filter'}('css_directory_uri', $css_dir_uri, $css_root_uri, $directory);
}

/**
 * Parses a string into variables to be stored in an array.
 *
 * Uses {@link http://www.php.net/parse_str parse_str()}
 *
 * @since 2.0.0
 * @param string $string
 *            The string to be parsed.
 * @param array $array
 *            Variables will be stored in this array.
 */
function tc_parse_str($string, $array)
{
    parse_str($string, $array);
    /**
     * Filter the array of variables derived from a parsed string.
     *
     * @since 4.2.0
     * @param array $array
     *            The array populated with variables.
     */
    $array = app()->hook->{'apply_filter'}('tc_parse_str', $array);
}

function get_user_avatar($email, $s = 80, $class = '', $d = 'mm', $r = 'g', $img = false)
{
    if (function_exists('enable_url_ssl') || function_exists('enable_force_url_ssl')) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    $url = $protocol . "www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=200&d=$d&r=$r";

    if (get_http_response_code($protocol . 'www.gravatar.com/') != 302) {
        $static_image_url = get_base_url() . "static/assets/img/avatar.png?s=200";
        $avatarsize = getimagesize($static_image_url);
        $avatar = '<img src="' . get_base_url() . 'static/assets/img/avatar.png" ' . resize_image($avatarsize[1], $avatarsize[1], $s) . ' class="' . $class . '" />';
    } else {
        $avatarsize = getimagesize($url);
        $avatar = '<img src="' . $url . '" ' . resize_image($avatarsize[1], $avatarsize[1], $s) . ' class="' . $class . '" />';
    }

    return app()->hook->{'apply_filter'}('user_avatar', $avatar, $email, $s, $class, $d, $r, $img);
}

function nocache_headers()
{
    $headers = [
        'Expires' => 'Sun, 01 Jan 2014 00:00:00 GMT',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache'
    ];
    foreach ($headers as $k => $v) {
        header("{$k}: {$v}");
    }
    return app()->hook->{'apply_filter'}('nocache_headers', $headers);
}

/**
 * Compares release values.
 *
 * @since 2.0.0
 * @param string $current
 *            Current release value.
 * @param string $latest
 *            Latest release value.
 * @param string $operator
 *            Operand use to compare current and latest release values.
 * @return bool
 */
function compare_releases($current, $latest, $operator = '>')
{
    $php_function = version_compare($latest, $current, $operator);
    /**
     * Filters the comparison between two release.
     *
     * @since 2.0.0
     * @param $php_function PHP
     *            function for comparing two release values.
     */
    $release = app()->hook->{'apply_filter'}('compare_releases', $php_function);

    if ($release) {
        return $latest;
    } else {
        return false;
    }
}

/**
 * Retrieves a response code from the header
 * of a given resource.
 *
 * @since 2.0.0
 * @param string $url
 *            URL of resource/website.
 * @return int HTTP response code.
 */
function get_http_response_code($url)
{
    $headers = get_headers($url);
    $status = substr($headers[0], 9, 3);
    /**
     * Filters the http response code.
     *
     * @since 2.0.0
     * @param
     *            string
     */
    return app()->hook->{'apply_filter'}('http_response_code', $status);
}

/**
 * Plugin success message when plugin is activated successfully.
 *
 * @since 2.0.0
 * @param string $plugin_name
 *            The name of the plugin that was just activated.
 */
function tc_plugin_activate_message($plugin_name)
{
    $success = _tc_flash()->{'success'}(_t('Plugin <strong>activated</strong>.'));
    /**
     * Filter the default plugin success activation message.
     *
     * @since 2.0.0
     * @param string $success
     *            The success activation message.
     * @param string $plugin_name
     *            The name of the plugin that was just activated.
     */
    return app()->hook->{'apply_filter'}('tc_plugin_activate_message', $success, $plugin_name);
}

/**
 * Plugin success message when plugin is deactivated successfully.
 *
 * @since 2.0.0
 * @param string $plugin_name
 *            The name of the plugin that was just deactivated.
 */
function tc_plugin_deactivate_message($plugin_name)
{
    $success = _tc_flash()->{'success'}(_t('Plugin <strong>deactivated</strong>.'));
    /**
     * Filter the default plugin success deactivation message.
     *
     * @since 2.0.0
     * @param string $success
     *            The success deactivation message.
     * @param string $plugin_name
     *            The name of the plugin that was just deactivated.
     */
    return app()->hook->{'apply_filter'}('tc_plugin_deactivate_message', $success, $plugin_name);
}

/**
 * Dashboard router function.
 * 
 * Includes dashboard router filter (dashboard_router).
 *
 * @since 2.0.0
 */
function _tc_dashboard_router()
{
    $router = app()->config('routers_dir') . 'dashboard.router.php';
    if (!app()->hook->{'has_filter'}('dashboard_router')) {
        require($router);
    }
    return app()->hook->{'apply_filter'}('dashboard_router', $router);
}

/**
 * Error router function.
 * 
 * Includes error router filter (error_router).
 *
 * @since 2.0.0
 */
function _tc_error_router()
{
    $router = app()->config('routers_dir') . 'error.router.php';
    if (!app()->hook->{'has_filter'}('error_router')) {
        require($router);
    }
    return app()->hook->{'apply_filter'}('error_router', $router);
}

/**
 * Register stylesheet.
 * 
 * @since 2.0.0
 * @param string $handle
 */
function tc_register_style($handle)
{
    return app()->asset->{'register_style'}($handle);
}

/**
 * Register javascript.
 * 
 * @since 2.0.0
 * @param string $handle
 */
function tc_register_script($handle)
{
    return app()->asset->{'register_script'}($handle);
}

/**
 * Enqueue stylesheet.
 * 
 * @since 2.0.0
 */
function tc_enqueue_style()
{
    echo app()->asset->{'enqueue_style'}();
}

/**
 * Enqueue javascript.
 * 
 * @since 2.0.0
 */
function tc_enqueue_script()
{
    echo app()->asset->{'enqueue_script'}();
}

/**
 * Mini logo. Filterable.
 * 
 * @since 2.0.0
 * @return string
 */
function get_logo_mini()
{
    $logo = '<strong>' . _t('ti') . '</strong>' . _t('ny');
    return app()->hook->{'apply_filter'}('logo_mini', $logo);
}

/**
 * Checks data to make sure it is a valid request.
 * 
 * @since 2.0.1
 * @param mixed $data
 */
function tc_validation_check($data)
{
    if ($data['m6qIHt4Z5evV'] != '' || !empty($data['m6qIHt4Z5evV'])) {
        _tc_flash()->{'error'}(_t('Spam is not allowed.'), get_base_url() . 'spam' . '/');
        exit();
    }

    if ($data['YgexGyklrgi1'] != '' || !empty($data['YgexGyklrgi1'])) {
        _tc_flash()->{'error'}(_t('Spam is not allowed.'), get_base_url() . 'spam' . '/');
        exit();
    }
}

/**
 * Large logo. Filterable.
 * 
 * @since 2.0.0
 * @return string
 */
function get_logo_large()
{
    $logo = '<strong>' . _t('tiny') . '</strong>' . _t('Campaign');
    return app()->hook->{'apply_filter'}('logo_large', $logo);
}

/**
 * Generates tinyCampaign logo in email footer.
 * 
 * @since 2.0.2
 * @return mixed
 */
function tinyc_footer_logo()
{
    $div = '<div id="wrapper" style="margin:0 auto !important;text-align:center !important;">';
    $div .= '<div id="logo-track"><img src="' . get_base_url() . 'static/assets/img/tinyC-Logo.png" alt="tinyCampaign" /></div>';
    $div .= '</div>';
    return app()->hook->{'apply_filter'}('footer_logo', $div);
}

/**
 * Generates tracking code.
 * 
 * @since 2.0.1
 * @param int $cid Campaign id.
 * @param int $sid Subscriber id.
 * @return mixed
 */
function campaign_tracking_code($cid, $sid)
{
    $image = '<img src="' . get_base_url() . 'tracking/cid/' . $cid . '/sid/' . $sid . '/" width="1" height="1" border="0" alt="tracking" />';
    return app()->hook->apply_filter('tracking_code', $image, $cid, $sid);
}

function tc_smtp($tcMailer)
{
    if (_escape(get_option('tc_smtp_host')) && _escape(get_option('tc_smtp_host')) != '' && _escape(get_option('tc_smtp_status')) == 1) {

        try {
            $node = Node::table('php_encryption')->find(1);
        } catch (NodeQException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (NotFoundException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }

        try {
            $password = Crypto::decrypt(_escape(get_option('tc_smtp_password')), Key::loadFromAsciiSafeString($node->key));
        } catch (Defuse\Crypto\Exception\BadFormatException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }

        try {
            $tcMailer->Mailer = "smtp";
            $tcMailer->ContentType = "text/html";
            $tcMailer->CharSet = "UTF-8";
            $tcMailer->XMailer = 'tinyCampaign ' . CURRENT_RELEASE;
            $tcMailer->From = _escape(get_option("system_email"));
            $tcMailer->FromName = _escape(get_option("system_name"));
            $tcMailer->Sender = (_escape(get_option('tc_bmh_username')) == '' ? $tcMailer->From : _escape(get_option('tc_bmh_username'))); // Return-Path
            $tcMailer->AddReplyTo($tcMailer->From, $tcMailer->FromName); // Reply-To
            $tcMailer->Host = _escape(get_option("tc_smtp_host"));
            $tcMailer->SMTPSecure = _escape(get_option("tc_smtp_smtpsecure"));
            $tcMailer->Port = _escape(get_option("tc_smtp_port"));
            $tcMailer->SMTPAuth = true;
            $tcMailer->isHTML(true);
            $tcMailer->Username = _escape(get_option("tc_smtp_username"));
            $tcMailer->Password = $password;
            _tc_flash()->{'success'}(_t('Email Sent.'));
        } catch (phpmailerException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (\app\src\Exception\Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }
    }
}

/**
 * Function used for sending test email.
 * 
 * @since 2.0.5
 * @param object $data Object of info passed to PHPMailer.
 * @param string $to Email recipient.
 * @param string $subject Email subject.
 * @param string $html HTML version of the email message.
 * @param string $text Text version of the email message.
 * @param object $message Object of \app\src\tc_Queue().
 */
function tinyc_test_email($data, $to, $subject, $html, $text = '', $message = '')
{
    if (is_object($data)) {
        try {
            $node = Node::table('php_encryption')->find(1);
        } catch (NodeQException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (NotFoundException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }

        try {
            $password = Crypto::decrypt(_escape($data->password), Key::loadFromAsciiSafeString($node->key));
        } catch (Defuse\Crypto\Exception\BadFormatException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }

        try {
            $tcMailer = _tc_phpmailer(true);
            $tcMailer->Mailer = "smtp";
            $tcMailer->ContentType = "text/html";
            $tcMailer->CharSet = "UTF-8";
            $tcMailer->XMailer = 'tinyCampaign ' . CURRENT_RELEASE;
            $tcMailer->addCustomHeader('X-Campaign-Id', $data->xcampaignid);
            $tcMailer->addCustomHeader('X-List-Id', $data->xlistid);
            $tcMailer->addCustomHeader('X-Subscriber-Id', $data->xsubscriberid);
            $tcMailer->addCustomHeader('X-Subscriber-Email', $data->xsubscriberemail);
            $tcMailer->addCustomHeader('Feedback-ID', $data->feedbackid);
            app()->hook->{'do_action'}('custom_email_header', $tcMailer, $data);
            $tcMailer->From = _escape($data->femail);
            $tcMailer->FromName = _escape($data->fname);
            $tcMailer->Sender = (_escape(get_option('tc_bmh_username')) == '' ? $tcMailer->From : _escape(get_option('tc_bmh_username'))); // Return-Path
            $tcMailer->AddReplyTo(_escape($data->remail), _escape($data->rname)); // Reply-To
            $tcMailer->addAddress($to);
            $tcMailer->Subject = $subject;
            $tcMailer->Body = $html;
            $tcMailer->AltBody = $text;
            $tcMailer->isHTML(true);
            $tcMailer->Host = _escape($data->hname);
            $tcMailer->SMTPSecure = _escape($data->protocol);
            $tcMailer->Port = _escape($data->port);
            $tcMailer->SMTPAuth = true;
            $tcMailer->Username = _escape($data->uname);
            $tcMailer->Password = $password;
            if ($tcMailer->send()) {
                _tc_flash()->{'success'}(_t('Email Sent.'));
            }
            $tcMailer->ClearAddresses();
            $tcMailer->ClearAttachments();
        } catch (phpmailerException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (\app\src\Exception\Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }
    }
}

/**
 * Function used for multiple sending servers.
 * 
 * @since 2.0.1
 * @param object $data Object of info passed to PHPMailer.
 * @param string $to Email recipient.
 * @param string $subject Email subject.
 * @param string $html HTML version of the email message.
 * @param string $text Text version of the email message.
 * @param object $message Object of \app\src\tc_Queue().
 */
function tinyc_email($data, $to, $subject, $html, $text = '', $message = '')
{
    if (is_object($data)) {
        try {
            $node = Node::table('php_encryption')->find(1);
        } catch (NodeQException $e) {
            Cascade::getLogger('error')->{'error'}($e->getMessage());
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->{'error'}($e->getMessage());
        } catch (Exception $e) {
            Cascade::getLogger('error')->{'error'}($e->getMessage());
        }

        try {
            $password = Crypto::decrypt(_escape($data->password), Key::loadFromAsciiSafeString($node->key));
        } catch (Defuse\Crypto\Exception\BadFormatException $e) {
            Cascade::getLogger('error')->{'error'}($e->getMessage());
        } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
            Cascade::getLogger('error')->{'error'}($e->getMessage());
        } catch (Exception $e) {
            Cascade::getLogger('error')->{'error'}($e->getMessage());
        }

        $is_error = false;

        try {
            $tcMailer = _tc_phpmailer(true);
            $tcMailer->Mailer = "smtp";
            $tcMailer->ContentType = "text/html";
            $tcMailer->CharSet = "UTF-8";
            $tcMailer->XMailer = 'tinyCampaign ' . CURRENT_RELEASE;
            $tcMailer->addCustomHeader('X-Campaign-Id', $data->xcampaignid);
            $tcMailer->addCustomHeader('X-List-Id', $data->xlistid);
            $tcMailer->addCustomHeader('X-Subscriber-Id', $data->xsubscriberid);
            $tcMailer->addCustomHeader('X-Subscriber-Email', $data->xsubscriberemail);
            $tcMailer->addCustomHeader('Feedback-ID', $data->feedbackid);
            app()->hook->{'do_action'}('custom_email_header', $tcMailer, $data);
            $tcMailer->From = _escape($data->femail);
            $tcMailer->FromName = _escape($data->fname);
            $tcMailer->Sender = (_escape(get_option('tc_bmh_username')) == '' ? $tcMailer->From : _escape(get_option('tc_bmh_username'))); // Return-Path
            $tcMailer->AddReplyTo(_escape($data->remail), _escape($data->rname)); // Reply-To
            $tcMailer->addAddress($to);
            $tcMailer->Subject = $subject;
            $tcMailer->Body = $html;
            $tcMailer->AltBody = $text;
            $tcMailer->isHTML(true);
            $tcMailer->Host = _escape($data->hname);
            $tcMailer->SMTPSecure = _escape($data->protocol);
            $tcMailer->Port = _escape($data->port);
            $tcMailer->SMTPAuth = true;
            $tcMailer->Username = _escape($data->uname);
            $tcMailer->Password = $password;
            $tcMailer->send();
            $tcMailer->ClearAddresses();
            $tcMailer->ClearAttachments();
        } catch (phpmailerException $e) {
            $is_error = true;
            $error_text = $e->getMessage();
        } catch (\app\src\Exception\Exception $e) {
            $is_error = true;
            $error_text = $e->getMessage();
        }

        if ($is_error) {
            update_error_count($data);
        } else {
            app()->hook->{'do_action'}('mark_queued_record_sent', $message);
            update_send_count($data);
        }
    }
}

/**
 * Sends the campaign to the queue.
 * 
 * @since 2.0.2
 * @access private
 * @param object $cpgn Campaign data object.
 */
function send_campaign_to_queue($cpgn)
{
    try {
        /**
         * If it passes the above check, then instantiate the message queue.
         */
        $queue = new app\src\tc_Queue();
        try {
            /**
             * Retrieve list info based on the unique campaign id.
             */
            $campaign_list = app()->db->campaign_list()
                    ->select('campaign_list.lid')
                    ->where('campaign_list.cid = ?', _escape($cpgn->id))
                    ->find();
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (ORMException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }

        /**
         * Create a loop to see if how many lists this campaign should
         * be sent to and grab the list id.
         */
        foreach ($campaign_list as $c_list) {
            try {
                /**
                 * Get a list of subscribers that meet the criteria.
                 */
                /* $subscriber = app()->db->subscriber()
                  ->select('DISTINCT subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                  ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                  ->where('subscriber_list.lid = ?', _escape($c_list->lid))->_and_()
                  ->where('subscriber.allowed = "true"')->_and_()
                  ->where('(subscriber.spammer = "0" AND subscriber.exception = "0")')->_or_()
                  ->where('(subscriber.spammer = "1" AND subscriber.exception = "1")')->_or_()
                  ->where('(subscriber.spammer = "0" AND subscriber.exception = "1")')->_and_()
                  ->where('subscriber_list.confirmed = "1"')->_and_()
                  ->where('subscriber_list.unsubscribed = "0"')
                  ->groupBy('subscriber.email')
                  ->find(); */

                if (_escape($cpgn->ruleid) != null) {
                    $rule = get_rule_by_id(_escape($cpgn->ruleid));
                    $subscriber = app()->db->subscriber()
                            ->select('DISTINCT subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                            ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                            ->where('subscriber_list.lid = ?', _escape($c_list->lid))->_and_()
                            ->where('subscriber.allowed = "true"')->_and_()
                            ->where('subscriber.bounces < "3"')->_and_()
                            ->where('(subscriber.spammer = "0" OR subscriber.exception = "1")')->_and_()
                            ->where('subscriber_list.confirmed = "1"')->_and_()
                            ->where('subscriber_list.unsubscribed = "0"')->_and_()
                            ->where(_escape($rule->rule))
                            ->groupBy('subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                            ->find();
                } else {
                    $subscriber = app()->db->subscriber()
                            ->select('DISTINCT subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                            ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                            ->where('subscriber_list.lid = ?', _escape($c_list->lid))->_and_()
                            ->where('subscriber.allowed = "true"')->_and_()
                            ->where('subscriber.bounces < "3"')->_and_()
                            ->where('(subscriber.spammer = "0" OR subscriber.exception = "1")')->_and_()
                            ->where('subscriber_list.confirmed = "1"')->_and_()
                            ->where('subscriber_list.unsubscribed = "0"')
                            ->groupBy('subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                            ->find();
                }
            } catch (NotFoundException $e) {
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            } catch (Exception $e) {
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            } catch (ORMException $e) {
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            }

            /**
             * Loop through the above $subscriber query and add each
             * subscriber to the queue.
             */
            $i = 0;
            foreach ($subscriber as $sub) {
                $list = get_list_by('id', _escape($c_list->lid));
                $server = get_server_info(_escape($list->server));
                $throttle = _escape($server->throttle) * ++$i;
                $sendstart = _escape($cpgn->sendstart);
                /**
                 * Create new tc_QueueMessage object.
                 */
                $new_message = new app\src\tc_QueueMessage();
                $new_message->setListId(_escape($c_list->lid));
                $new_message->setMessageId($cpgn->id);
                $new_message->setSubscriberId(_escape($sub->id));
                $new_message->setToEmail(_escape($sub->email));
                $new_message->setToName(_escape($sub->fname) . ' ' . _escape($sub->lname));
                $new_message->setTimestampCreated(\Jenssegers\Date\Date::now());
                $new_message->setTimestampToSend(new \Jenssegers\Date\Date("$sendstart +$throttle seconds"));
                /**
                 * Add message to the queue.
                 */
                $queue->addMessage($new_message);
            }
        }

        try {
            $upd = app()->db->campaign();
            $upd->set([
                        'status' => 'processing',
                        'last_queued' => Jenssegers\Date\Date::now()
                    ])
                    ->where('id = ?', _escape($cpgn->id))
                    ->update();
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (ORMException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }


        tc_logger_activity_log_write('Update Record', 'Campaign Queued', _escape($cpgn->subject), get_userdata('uname'));
        _tc_flash()->{'success'}(_t('Campaign was successfully sent to the queue.'));
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

/**
 * Marks the sent message as complete and increments the
 * recipient field in the campaign table.
 * 
 * @since 2.0.4
 * @param object $message Object of \app\src\tc_Queue().
 */
function mark_queued_record_sent($message)
{
    try {
        $q = app()->db->campaign()
                ->where('id = ?', _escape((int) $message->getMessageId()))
                ->findOne();
        $q->set([
                    'recipients' => _escape((int) $q->recipients) + 1
                ])
                ->update();

        // remove message from the queue by updating is_sent value
        $queue = new \app\src\tc_Queue();
        $queue->setMessageIsSent($message);
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (ORMException $e) {
        Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function import_subscriber_to_list($lid, $data)
{
    $sub = get_subscriber_by('email', $data[2]);
    if (_escape($sub->id) <= 0) {
        $subscriber = app()->db->subscriber();
        $subscriber->insert([
            'fname' => $data[0],
            'lname' => $data[1],
            'email' => $data[2],
            'code' => _random_lib()->generateString(50, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
            'ip' => app()->req->server['REMOTE_ADDR'],
            'addedBy' => get_userdata('id'),
            'addDate' => Jenssegers\Date\Date::now()
        ]);
        $sid = $subscriber->lastInsertId();
        $slist = app()->db->subscriber_list();
        $slist->insert([
            'lid' => $lid,
            'sid' => $sid,
            'method' => 'import',
            'addDate' => Jenssegers\Date\Date::now(),
            'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
            'confirmed' => $data[3],
            'unsubscribed' => $data[4]
        ]);
    } else {
        $sub_list = is_subscribed_to_list($lid, _escape($sub->id));
        if (!$sub_list) {
            $slist = app()->db->subscriber_list();
            $slist->insert([
                'lid' => $lid,
                'sid' => _escape($sub->id),
                'method' => 'import',
                'addDate' => Jenssegers\Date\Date::now(),
                'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                'confirmed' => $data[3],
                'unsubscribed' => $data[4]
            ]);
        } else {
            //Do nothing
        }
    }
}

app()->hook->{'add_action'}('tc_dashboard_head', 'head_release_meta', 5);
app()->hook->{'add_action'}('tc_dashboard_head', 'tc_enqueue_style', 1);
app()->hook->{'add_action'}('release', 'foot_release', 5);
app()->hook->{'add_action'}('dashboard_top_widgets', 'dashboard_email_list_count', 5);
app()->hook->{'add_action'}('dashboard_top_widgets', 'dashboard_campaign_count', 5);
app()->hook->{'add_action'}('dashboard_top_widgets', 'dashboard_subscriber_count', 5);
app()->hook->{'add_action'}('dashboard_top_widgets', 'dashboard_email_sent_count', 5);
app()->hook->{'add_action'}('activated_plugin', 'tc_plugin_activate_message', 5, 1);
app()->hook->{'add_action'}('deactivated_plugin', 'tc_plugin_deactivate_message', 5, 1);
app()->hook->{'add_action'}('login_form_top', 'tc_login_form_show_message', 5);
app()->hook->{'add_action'}('tc_dashboard_footer', 'tc_enqueue_script', 5);
app()->hook->{'add_action'}('tcMailer_init', 'tc_smtp', 5, 1);
app()->hook->{'add_action'}('validation_check', 'tc_validation_check', 5, 1);
app()->hook->{'add_action'}('queue_campaign', 'send_campaign_to_queue', 5, 1);
app()->hook->{'add_action'}('tinyc_test_email_init', 'tinyc_test_email', 5, 6);
app()->hook->{'add_action'}('tinyc_email_init', 'tinyc_email', 5, 6);
app()->hook->{'add_action'}('custom_email_header', 'list_unsubscribe', 5, 2);
app()->hook->{'add_action'}('check_subscriber_email', 'mark_subscriber_as_spammer', 5, 1);
app()->hook->{'add_action'}('mark_queued_record_sent', 'mark_queued_record_sent', 5, 1);
app()->hook->{'add_action'}('import_subscriber', 'import_subscriber_to_list', 5, 2);
app()->hook->{'add_filter'}('tc_authenticate_user', 'tc_authenticate', 5, 3);
app()->hook->{'add_filter'}('tc_auth_cookie', 'tc_set_auth_cookie', 5, 2);
