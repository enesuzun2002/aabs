<?php
/*
 * Copyright (C) 2017-2018 Lukas Berger <mail@lukasberger.at>
 * Copyright (C) 2019 Enes Uzun <enesuzun200227@gmail.com>
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

function upload_to_drive($data) {

    $date = $data['date'];
	$output = $data['output'];
	$hashes = $data['hashes'];
	$uploaddir = $data['upload']['dir'];
	$uploadfile = $data['upload']['file'];

	echo "Set up gdrive application if you haven't already\n";
	
/* TODO
 * Find a way to upload the builds with different date to different folders
 */
echo "Uploading build...\n";
    xexec("mkdir {$date}");
    xexec("mv {$output} {$date}/{$uploadfile}");
	xexec("gdrive upload -r --name {$date} -p {$uploaddir} {$date}");
    xexec("rm -rf {$date}");

	foreach ($hashes as $hash => $file) {
		echo "Uploading {$hash}sum...\n";
        xexec("mkdir {$date}");
        xexec("mv {$file} {$date}/{$uploadfile}.{$hash}sum");
		xexec("gdrive upload -r --name {$date} -p {$uploaddir} {$date}");
        xexec("rm -rf {$date}");
	}
}
