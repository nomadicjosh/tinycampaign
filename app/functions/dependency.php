<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * tinyCampaign Dependency Injection, Wrappers, etc.
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Call the application global scope.
 * 
 * @since 2.0.6
 * @return object
 */
function app()
{
    $app = \Liten\Liten::getInstance();
    return $app;
}

tc_load_file(BASE_PATH . 'vendor/autoload.php');

app()->inst->singleton('hook', function () {
    return new \TinyC\Hooks();
});

app()->inst->singleton('asset', function () {
    $options = [
        'public_dir' => remove_trailing_slash(BASE_PATH),
        'css_dir' => 'static/assets/css',
        'js_dir' => 'static/assets/js',
        'pipeline' => false,
        'pipeline_dir' => 'static/assets/min'
    ];
    return new \TinyC\tc_Assets($options);
});

/**
 * Wrapper function for the core PHP function: trigger_error.
 *
 * This function makes the error a little more understandable for the
 * end user to track down the issue.
 *
 * @since 2.0.0
 * @param string $message
 *            Custom message to print.
 * @param string $level
 *            Predefined PHP error constant.
 */
function _trigger_error($message, $level = E_USER_NOTICE)
{
    $debug = debug_backtrace();
    $caller = next($debug);
    echo '<div class="alerts alerts-error center">';
    trigger_error($message . ' used <strong>' . $caller['function'] . '()</strong> called from <strong>' . $caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong>' . "\n<br />error handler", $level);
    echo '</div>';
}

/**
 * Wrapper function for Hooks::maybe_serialize() method and
 * serializes data if needed.
 *
 * @see Hooks::maybe_serialize()
 *
 * @since 6.0.03
 * @param string|array|object $data
 *            Data to be serialized.
 * @return mixed
 */
function maybe_serialize($data)
{
    return app()->hook->maybe_serialize($data);
}

/**
 * Wrapper function for Hooks::is_serialized() method and
 * checks value to find if it was serialized.
 *
 * @see Hooks::is_serialized()
 *
 * @since 6.0.03
 * @param string $data
 *            Value to check if serialized.
 * @return bool False if not serialized or true if serialized.
 */
function is_serialized($data)
{
    return app()->hook->is_serialized($data);
}

/**
 * Wrapper function for Hooks::maybe_unserialize() method and
 * unserializes value if it is serialized.
 *
 * @see Hooks::maybe_unserialized()
 *
 * @since 6.0.03
 * @param string $original
 *            Maybe unserialized original, if is needed.
 * @return mixed Any type of serialized data.
 */
function maybe_unserialize($original)
{
    return app()->hook->maybe_unserialize($original);
}

/**
 * Returns false.
 *
 * Apply to filters to return false.
 *
 * @since 2.0.0
 * @return bool False
 */
function __return_false()
{
    return false;
}

/**
 * Returns true.
 *
 * Apply to filters to return true.
 *
 * @since 2.0.0
 * @return bool True
 */
function __return_true()
{
    return true;
}

/**
 * Returns null.
 *
 * Apply to filters to return null.
 *
 * @since 2.0.0
 * @return bool NULL
 */
function __return_null()
{
    return null;
}

/**
 * Wrapper function for Plugin::plugin_basename() method and
 * extracts the file name of a specific plugin.
 *
 * @see Plugin::plugin_basename()
 *
 * @since 2.0.0
 * @param string $filename
 *            Plugin's file name.
 */
function plugin_basename($filename)
{
    return \TinyC\Plugin::inst()->plugin_basename($filename);
}

/**
 * Wrapper function for Plugin::register_activation_hook() method.
 * When a plugin
 * is activated, the action `activate_pluginname` hook is called. `pluginname`
 * is replaced by the actually file name of the plugin being activated. So if the
 * plugin is located at 'app/plugin/sample/sample.plugin.php', then the hook will
 * call 'activate_sample.plugin.php'.
 *
 * @see Plugin::register_activation_hook()
 *
 * @since 2.0.0
 * @param string $filename
 *            Plugin's filename.
 * @param string $function
 *            The function that should be triggered by the hook.
 */
function register_activation_hook($filename, $function)
{
    return \TinyC\Plugin::inst()->register_activation_hook($filename, $function);
}

/**
 * Wrapper function for Plugin::register_deactivation_hook() method.
 * When a plugin
 * is deactivated, the action `deactivate_pluginname` hook is called. `pluginname`
 * is replaced by the actually file name of the plugin being deactivated. So if the
 * plugin is located at 'app/plugin/sample/sample.plugin.php', then the hook will
 * call 'deactivate_sample.plugin.php'.
 *
 * @see Plugin::register_deactivation_hook()
 *
 * @since 2.0.0
 * @param string $filename
 *            Plugin's filename.
 * @param string $function
 *            The function that should be triggered by the hook.
 */
function register_deactivation_hook($filename, $function)
{
    return \TinyC\Plugin::inst()->register_deactivation_hook($filename, $function);
}

/**
 * Wrapper function for Plugin::plugin_dir_path() method.
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @see Plugin::plugin_dir_path()
 *
 * @since 2.0.0
 * @param string $filename
 *            The filename of the plugin (__FILE__).
 * @return string The filesystem path of the directory that contains the plugin.
 */
function plugin_dir_path($filename)
{
    return \TinyC\Plugin::inst()->plugin_dir_path($filename);
}

/**
 * Special function for file includes.
 *
 * @since 2.0.0
 * @param string $file
 *            File which should be included/required.
 * @param bool $once
 *            File should be included/required once. Default true.
 * @param bool|Closure $show_errors
 *            If true error will be processed, if Closure - only Closure will be called. Default true.
 * @return mixed
 */
function tc_load_file($file, $once = true, $show_errors = true)
{
    if (file_exists($file)) {
        if ($once) {
            return require_once $file;
        } else {
            return require $file;
        }
    } elseif (is_bool($show_errors) && $show_errors) {
        _trigger_error(sprintf(_t('Invalid file name: <strong>%s</strong> does not exist. <br />'), $file));
    } elseif ($show_errors instanceof \Closure) {
        return (bool) $show_errors();
    }
    return false;
}

/**
 * Removes directory recursively along with any files.
 *
 * @since 2.0.0
 * @param string $dir
 *            Directory that should be removed.
 */
function _rmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DS . $object)) {
                    _rmdir($dir . DS . $object);
                } else {
                    unlink($dir . DS . $object);
                }
            }
        }
        rmdir($dir);
    }
}

