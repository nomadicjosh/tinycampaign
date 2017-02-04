# tinyCampaign

tinyCampaign is a simple system for creating and unlimited number of campaigns or newsletters.

## System Requirement

* Minimum of PHP version 5.6+
* Minimum of MySQL 5.6+
* Apache / Nginx


## Features

* Send using SMTP only
* Create unlimited number of email lists
* Email Queue
* Start Queue, Pause Queue, Resume Queue
* Send Test Email
* Import subscribers
* Export Subscribers
* Subscriber's Preference Page
* Public Archives
* Toggle Double opt-in
* Custom success and error url's
* Reports
* and more . . .


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

## Resources.

* User manual: [url] (https://codecanyon.7mediaws.org/article-categories/tinycampaign/).