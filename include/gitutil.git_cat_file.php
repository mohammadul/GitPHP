<?php
/*
 *  gitutil.git_cat_file.php
 *  gitphp: A PHP git repository browser
 *  Component: Git utility - cat file
 *
 *  Copyright (C) 2008 Christopher Han <xiphux@gmail.com>
 */

 include_once('defs.commands.php');

function git_cat_file($proj,$hash,$pipeto = NULL, $type = "blob")
{
	global $gitphp_conf;
	$cmd = "env GIT_DIR=" . $proj . " " . $gitphp_conf['gitbin'] . GIT_CAT_FILE . " " . $type . " " . $hash;
	if ($pipeto)
		$cmd .= " > " . $pipeto;
	return shell_exec($cmd);
}

?>