/**
 * Appends a trailing slash.
 *
 * Will remove trailing forward and backslashes if it exists already before adding
 * a trailing forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 2.0.0
 * @param string $string
 *            What to add the trailing slash to.
 * @return string String with trailing slash added.
 */
function add_trailing_slash($string)
{
    return remove_trailing_slash($string) . '/';
}

/**
 * Removes trailing forward slashes and backslashes if they exist.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 2.0.0
 * @param string $string
 *            What to remove the trailing slashes from.
 * @return string String without the trailing slashes.
 */
function remove_trailing_slash($string)
{
    return rtrim($string, '/\\');
}

app()->asset->registerStyleCollection('morris', ['morris/morris.css']);
app()->asset->registerStyleCollection('datepicker', ['datepicker/bootstrap-datepicker.js']);
app()->asset->registerStyleCollection('timepicker', ['timepicker/bootstrap-timepicker.min.css']);
app()->asset->registerStyleCollection('datatables', ['datatables/dataTables.bootstrap.css']);
app()->asset->registerStyleCollection('tabletools', ['datatables/extensions/TableTools/css/dataTables.tableTools.min.css']);
app()->asset->registerStyleCollection('iCheck', ['iCheck/all.css']);
app()->asset->registerStyleCollection('iCheck_blue', ['iCheck/minimal/blue.css']);
app()->asset->registerStyleCollection('select2', ['select2/select2.min.css']);
app()->asset->registerStyleCollection('datetime', ['bootstrap-datetimepicker/bootstrap-datetimepicker.min.css']);
app()->asset->registerStyleCollection('elfinder', ['elfinder/css/elfinder.min.css', 'elfinder/css/theme.css']);
app()->asset->registerStyleCollection('elfinder-moono', ['elfinder/moono/css/theme.css']);
app()->asset->registerStyleCollection('selectize', ['selectize/selectize.default.css']);
app()->asset->registerStyleCollection('querybuilder', ['//cdn.jsdelivr.net/npm/jQuery-QueryBuilder/dist/css/query-builder.default.min.css','//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css','//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/css/selectize.bootstrap3.min.css']);

