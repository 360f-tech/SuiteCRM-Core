<?php
// $sugar_config['external_cache']['redis']['host'] = 'redis';
// $sugar_config['external_cache']['redis']['port'] = 6379;
// $sugar_config['external_cache']['redis']['persistent'] = true;
// $sugar_config['external_cache']['redis']['database'] = 0;
// $sugar_config['external_cache_disabled_redis'] = false;
$sugar_config['external_cache_disabled'] = false;
$sugar_config['external_cache_class'] = 'SugarCacheAPC';
$sugar_config['http_referer']['list'][] = 'dev-epos-crm.360f.com';
$sugar_config['http_referer']['actions'] = array('index', 'ListView', 'DetailView', 'EditView', 'oauth', 'authorize', 'Authenticate', 'Login', 'SupportPortal', 'Upgrade');