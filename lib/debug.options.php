<?php

if (!function_exists('e')){function e($e){var_dump($e);}}

// some xdebug settings to make my live easier
@ini_set('xdebug.var_display_max_depth', 8);
@ini_set('xdebug.trace_format', 0);
@ini_set('xdebug.collect_params', 2);
@ini_set('xdebug.collect_return', 1);
@ini_set('xdebug.var_display_max_data', 2000);
@ini_set('xdebug.var_display_max_children', 2000);
