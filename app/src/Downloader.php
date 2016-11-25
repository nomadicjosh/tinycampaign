<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * File Downloader Class
 *  
 * PHP 5.4+
 *
 * eduTrac(tm) : Student Information System (http://www.7mediaws.org/)
 * @copyright (c) 2013 7 Media Web Solutions, LLC
 * 
 * @license     http://www.edutracerp.com/general/edutrac-erp-commercial-license/ Commercial License
 * @link        http://www.7mediaws.org/
 * @since       3.0.0
 * @package     eduTrac
 * @author      Joshua Parker <josh@7mediaws.org>
 */
class Downloader
{
    /*
      |---------------------------
      | Properties
      |---------------------------
     */

    private $download_hook = [];
    private $args = array(
        'download_path' => NULL,
        'file' => NULL,
        'extension_check' => TRUE,
        'referrer_check' => FALSE,
        'referrer' => NULL,
    );
    private $allowed_extensions = array(
        /* Archives */
        'zip' => 'application/zip',
        '7z' => 'application/octet-stream',
        /* Documents */
        'txt' => 'text/plain',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        /* Executables */
        'exe' => 'application/octet-stream',
        /* Images */
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        /* Audio */
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/x-wav',
        /* Video */
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo'
    );

    /*
      |---------------------------
      | Constructor
      |
      | @public
      | @param array $args
      | @param array $allowed_extensions
      |
      |---------------------------
     */

    public function __construct($args = [], $allowed_extensions = [])
    {

        $this->set_args($args);
        $this->set_allowed_extensions($allowed_extensions);
    }
    /*
      |---------------------------
      | Print variable in readable format
      |
      | @public
      | @param string|array|object $var
      |
      |---------------------------
     */

    public function dl_print($var)
    {

        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }
    /*
      |---------------------------
      | Update default arguments
      | It will update default array of class i.e $args
      |
      | @private
      | @param array $args - input arguments
      | @param array $defatuls - default arguments
      | @return array
      |
      |---------------------------
     */

    private function dl_parse_args($args = [], $defaults = [])
    {
        return array_merge($defaults, $args);
    }
    /*
      |---------------------------
      | Get extension and name of file
      |
      | @private
      | @param string $file_name
      | @return array - having file_name and file_ext
      |
      |---------------------------
     */

    private function dl_extension($file_name)
    {
        $temp = [];
        $temp['file_name'] = strtolower(substr($file_name, 0, strripos($file_name, '.')));
        $temp['file_extension'] = strtolower(substr($file_name, strripos($file_name, '.') + 1));
        return $temp;
    }
    /*
      |---------------------------
      | Set default arguments
      | It will set default array of class i.e $args
      |
      | @private
      | @param array $args
      | @return 0
      |
      |---------------------------
     */

    private function set_args($args = [])
    {

        $defaults = $this->get_args();
        $args = $this->dl_parse_args($args, $defaults);
        $this->args = $args;
    }
    /*
      |---------------------------
      | Get default arguments
      | It will get default array of class i.e $args
      |
      | @public
      | @return array
      |
      |---------------------------
     */

    public function get_args()
    {
        return $this->args;
    }
    /*
      |---------------------------
      | Set default allowed extensions
      | It will set default array of class i.e $allowed_extensions
      |
      | @private
      | @param array $allowed_extensions
      | @return 0
      |
      |---------------------------
     */

    private function set_allowed_extensions($allowed_extensions = [])
    {

        $defaults = $this->get_allowed_extensions();
        $allowed_extensions = array_unique($this->dl_parse_args($allowed_extensions, $defaults));
        $this->allowed_extensions = $allowed_extensions;
    }
    /*
      |---------------------------
      | Get default allowed extensions
      | It will get default array of class i.e $allowed_extensions
      |
      | @public
      | @return array
      |
      |---------------------------
     */

    public function get_allowed_extensions()
    {
        return $this->allowed_extensions;
    }
    /*
      |---------------------------
      | Set Mimi Type
      | It will set default array of class i.e $allowed_extensions
      |
      | @private
      | @param string $file_path
      ! @return string
      |
      |---------------------------
     */

    private function set_mime_type($file_path)
    {

        /* by Function - mime_content_type */
        if (function_exists('mime_content_type')) {
            $file_mime_type = mime_content_type($file_path);
        }

        /* by Function - mime_content_type */ else if (function_exists('finfo_file')) {

            $finfo = finfo_open(FILEINFO_MIME);
            $file_mime_type = finfo_file($finfo, $file_path);
            finfo_close($finfo);
        }

        /* Default - FALSE */ else {
            $file_mime_type = FALSE;
        }

        return $file_mime_type;
    }
    /*
      |---------------------------
      | Get Mimi Type
      | It will set default array of class i.e $allowed_extensions
      |
      | @public
      | @param string $file_path
      ! @return string
      |
      |---------------------------
     */

    public function get_mime_type($file_path)
    {
        return $this->set_mime_type($file_path);
    }
    /*
      |---------------------------
      | Pre Download Hook
      |
      | @private
      | @return 0
      |
      |---------------------------
     */

    private function set_download_hook()
    {

        /* Allowed Extensions */
        $allowed_extensions = $this->get_allowed_extensions();

        /* Arguments */
        $args = $this->get_args();

        /* Extract Arguments */
        extract($args);

        /* Directory Depth */
        $dir_depth = dirname($file);
        if (!empty($dir_depth) && $dir_depth != ".") {
            $download_path = $download_path . $dir_depth . "/";
        }

        /* File Name */
        $file = basename($file);

        /* File Path */
        $file_path = $download_path . $file;
        $this->download_hook['file_path'] = $file_path;

        /* File and File Path Validation */
        if (empty($file) || !file_exists($file_path)) {
            $this->download_hook['download'] = FALSE;
            $this->download_hook['message'] = "Invalid File or File Path.";
            return 0;
        }

        /* File Name and Extension */
        $nameext = $this->dl_extension($file);
        $file_name = $nameext['file_name'];
        $file_extension = $nameext['file_extension'];

        $this->download_hook['file'] = $file;
        $this->download_hook['file_name'] = $file_name;
        $this->download_hook['file_extension'] = $file_extension;

        /* Allowed Extension - Validation */
        if ($extension_check == TRUE && !array_key_exists($file_extension, $allowed_extensions)) {
            $this->download_hook['download'] = FALSE;
            $this->download_hook['message'] = "File is not allowed to download";
            return 0;
        }

        /* Referrer - Validation */
        if ($referrer_check == TRUE && !empty($referrer) && strpos(strtoupper($_SERVER['HTTP_REFERER']), strtoupper($referrer)) === FALSE) {
            $this->download_hook['download'] = FALSE;
            $this->download_hook['message'] = "Internal server error - Please contact system administrator";
            return 0;
        }

        /* File Size in Bytes */
        $file_size = filesize($file_path);
        $this->download_hook['file_size'] = $file_size;

        /* File Mime Type - Auto, Manual, Default */
        $file_mime_type = $this->get_mime_type($file_path);
        if (empty($file_mime_type)) {

            $file_mime_type = $allowed_extensions[$file_extension];
            if (empty($file_mime_type)) {
                $file_mime_type = "application/force-download";
            }
        }

        $this->download_hook['file_mime_type'] = $file_mime_type;

        $this->download_hook['download'] = TRUE;
        $this->download_hook['message'] = "File is ready to download";
        return 0;
    }
    /*
      |---------------------------
      | Download Hook
      | Allows you to do some action before download
      |
      | @public
      | @return array
      |
      |---------------------------
     */

    public function get_download_hook()
    {
        $this->set_download_hook();
        return $this->download_hook;
    }
    /*
      |---------------------------
      | Post Download Hook
      |
      | @private
      | @return array
      |
      |---------------------------
     */

    private function set_post_download_hook()
    {
        return $this->download_hook;
    }
    /*
      |---------------------------
      | Download
      | Start download stream
      |
      | @public
      | @return 0
      |
      |---------------------------
     */

    public function set_download()
    {

        /* Download Hook */
        $download_hook = $this->set_post_download_hook();

        /* Extract */
        extract($download_hook);

        /* Recheck */
        if ($download_hook['download'] != TRUE) {
            echo "File is not allowed to download";
            return 0;
        }

        /* Execution Time Unlimited */
        set_time_limit(0);

        /*
          |----------------
          | Header
          | Forcing a download using readfile()
          |----------------
         */

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file_mime_type);
        header('Content-Disposition: attachment; filename=' . $file);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $file_size);
        ob_clean();
        flush();
        readfile($file_path);
        exit;
    }
    /*
      |---------------------------
      | Download
      | Start download stream
      |
      | @public
      | @return array
      |
      |---------------------------
     */

    public function get_download()
    {
        $this->set_download();
        exit;
    }
    /*
      |---------------------------
      | Destructor
      |---------------------------
     */

    public function __destruct()
    {
        
    }
}
