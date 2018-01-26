<?php
/*
 * Copyright (C) 2017 Lukas Berger <mail@lukasberger.at>
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

function aabs_build($rom, $lunch_rom, $lunch_flavor, $targets_combinations) {
	// check if build is disabled
	if (AABS_SKIP_BUILD) {
		return;
	}

	// check if ROM is disabled
	if (AABS_ROMS != "*" && strpos(AABS_ROMS . ",", "{$rom},") === false) {
		return;
	}

	// check if ROM is supported and existing
	if (!validate_rom($rom)) {
		return;
	}

	$__assert  = "";
	$__assert .= 'ret=$?' . "\n";
	$__assert .= 'if [ ! $ret -eq 0 ]; then' . "\n";
	$__assert .= "\t" . 'exit $ret' . "\n";
	$__assert .= 'fi' . "\n";
	$__assert .= "\n";

	$command  = "";
	$command .= '#!/bin/bash' . "\n";
	$command .= "\n";
	$command .= 'cd "' . AABS_SOURCE_BASEDIR . "/{$rom}" . '"' . "\n" . $__assert;
	$command .= "\n";
	$command .= 'export RR_BUILDTYPE=Unofficial' . "\n";
	$command .= 'export WITH_ROOT_METHOD="magisk"' . "\n";
	$command .= "\n";
	$command .= 'source build/envsetup.sh' . "\n";
	$command .= "\n";

	foreach($targets_combinations as $device => $cmd) {
		// check if device is disabled
		if (AABS_DEVICES != "*" && strpos(AABS_DEVICES, "{$device} ") === false) {
			continue;
		}

		$clean   = isset($cmd['clean']) ? $cmd['clean'] : array( );
		$clobber = isset($cmd['clobber']) ? $cmd['clobber'] : false;
		$jobs	= isset($cmd['jobs']) ? $cmd['jobs'] : AABS_BUILD_JOBS;
		$match   = isset($cmd['match']) ? $cmd['match'] : "";
		$targets = isset($cmd['targets']) ? $cmd['targets'] : "bacon";

		foreach ($clean as $clean_file) {
			$clean_path = "out/target/product/{$device}/" . $clean_file;

			$command .= "\n";
			$command .= 'rm -fv ' . $clean_path . "\n" . $__assert;
			$command .= 'rm -fv ' . $clean_path . '*' . "\n" . $__assert;
			$command .= "\n";
		}

		$command .= 'lunch ' . $lunch_rom . '_' . $device . '-' . $lunch_flavor . "\n" . $__assert;

		if ($clobber) {
			$command .= 'make clobber -j' . $jobs . "\n" . $__assert;
		}

		// build.prop
		$command .= 'make ' . get_output_directory($rom, $device, AABS_SOURCE_BASEDIR . "/{$rom}") . '/system/build.prop -j' . $jobs . "\n" . $__assert;

		// build-targets
		$command .= 'make ' . $targets . ' -j' . $jobs . "\n" . $__assert;

		$command .= "\n";
	}

	xexec($command);
}