<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Email Class
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class tc_Email
{

    public $mailer;
    public $app;

    public function __construct()
    {
        $this->mailer = _tc_phpmailer();
        $this->app = \Liten\Liten::getInstance();
    }

    /**
     * Borrowed from WordPress
     *
     * Send mail, similar to PHP's mail
     * A true return value does not automatically mean that the user received the
     * email successfully. It just only means that the method used was able to
     * process the request without any errors.
     *
     * @since 2.0.0
     * @param string $to
     *            Recipient's email address.
     * @param string $subject
     *            Subject of the email.
     * @param mixed $message
     *            The body of the email.
     * @param mixed $headers
     *            Email headers sent.
     * @param mixed $attachments
     *            Attachments to be sent with the email.
     * @return mixed
     */
    public function tc_mail($to, $subject, $message, $headers = '', $attachments = array())
    {
        $charset = 'UTF-8';

        /**
         * Filter the tc_mail() arguments.
         *
         * @since 2.0.0
         *       
         * @param array $args
         *            A compacted array of tc_mail() arguments, including the "to" email,
         *            subject, message, headers, and attachments values.
         */
        $atts = $this->app->hook->{'apply_filter'}('tc_mail', compact('to', 'subject', 'message', 'headers', 'attachments'));

        if (isset($atts['to'])) {
            $to = $atts['to'];
        }
        if (isset($atts['subject'])) {
            $subject = $atts['subject'];
        }
        if (isset($atts['message'])) {
            $message = $atts['message'];
        }
        if (isset($atts['headers'])) {
            $headers = $atts['headers'];
        }
        if (isset($atts['attachments'])) {
            $attachments = $atts['attachments'];
        }

        if (!is_array($attachments)) {
            $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
        }

        // Headers
        if (empty($headers)) {
            $headers = [];
        } else {
            if (!is_array($headers)) {
                // Explode the headers out, so this function can take both
                // string headers and an array of headers.
                $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
            } else {
                $tempheaders = $headers;
            }
            $headers = [];
            $cc = [];
            $bcc = [];
            // If it's actually got contents
            if (!empty($tempheaders)) {
                // Iterate through the raw headers
                foreach ((array) $tempheaders as $header) {
                    if (strpos($header, ':') === false) {
                        if (false !== stripos($header, 'boundary=')) {
                            $parts = preg_split('/boundary=/i', trim($header));
                            $boundary = trim(str_replace(array(
                                "'",
                                '"'
                                    ), '', $parts[1]));
                        }
                        continue;
                    }
                    // Explode them out
                    list ($name, $content) = explode(':', trim($header), 2);
                    // Cleanup crew
                    $name = trim($name);
                    $content = trim($content);
                    switch (strtolower($name)) {
                        // Mainly for legacy -- process a From: header if it's there
                        case 'from':
                            $bracket_pos = strpos($content, '<');
                            if ($bracket_pos !== false) {
                                // Text before the bracketed email is the "From" name.
                                if ($bracket_pos > 0) {
                                    $from_name = substr($content, 0, $bracket_pos - 1);
                                    $from_name = str_replace('"', '', $from_name);
                                    $from_name = trim($from_name);
                                }
                                $from_email = substr($content, $bracket_pos + 1);
                                $from_email = str_replace('>', '', $from_email);
                                $from_email = trim($from_email);
                                // Avoid setting an empty $from_email.
                            } elseif ('' !== trim($content)) {
                                $from_email = trim($content);
                            }
                            break;
                        case 'content-type':
                            if (strpos($content, ';') !== false) {
                                list ($type, $charset_content) = explode(';', $content);
                                $content_type = trim($type);
                                if (false !== stripos($charset_content, 'charset=')) {
                                    $charset = trim(str_replace(array(
                                        'charset=',
                                        '"'
                                            ), '', $charset_content));
                                } elseif (false !== stripos($charset_content, 'boundary=')) {
                                    $boundary = trim(str_replace(array(
                                        'BOUNDARY=',
                                        'boundary=',
                                        '"'
                                            ), '', $charset_content));
                                    $charset = '';
                                }
                                // Avoid setting an empty $content_type.
                            } elseif ('' !== trim($content)) {
                                $content_type = trim($content);
                            }
                            break;
                        case 'cc':
                            $cc = array_merge((array) $cc, explode(',', $content));
                            break;
                        case 'bcc':
                            $bcc = array_merge((array) $bcc, explode(',', $content));
                            break;
                        default:
                            // Add it to our grand headers array
                            $headers[trim($name)] = trim($content);
                            break;
                    }
                }
            }
        }

        // Empty out the values that may be set
        $this->mailer->ClearAllRecipients();
        $this->mailer->ClearAttachments();
        $this->mailer->ClearCustomHeaders();
        $this->mailer->ClearReplyTos();

        // From email and name
        // If we don't have a name from the input headers
        if (!isset($from_name)) {
            $from_name = 'tinyCampaign';
        }

        if (!isset($from_email)) {
            // Get the site domain and get rid of www.
            $sitename = strtolower($_SERVER['SERVER_NAME']);
            if (substr($sitename, 0, 4) == 'www.') {
                $sitename = substr($sitename, 4);
            }

            $from_email = 'tc@' . $sitename;
        }

        /**
         * Filter the email address to send from.
         *
         * @since 2.0.0
         *       
         * @param string $from_email
         *            Email address to send from.
         */
        $this->mailer->From = $this->app->hook->{'apply_filter'}('tc_mail_from', $from_email);

        /**
         * Filter the name to associate with the "from" email address.
         *
         * @since 2.0.0
         *       
         * @param string $from_name
         *            Name associated with the "from" email address.
         */
        $this->mailer->FromName = $this->app->hook->{'apply_filter'}('tc_mail_from_name', $from_name);

        // Set destination addresses
        if (!is_array($to)) {
            $to = explode(',', $to);
        }

        foreach ((array) $to as $recipient) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';
                if (preg_match('/(.*)<(.+)>/', $recipient, $matches)) {
                    if (count($matches) == 3) {
                        $recipient_name = $matches[1];
                        $recipient = $matches[2];
                    }
                }
                $this->mailer->AddAddress($recipient, $recipient_name);
            } catch (phpmailerException $e) {
                continue;
            }
        }

        // Set mail's subject and body
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $message;

        // Add any CC and BCC recipients
        if (!empty($cc)) {
            foreach ((array) $cc as $recipient) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    $recipient_name = '';
                    if (preg_match('/(.*)<(.+)>/', $recipient, $matches)) {
                        if (count($matches) == 3) {
                            $recipient_name = $matches[1];
                            $recipient = $matches[2];
                        }
                    }
                    $this->mailer->AddCc($recipient, $recipient_name);
                } catch (phpmailerException $e) {
                    continue;
                }
            }
        }

        if (!empty($bcc)) {
            foreach ((array) $bcc as $recipient) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    $recipient_name = '';
                    if (preg_match('/(.*)<(.+)>/', $recipient, $matches)) {
                        if (count($matches) == 3) {
                            $recipient_name = $matches[1];
                            $recipient = $matches[2];
                        }
                    }
                    $this->mailer->AddBcc($recipient, $recipient_name);
                } catch (phpmailerException $e) {
                    continue;
                }
            }
        }

        // Set to use PHP's mail()
        $this->mailer->IsMail();

        // Set Content-Type and charset
        // If we don't have a content-type from the input headers
        if (!isset($content_type)) {
            $content_type = 'text/plain';
        }

        /**
         * Filter the tc_mail() content type.
         *
         * @since 2.0.0
         *       
         * @param string $content_type
         *            Default tc_mail() content type.
         */
        $content_type = $this->app->hook->{'apply_filter'}('tc_mail_content_type', $content_type);

        $this->mailer->ContentType = $content_type;

        // Set whether it's plaintext, depending on $content_type
        if ('text/html' == $content_type) {
            $this->mailer->IsHTML(true);
        }

        // Set the content-type and charset

        /**
         * Filter the default tc_mail() charset.
         *
         * @since 2.0.0
         *       
         * @param string $charset
         *            Default email charset.
         */
        $this->mailer->CharSet = $this->app->hook->{'apply_filter'}('tc_mail_charset', $charset);

        // Set custom headers
        if (!empty($headers)) {
            foreach ((array) $headers as $name => $content) {
                $this->mailer->AddCustomHeader(sprintf('%1$s: %2$s', $name, $content));
            }

            if (false !== stripos($content_type, 'multipart') && !empty($boundary)) {
                $this->mailer->AddCustomHeader(sprintf("Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary));
            }
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                try {
                    $this->mailer->AddAttachment($attachment);
                } catch (phpmailerException $e) {
                    continue;
                }
            }
        }

        /**
         * Fires after PHPMailer is initialized.
         *
         * @since 2.0.0
         *       
         * @param PHPMailer $this->mailer
         *            The PHPMailer instance, passed by reference.
         */
        $this->app->hook->{'do_action_array'}('tcMailer_init', [
            &$this->mailer
        ]);

        // Send!
        try {
            return $this->mailer->Send();
        } catch (phpmailerException $e) {

            $mail_error_data = compact($to, $subject, $message, $headers, $attachments);
            /**
             * Fires after a phpmailerException is caught.
             *
             * @since 6.2.3
             *       
             * @param tc_Error $error
             *            A tc_Error object with the phpmailerException code, message, and an array
             *            containing the mail recipient, subject, message, headers, and attachments.
             */
            $this->app->hook->{'do_action'}('tc_mail_failed', new \app\src\tc_Error($e->getCode(), $e->getMessage(), $mail_error_data));
            return false;
        }

        return true;
    }

    /**
     * Email sent to new user when account is created.
     * 
     * @since 2.0.0
     *
     * @param int $user
     *            User object.
     * @param string $pass
     *            Plaintext password.
     * @return type
     */
    public function sendNewUserEmail($user, $pass)
    {
        $domain = get_domain_name();
        $site = _h(get_option('system_name'));

        $message = _t('Hi there,') . "<br />";
        $message .= sprintf(_t("<p>Welcome to %s! Here's how to log in: "), $site);
        $message .= get_base_url() . "</p>";
        $message .= sprintf(_t('Username: %s'), $user->uname) . "<br />";
        $message .= sprintf(_t('Password: %s'), $pass) . "<br />";
        $message .= sprintf(_t('<p>If you have any problems, please contact us at %s.'), get_option('system_email')) . "</p>";

        $message = process_email_html($message, _t("New Account"));
        $headers = "From: $site <auto-reply@$domain>\r\n";
        if (_h(get_option('tc_smtp_status')) == 0) {
            $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE."\r\n";
            $headers .= "MIME-Version: 1.0" . "\r\n";
        }

        $this->tc_mail($user->email, _t('New Account'), $message, $headers);
        return $this->app->hook->{'apply_filter'}('new_user_email', $message, $headers);
    }
}
