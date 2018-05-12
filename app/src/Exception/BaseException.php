<?php namespace TinyC\Exception;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * tinyCampaign Exception Class
 * 
 * This extends the framework `LitenException` class to allow converting
 * exceptions to and from `tc_Error` objects.
 * 
 * Unfortunately, because an `tc_Error` object may contain multiple messages and error
 * codes, only the first message for the first error code in the instance will be
 * accessible through the exception's methods.
 *  
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class BaseException extends \Liten\Exception\LitenException {

	/**
	 * tinyCampaign handles string error codes.
	 * @var string
	 */
	protected $code;

	/**
	 * Error instance.
	 * @var \TinyC\tc_Error
	 */
	protected $tc_error;

	/**
	 * tinyCampaign exception constructor.
	 *
	 * The class constructor accepts either the framework `\Liten\Exception\LitenException` creation
	 * parameters or an `\TinyC\tc_Error` instance in place of the previous exception.
	 *
	 * If an `\TinyC\tc_Error` instance is given in this way, the `$message` and `$code`
	 * parameters are ignored in favour of the message and code provided by the
	 * `\TinyC\tc_Error` instance.
	 *
	 * Depending on whether an `\TinyC\tc_Error` instance was received, the instance is kept
	 * or a new one is created from the provided parameters.
	 *
	 * @param string               $message  Exception message (optional, defaults to empty).
	 * @param string               $code     Exception code (optional, defaults to empty).
	 * @param `\Liten\Exception\LitenException` | `\TinyC\tc_Error` $previous Previous exception or error (optional).
	 *
	 * @uses \TinyC\tc_Error
	 * @uses \TinyC\tc_Error::get_error_code()
	 * @uses \TinyC\tc_Error::get_error_message()
	 */
	public function __construct( $message = '', $code = '', $previous = null ) {
		$exception = $previous;
		$tc_error  = null;

		if ( $previous instanceof \TinyC\tc_Error ) {
			$code      = $previous->get_error_code();
			$message   = $previous->get_error_message( $code );
			$tc_error  = $previous;
			$exception = null;
		}

		parent::__construct( $message, null, $exception );

		$this->code     = $code;
		$this->tc_error = $tc_error;
	}

	/**
	 * Obtain the exception's `\TinyC\tc_Error` object.
	 * 
     * @since 2.0.0
	 * @return tc_Error tinyCampaign error.
	 */
	public function get_tc_error() {
		return $this->tc_error ? $this->tc_error : new \TinyC\tc_Error( $this->code, $this->message, $this );
	}

}