app()->asset->registerScriptCollection('morris', ['morris/morris.min.js']);
app()->asset->registerScriptCollection('chartjs', ['chartjs/Chart.min.js']);
app()->asset->registerScriptCollection('datepicker', ['datepicker/bootstrap-datepicker.js']);
app()->asset->registerScriptCollection('timepicker', ['timepicker/bootstrap-timepicker.min.js']);
app()->asset->registerScriptCollection('datatables', ['datatables/jquery.dataTables.min.js', 'datatables/dataTables.bootstrap.min.js', 'pages/datatable.js']);
app()->asset->registerScriptCollection('tabletools', ['datatables/extensions/TableTools/js/dataTables.tableTools.min.js']);
app()->asset->registerScriptCollection('iCheck', ['iCheck/icheck.min.js', 'pages/iCheck.js']);
app()->asset->registerScriptCollection('select2', ['select2/select2.full.min.js', 'select2/select2.js', 'pages/select2.js']);
app()->asset->registerScriptCollection('moment.js', ['daterangepicker/moment.min.js']);
app()->asset->registerScriptCollection('datetime', ['bootstrap-datetimepicker/bootstrap-datetimepicker.min.js', 'pages/datetime.js']);
app()->asset->registerScriptCollection('elfinder', ['elfinder/js/elfinder.full.js', 'elfinder/js/tinymce.plugin.js']);
app()->asset->registerScriptCollection('highcharts', ['Highcharts/highcharts.js', 'Highcharts/modules/exporting.js']);
app()->asset->registerScriptCollection('highcharts-3d', ['Highcharts/highcharts.js', 'Highcharts/highcharts-3d.js', 'Highcharts/modules/exporting.js']);
app()->asset->registerScriptCollection('dashboard', ['pages/dashboard.js']);
app()->asset->registerScriptCollection('campaign-domains', ['pages/campaign-domains.js']);
app()->asset->registerScriptCollection('campaign-opened', ['pages/campaign-opened.js']);
app()->asset->registerScriptCollection('campaign-clicked', ['pages/campaign-clicked.js']);
require( APP_PATH . 'functions' . DS . 'global-function.php' );
require( APP_PATH . 'functions' . DS . 'db-function.php' );
require( APP_PATH . 'functions' . DS . 'notify-function.php' );
require( APP_PATH . 'functions' . DS . 'bounce-function.php' );
require( APP_PATH . 'functions' . DS . 'rules-function.php' );
require( APP_PATH . 'functions' . DS . 'list-function.php' );
require( APP_PATH . 'functions' . DS . 'nodeq-function.php' );
require( APP_PATH . 'functions' . DS . 'auth-function.php' );
require( APP_PATH . 'functions' . DS . 'cache-function.php' );
require( APP_PATH . 'functions' . DS . 'textdomain-function.php' );
require( APP_PATH . 'functions' . DS . 'core-function.php' );
require( APP_PATH . 'functions' . DS . 'logger-function.php' );
require( APP_PATH . 'functions' . DS . 'user-function.php' );
require( APP_PATH . 'functions' . DS . 'subscriber-function.php' );
require( APP_PATH . 'functions' . DS . 'parsecode-function.php' );
