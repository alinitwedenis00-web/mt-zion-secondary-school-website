<?php
/** Set SITE_URL to the real HTTPS domain after domain registration. */
if (!defined('SITE_URL')) {
    $configuredUrl = getenv('SITE_URL') ?: 'https://REPLACE-WITH-YOUR-DOMAIN';
    define('SITE_URL', rtrim($configuredUrl, '/'));
}
