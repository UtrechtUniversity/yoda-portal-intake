<?php

$config['INTAKEPATH_StudyPrefix'] = "grp-intake-"; // will be extended with study name to form the exact path for intake.

// Files to be excluded separated by semicolon. Wildcards allowed,
// see http://php.net/manual/en/function.fnmatch.php
$config['file_exclusion_patterns'] = "._*;Thumbs.db;*DS_Store";

if (file_exists(dirname(__FILE__) . '/config_local.php'))
	include(    dirname(__FILE__) . '/config_local.php');

/* End of file config.php */
/* Location: ./application/config/config.php */
