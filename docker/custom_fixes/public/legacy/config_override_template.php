<?php
// $sugar_config['external_cache']['redis']['host'] = 'redis';
// $sugar_config['external_cache']['redis']['port'] = 6379;
// $sugar_config['external_cache']['redis']['persistent'] = true;
// $sugar_config['external_cache']['redis']['database'] = 0;
// $sugar_config['external_cache_disabled_redis'] = false; // set false and add Redis config to enable Redis cache
$sugar_config['external_cache_disabled'] = false; // default
$sugar_config['external_cache_class'] = 'SugarCacheAPC'; // default
$sugar_config['disable_persistent_connections'] = false;
$sugar_config['developerMode'] = false;
$sugar_config['http_referer']['list'][] = 'dev-epos-crm.360f.com';
// $sugar_config['http_referer']['actions'] = array('index', 'ListView', 'DetailView', 'EditView', 'oauth', 'authorize', 'Authenticate', 'Login', 'SupportPortal', 'Upgrade');