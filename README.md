# tinyCampaign

tinyCampaign is a simple system for creating and unlimited number of campaigns or newsletters.

## System Requirement

* Minimum of PHP version 5.4
* Apache / Nginx


## Features


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

* User manual: [url] (http://demo.edutrac.net/).