<?php
/*
 * Copyright (C) 2017-2018 Lukas Berger <mail@lukasberger.at>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

function xexec_internal($cmdline, $censoring = array( )) {
	$output = array( );
	$rc = 0;
	$dcmdline = $cmdline;
	$tempfile = "";
	$is_external = (strpos($cmdline, "\n") !== false);

	if ($is_external) {
		$tempfile = tempnam(sys_get_temp_dir(), "aabs-exec-");
		file_put_contents($tempfile, $cmdline);
		chmod($tempfile, 0777);
		$dcmdline = $cmdline = "/bin/bash -c {$tempfile}";
	} else {
		if (is_array($censoring)) {
			foreach($censoring as $censor) {
				$dcmdline = str_replace($censor, "***", $dcmdline);
			}
		}
	}

	echo "{$dcmdline}\n";
	system($cmdline, $rc);

	if ($is_external && $tempfile != "") {
		unlink($tempfile);
	}

	return $rc;
}
