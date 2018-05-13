<?php

/**
 * This file is used for lazy loading of the routers
 * and modules when called.
 */

if (strpos(getPathInfo('/api'), "/api") === 0)
{
    require($app->config('routers_dir') . 'api.router.php');
}

elseif (strpos(getPathInfo('/dashboard'), "/dashboard") === 0)
{
    _tc_dashboard_router();
}

elseif (strpos(getPathInfo('/media'), "/media") === 0)
{
    _tc_dashboard_router();
}

elseif (strpos(getPathInfo('/list'), "/list") === 0)
{
    require($app->config('routers_dir') . 'list.router.php');
}

elseif (strpos(getPathInfo('/campaign'), "/campaign") === 0)
{
    require($app->config('routers_dir') . 'campaign.router.php');
}

elseif (strpos(getPathInfo('/rss-campaign'), "/rss-campaign") === 0)
{
    require($app->config('routers_dir') . 'campaign.router.php');
}

elseif (strpos(getPathInfo('/rlde'), "/rlde") === 0)
{
    require($app->config('routers_dir') . 'campaign.router.php');
}

elseif (strpos(getPathInfo('/cron'), "/cron") === 0)
{
    require($app->config('routers_dir') . 'cron.router.php');
}

elseif (strpos(getPathInfo('/plugins'), "/plugins") === 0)
{
    require($app->config('routers_dir') . 'plugins.router.php');
}

elseif (strpos(getPathInfo('/setting'), "/setting") === 0)
{
    require($app->config('routers_dir') . 'setting.router.php');
}

elseif (strpos(getPathInfo('/user'), "/user") === 0)
{
    require($app->config('routers_dir') . 'user.router.php');
}

elseif (strpos(getPathInfo('/subscriber'), "/subscriber") === 0)
{
    require($app->config('routers_dir') . 'subscriber.router.php');
}

elseif (strpos(getPathInfo('/error'), "/error") === 0)
{
    _tc_error_router();
}

elseif (strpos(getPathInfo('/audit-trail'), "/audit-trail") === 0)
{
    _tc_error_router();
}

else {
    
    require($app->config('routers_dir') . 'index.router.php');
    // default routes
}

