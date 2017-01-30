<?php namespace app\src\Exception;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * tinyCampaign Not Found Exception Class
 * 
 * This extends the default `LitenException` class to allow converting
 * file not found exceptions to and from `tc_Error` objects.
 * 
 * Unfortunately, because an `tc_Error` object may contain multiple messages and error
 * codes, only the first message for the first error code in the instance will be
 * accessible through the exception's methods.
 *  
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class NotFoundException extends BaseException
{
    public function __construct($message = 'Data requested cannot be found in the data source.', $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
