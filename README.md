# tinyCampaign

tinyCampaign is a simple and lightweight newsletter system built on a nice Bootstrap and the [Liten](//www.litenframework.com/) framework. You can send out simple and beautiful HTML emails with ease. You must use an SMTP account in order to send out emails. This is to help reduce spam as well as to better identify spammers abusing the system.

![tinyCampaign](https://tiny-campaign.s3.amazonaws.com/images/tinyc-image.png)

## Rewrite

### Apache

<pre>
RewriteEngine On
 
# Some hosts may require you to use the `RewriteBase` directive.
# If you need to use the `RewriteBase` directive, it should be the
# absolute physical path to the directory that contains this htaccess file.
#
# RewriteBase /
 
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
</pre>

### Nginx

#### Root Directory

<pre>
location / {
    try_files $uri /index.php$is_args$args;
}
</pre>

#### Subdirectory

<pre>
location /newsletter {
    try_files $uri /newsletter/index.php$is_args$args;
}
</pre>

Before downloading, make sure your server meets the following requirements:

*   Apache/Nginx
*   PHP 7.2+
*   MySQL 5.6+
*   PDO Driver
*   Access to shell/commandline
*   cURL Enabled

## Installation

Visit the [KnowledgeBase](https://tinyc.7mediaws.org/knowledge-base/installation/) for detailed installation instructions.

tinyCampaign Demo:
------------------

*   URL: [https://www.tcdemo.us/](//www.tcdemo.us/)
*   Username: tinyc
*   Password: tinyc1234

### Features

*   Unlimited subscribers and mailing lists.
*   Send personalized and customized emails
*   Email speed throttling
*   Custom list settings
*   Custom success and error url's
*   Automatic bounce handling
*   Regular and ajax subscriber forms to embed
*   Per campaign custom sender settings
*   Send campaigns now or later
*   Send to multiple lists at once
*   Html editor optimized for newsletter design.
*   Campaign personalization (body, subject, hyperlinks, etc.)
*   Easily upload images
*   Send html, text, or both
*   Templates manager
*   View archived campaigns via a web browser
*   Queue multiple campaigns
*   Preview campaigns
*   Send test campaign before queuing
*   Use multiple SMTP servers
*   Per user SMTP server(s)
*   Multiple user accounts
*   [emoji](//getemoji.com/) support

*   Per user statistics and reports
*   Per user email templates
*   Pause, resume, edit a campaign
*   Clicks/Views email campaign reports
*   Charts exportable to pdf/png
*   Email open reporting
*   Link click tracking
*   Distribution reports
*   [Campaign Tracking with Google Analytics](//tinyc.7mediaws.org/knowledge-base/track-campaign-with-google-analytics/)
*   Double opt-in verification/confirmation at list level (toggle)
*   Import subscribers from csv files
*   Export subscribers to csv files
*   Subscriber's Preference Page
*   Modify Subscription Details
*   One click unsubscribes
*   Backend email queue
*   Backend caching
*   System snapshot report
*   System error logs (database & file)
*   System audit trail
*   Hooks and plugin system
*   [Custom email headers](//tinyc.7mediaws.org/wiki/custom_email_header/)
*   List-Unsubscribe header
*   [Serve application over SSL](//tinyc.7mediaws.org/knowledge-base/secure-socket-layer-ssl/)
*   Real-time spam prevention

### Changelog

<pre>
    v2.1.0 (TBD)
    - [Enhance] Added ability to re-queue campaigns to be sent to those subscribed after the campaign was queued
    
    v2.0.6 (2018.05.13)
    - [Fix] Misc.
    - [Feature] Campaign segmentation with rules
    - [Feature] Added Media Library Screen
    - [Feature] Added FTP screen

    v2.0.5 (2018.05.09)
    - [Fix] Ternary to look at full header or full body to retrieve bounce info
    - [Fix] Error created when sending test email
    - [Fix] When deleting user from within email list, the list gets deleted instead
    - [Enhance] JSON Pretty Print Cache
    - [Enhance] Mark subscriber as unsubscribed when blacklisted
    - [Enhance] Added `last_queued` date field to `campaign` table
    - [Enhance] Caching subscriber list for faster response time
    - [Enhanced] Added Google's Feedback-ID to header
    - [Enhance] Subscriber tagging
    - [Enhance] Migrate campaign queue to database
    - [Feature] RSS campaigns
    
    
    v2.0.4 (2017-02-21)
    - [Enhance] Custom email headers (through action hook)
    - [Enhance] List-Unsubscribe header
    - [Enhance] Exception field added to `subscriber` table (helpful if user continues to be marked as spam, but should be sent emails anyway)
    - [Fix] Views were not updating correctly in the `campaign` table
    - [Enhance] Added opens per hour per day report
    - [Enhance] Added clicks per hour per day report
    - [Enhance] Added unsubscribed report
    - [Fix] `ReturnPath` property in PHPMailer has been deprecated and replaced with `Sender` for receiving bounced emails
    - [Enhance] Merge queues into one queue; save on server space
    - [Enhance] Added emoji support
    
    v2.0.3 (2017-02-14)
    - [Enhance] Pretty dates instead of Y-m-d H:i:s
    - [Enhance] UI updates
    - [Enhance] Added pretty labels to statuses and integers
    - [Enhance] Added `status` toggle to cronjob handlers
    - [Fix] `Active Subscribers` widget count on the dashboard
    - [Enhance] Email function for multiple servers is now attached to an action hook
    - [Fix] Added missing decrypt measure for bounce mail handler
    - [Enhance] Added spammer column to the `subscriber` table
    - [Enhance] Added spam tolerance to options table; needs to be adjusted on general settings screen
    - [Fix] issue with updating a role
    - [Fix] count on dashboard charts
    - [Fix] count for email sent widget
    - [Enhance] added new campaign placeholder (subject)
    - [Fix] updated dataTables id
    - [Enhance] updated tracking link (backwards compatible)
    - [Enhance] added custom email headers
    - [Enhance] added email domain report pie chart
    - [Enhance] added opened report by day pie chart
    - [Enhance] added clicked report by day pie chart
    - [Enhance] added subscribe method (import, subscribe, add) to `subscriber_list` table
    - [Enhance] added bounce node to catch extra info from bounces
    - [Enhance] added ajax subscribe form option
    
    v2.0.2 (2017.02.10)
    - [Enhance] Renamed column `unsubscribe` to `unsubscribed` in `subscriber_list` table
    - [Fix] Changed the way routers are loaded due to some server setups
    - [Enhance] Separated tinyCampaign logo from tracking code (made both filterable)
    - [Enhance] Now you can add two other fields to import (confirmed, unsubscribed)
    - [Enhance] Added archive list screen
    - [Enhance] Updated 404 route to use proper HTTP status in json format
    - [Enhance] When clicked, email queue process starts immediately instead of going into holding (connected to an action hook)
    - [Enhance] Added caching to a few other database functions
    - [Enhance] Added text tab to create a text version of campaigns
    - [Enhance] Toggle Notify option on email list to receive email every time someone subscribes
    - [Enhance] Revamped Username Changer plugin
    
    v2.0.1 (2017.02.07)
    - [Feature] Templates
    - [Feature] Multiple SMTP servers
    - [Enhance] Bootstrap update
    - [Enhance] Database additions/updates
    - [Feature] Click link tracking
    - [Enhance] Documentation updates
    - [Fix] issue with missing list id when Node Queue is created
    - [Fix] issue where campaign wasn't marked sent when complete
    - [Fix] Emails Sent / List dashboard widget
    - [Fix] mail throttle where send start is in the future
    
    v2.0.0 (2017.02.03)
    - [Dev] Revamp of the whole system.
    - [Feature] Backend queue called NodeQ
    - [Feature] Caching and ability to flush cache
    - [Feature] Campaign can be sent to multiple lists
    - [Feature] Bounce Email Handling
    - [Feature] Error Logs and System Snapshot Report
    - [Feature] Audit Trail
    
    v1.0.0 (2013.05.21)
    - First release
</pre>

### Documentation and Support

*   Check out the [support documentation](//tiny-campaign.s3.amazonaws.com/index.html)
*   For api help and code samples, check out the [knowledgebase](//tinyc.7mediaws.org/).