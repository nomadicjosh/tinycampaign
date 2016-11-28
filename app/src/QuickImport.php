<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * tinyCampaign Quick CSV Import
 *  
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class QuickImport
{

    public $table_name; //where to import to
    public $file_name;  //where to import from
    public $use_csv_header; //use first line of file OR generated columns names
    public $line_separate_char; //character(s) to separate lines (usually \n )
    public $field_separate_char; //character to separate fields
    public $field_enclose_char; //character to enclose fields, which contain separator char into content
    public $field_escape_char;  //char to escape special symbols
    public $error; //error message
    public $arr_csv_columns; //array of columns
    public $arr_csv_columns_to_load; //array of columns
    public $table_exists; //flag: does table for import exist
    public $make_temporary; //flag: does table for import exist
    public $additional_create;
    public $rows_count; //how many rows has been imported
    public $encoding; //encoding table, used to parse the incoming file. Added in 1.5 version
    protected $_app;

    function __construct($name, $temp)
    {
        $this->_app = \Liten\Liten::getInstance();
        $this->file_name = APP_PATH . 'tmp/uploads/';
        $this->file_name = $this->file_name . basename($name);
        if (move_uploaded_file($temp, $this->file_name)) {
            return true;
        } else {
            return false;
        }

        //$this->file_name = $file_name;
        $this->arr_csv_columns = [];
        $this->use_csv_header = true;
        $this->line_separate_char = '\n';
        $this->field_separate_char = ",";
        $this->field_enclose_char = "\"";
        $this->field_escape_char = "\\";
        $this->table_exists = false;
    }

    function import($table_name = "")
    {
        $this->table_name = $table_name;
        if (empty($this->table_name))
            $this->table_name = "temp_" . date("d_m_Y_H_i_s");

        if (!$this->table_exists) {
            $this->create_import_table();
        }

        if (!in_array($this->line_separate_char, array('\n', '\r', '\r\n'))) {
            $this->line_separate_char = '\n';
        }

        if (empty($this->arr_csv_columns_to_load))
            $this->arr_csv_columns_to_load = $this->arr_csv_columns;

        $fields = array();
        foreach ($this->arr_csv_columns_to_load as $column) {
            $field = '@dummy';
            if (is_array($column)) {
                $field = '`' . $column['name'] . '`';
            } elseif ('' != trim($column)) {
                $field = '`' . $column . '`';
            }
            $fields[] = $field;
        }

        /* change start. Added in 1.5 version */
        if ("" != $this->encoding && "default" != $this->encoding)
            $this->set_encoding();
        /* change end */

        //if($this->table_exists)
        //{
        $sql = "LOAD DATA LOCAL INFILE '" . $this->file_name .
            "' IGNORE INTO TABLE `" . $this->table_name .
            "` FIELDS TERMINATED BY '" . $this->field_separate_char .
            "' OPTIONALLY ENCLOSED BY '" . $this->field_enclose_char .
            "' ESCAPED BY '" . $this->field_escape_char .
            "' LINES TERMINATED BY '" . $this->line_separate_char .
            "' " .
            ($this->use_csv_header ? " IGNORE 1 LINES " : "")
            . "(" . implode(",", $fields) . ")";
        $res = DB::inst()->query($sql);
        $this->deleteFile();
        if ($res > 0) {
            return true;
        } else {
            return false;
        }
        //}
    }

    /**
     * Delete uploaded file after import completion successfully  
     * @param    bool      
     */
    public function deleteFile()
    {
        if (file_exists($this->file_name)) {
            if (unlink($this->file_name)) {
                return true;
            }
        } else {
            return false;
        }
    }

    //returns array of CSV file columns
    function get_csv_header_fields()
    {
        $this->arr_csv_columns = array();
        $fpointer = fopen($this->file_name, "r");
        if ($fpointer) {
            $arr = fgetcsv($fpointer, 10 * 1024, $this->field_separate_char);
            if (is_array($arr) && !empty($arr)) {
                if ($this->use_csv_header) {
                    foreach ($arr as $val)
                    //if(''!=trim($val))
                        $this->arr_csv_columns[] = array('name' => $val, 'type' => 'TEXT');
                } else {
                    $i = 1;
                    foreach ($arr as $val) {
                        //if(''!=trim($val))
                        $this->arr_csv_columns[] = array('name' => 'column' . $i, 'type' => 'TEXT');
                        $i++;
                    }
                }
            }
            unset($arr);
            fclose($fpointer);
        } else
            $this->error = "file cannot be opened: " . ("" == $this->file_name ? "[empty]" : $this->file_name);
        return $this->arr_csv_columns;
    }

    function create_import_table()
    {
        $sql = "CREATE " . ($this->make_temporary ? 'TEMPORARY' : '') . " TABLE IF NOT EXISTS " . $this->table_name . " (";

        if (empty($this->arr_csv_columns))
            $this->get_csv_header_fields();

        if (!empty($this->arr_csv_columns)) {
            $arr = array();
            foreach ($this->arr_csv_columns as $i => $column)
                $arr[] = "`" . $column['name'] . "` " . $column['type'];
            if (!empty($this->additional_create))
                $arr[] = $this->additional_create;
            $sql .= implode(",", $arr);
            $sql .= ")";
            //new dBug($sql);
            $res = DB::inst()->query($sql);
            //$this->error = $this->fDB->fError;
            //$this->table_exists = empty($this->error);
        }
    }
    /* change start. Added in 1.5 version */

    //returns recordset with all encoding tables names, supported by your database
    function get_encodings()
    {
        $rez = [];
        $sql = "SHOW CHARACTER SET";
        $res = DB::inst()->query($sql);
        if ($res->num_rows > 0) {
            while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
                $rez[$row["Charset"]] = ("" != $row["Description"] ? $row["Description"] : $row["Charset"]); //some MySQL databases return empty Description field
            }
        }
        return $rez;
    }
    /* change start. Added in 1.5 version */

    //returns recordset with all encoding tables names, supported by your database
    function get_column($column_name, $whole_count, $page = 1, $limit = 0)
    {
        $arrColumns = [];
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS DISTINCT `%1\$s`
            FROM `%2\$s`
            WHERE `%1\$s` <> ''
            ORDER BY `%1\$s`
           %3\$s", $column_name, $this->table_name, ($limit > 0 ? 'LIMIT ' . ($page - 1) * $limit . ', ' . $limit : '')
        );
        $res = $this->_app->db->query($sql);
        //new dBug($sql);
        /* if( !empty($this->fDB->fError) )
          {
          $this->error = $this->fDB->fError;
          return $arrColumns;
          } */

        $arrColumns = $this->fDB->getColumn($column_name);
        //new dBug($arrColumns);
        $sql = "SELECT FOUND_ROWS()";
        $res = $this->_app->db->query($sql);
        $whole_count = $this->fDB->getField();
        return $arrColumns;
    }

    //defines the encoding of the server to parse to file
    function set_encoding($encoding = "")
    {
        if ("" == $encoding)
            $encoding = $this->encoding;
        $sql = "SET SESSION character_set_database = " . $encoding; //'character_set_database' MySQL server variable is [also] to parse file with rigth encoding
        $res = $this->_app->db->query($sql);
        //return mysql_error();
    }
    /* change end */
}
