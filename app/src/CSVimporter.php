<?php namespace TinyC;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Quick CSV Importer
 *  
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class CSVimporter
{

    public $csvFile;                    // Full path to csv file
    public $topLineFields = true;       // Is the first line of the CSV field names to use
    public $fieldSeparator = ",";       // Character that separates fields
    public $fieldEnclosure = "\"";      // The character that encloses each field
    public $fieldNamesArray;            // Array to hold the field names of the file content
    public $rowWalker = "";         // The array_walk function to call for each row's data array
    public $fileHandler;                // File handler for csv
    public $showDebug = false;          // Show debugging information
    protected $_app;

    /**
     * Constructor
     *
     * @file        string          The filename (with full path) of the CSV file to import
     * @params      array           OPTIONAL: Extra parameters to send to the constructor.  Array example:
     *                                  array(
     *                                      topLineFields => false,         // The first line of the CSV file does not contain field names
     *                                      fieldSeparator => ",",          // The CSV data fields are separated by a comma
     *                                      fieldEnclosure => "\"",         // Double quotes enclose each field containing commas
     *                                      rowWalker => "formatMyData"     / The name of the function to call to format each CSV line before import.
     *                                                                      // This function can be used to convert date strings to timestamps or modify
     *                                                                      // any other data before import.  This is the name of a function that you supply.
     *                                  )
     *                              Any or ALL of the above array values can be specified.  They will override the default values of the class
     *
     * @return      void            No value returned
     */
    public function __construct($name, $temp, $params = NULL, \Liten\Liten $liten = null)
    {
        $this->csvFile = BASE_PATH . 'app/tmp/uploads/';
        $this->csvFile = $this->csvFile . basename($name);
        $this->_app = !empty($liten) ? $liten : \Liten\Liten::getInstance();
        if (move_uploaded_file($temp, $this->csvFile)) {
            return true;
        } else {
            return false;
        }

        if (!is_file($this->csvFile))
            die("The CSV file (" . $this->csvFile . ") does not exist.");

        // See if additional parameters were passed in
        if (is_array($params)) {
            while (list($var, $val) = each($params)) {
                if (isset($val))
                    $this->$var = $val;
            }
        }
    }

    /**
     * Set the field names for the csv data
     *
     * @fields      array           If the first line of your CSV file does not contain the field names of the data, you can use this method
     *                              to create them.  Heres an example of a CSV file with 7 fields that we want to specify names for:
     *                                  array("Field 1", "Field 2", "Field 3", "Field 4", "Field 5", "Field 6", "Field 7")
     *                              The parameter is an array of string values that will be the field names of the table
     *
     * @return      void            No value returned
     */
    public function setFieldNames($fields)
    {
        $handle = fopen($this->csvFile, "r");
        while (($data = fgetcsv($handle, 1000, $this->fieldSeparator, $this->fieldEnclosure)) !== FALSE) {
            $fieldCount = count($data);
            break;
        }
        fclose($handle);

        // Make sure number of field names match
        if (count($fields) != $fieldCount)
            die("There are " . $fieldCount . " fields in the CSV file, you have supplied " . count($fields) . " field names.");
        $this->fieldNamesArray = $fields;
    }

    /**
     * Gets and array of the csv contents.  This method is mostly used by the class and really isn't needed to be called.
     *
     * @return      array           The array returned is a multidemensional array of the data in the csv file.  The data is returned in an array like this:
     *                                  Array
     *                                  (
     *                                      [0] => Array
     *                                          (
     *                                              [field1] => value1
     *                                              [field2] => value2
     *                                              [field3] => value3
     *                                          )
     *                                  
     *                                      [1] => Array
     *                                          (
     *                                              [field1] => value1
     *                                              [field2] => value2
     *                                              [field3] => value3
     *                                          )
     *                                      ...
     *                                  )
     *                              Each array key ([0], [1], etc.) represents a row of data from the csv file
     */
    public function getCSVarray()
    {
        // Make sure the field names array is set first
        if (!$this->topLineFields && count($this->fieldNamesArray) < 1)
            die("You must set the field names");

        $line = 1;
        $dataArray = [];
        $this->fileHandler = fopen($this->csvFile, "r");
        while (($data = fgetcsv($this->fileHandler, 1000, $this->fieldSeparator, $this->fieldEnclosure)) !== FALSE) {
            // Set first line as field names
            if ($line == 1 && $this->topLineFields)
                $this->fieldNamesArray = $data;
            else {
                $info = [];
                for ($i = 0; $i < count($data); $i++) {
                    $fieldArray = $this->fieldNamesArray;
                    $info[$fieldArray[$i]] = $this->formatFieldData($data[$i]);
                }

                // If there is a user defined array walk to format row data, do it here
                if (!empty($this->rowWalker))
                    array_walk($info, $this->rowWalker);

                $dataArray[] = $info;

                if (count($data) != count($this->fieldNamesArray)) {
                    array_pop($dataArray);
                    if ($this->showDebug)
                        echo "The following line was not included, because the number of fields did not match:<br /><b>" . implode($this->fieldSeparator, $data) . "</b><br /><br />";
                }
            }
            $line++;
        }
        //echo "<pre>"; print_r($dataArray); echo "</pre>";
        fclose($this->fileHandler);
        return $dataArray;
    }

    /**
     * Get array of sql insert statements to put the data into a table
     *
     * @sqlTable    string          String representing the name of the MySQL table in which to place the csv data.  The table must have fields
     *                              with the same field names as the imported data.
     *
     * @return      array           Array of sql queries in string form to insert the csv data
     */
    /* public function getSQLinsertsArray($sqlTable)
      {
      $data = $this->getCSVarray();
      $queries = [];
      $fieldsStr = "";
      while(list($k, $field) = each($data))
      {
      if(empty($fieldStr)) $fieldStr = implode(", ", array_keys($field));

      //$valueStr = "'".implode("', '", $field)."'";

      $placeholders = array_map(function($col) { return ":$col"; }, $field);
      $bind = array_combine($placeholders,$field);

      $queries[] = DB::inst()->query("INSERT INTO ".$sqlTable." (".$fieldStr.") VALUES (".implode(",", $placeholders).");",$bind);
      //error_log(var_export($queries,true));
      }
      return $queries;
      } */

    public function getSQLinsertsArray($sqlTable)
    {
        $data = $this->getCSVarray();
        $queries = [];
        $fieldsStr = "";
        while (list($k, $field) = each($data)) {
            if (empty($fieldStr))
                $fieldStr = implode(", ", array_keys($field));

            $valueStr = "'" . implode("', '", $field) . "'";

            $queries[] = $this->_app->db->query("INSERT INTO " . $sqlTable . " (" . $fieldStr . ") VALUES (" . $valueStr . ");");
            //echo $query."<br />";
        }
        return $queries;
    }

    /**
     * Run the sql queries to import info into db
     *
     * @sqlTable    string          String representing the name of the MySQL table in which to place the csv data.  The table must have fields
     *                              with the same field names as the imported data.
     *
     * @return      void            No value returned
     */
    public function queryInto($sqlTable)
    {
        $queries = $this->getSQLinsertsArray($sqlTable);
        while (list($k, $query) = each($queries)) {
            $q = $query;
        }

        $this->deleteFile();

        if ($q > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete uploaded file after import completion successfully  
     * @param    bool      
     */
    public function deleteFile()
    {
        if (file_exists($this->csvFile)) {
            if (unlink($this->csvFile)) {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Format the field to clean the data
     *
     * @field       string          CSV field data that needs to be cleaned
     *
     * @return      void            No value returned
     */
    public function formatFieldData($field)
    {
        $field = str_replace("\\" . $this->fieldEnclosure, $this->fieldEnclosure, $field);  // Remove escape chars
        return $field;
    }
}
