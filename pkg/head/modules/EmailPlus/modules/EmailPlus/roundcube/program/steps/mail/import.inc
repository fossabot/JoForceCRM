<?php

/**
 +-----------------------------------------------------------------------+
 | program/steps/mail/import.inc                                         |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save the uploaded file(s) as messages to the current IMAP folder    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

// clear all stored output properties (like scripts and env vars)
$OUTPUT->reset();

if (is_array($_FILES['_file'])) {
    $imported = 0;

    foreach ((array)$_FILES['_file']['tmp_name'] as $i => $filepath) {
        // Process uploaded file if there is no error
        $err = $_FILES['_file']['error'][$i];

        if (!$err) {
            // check file content type first
            $ctype = rcube_mime::file_content_type($filepath, $_FILES['_file']['name'][$i], $_FILES['_file']['type'][$i]);
            list($mtype_primary, $mtype_secondary) = explode('/', $ctype);

            if (in_array($ctype, array('application/zip', 'application/x-zip'))) {
                $filepath = rcmail_zip_extract($filepath);
                if (empty($filepath)) {
                    continue;
                }
            }
            else if (!in_array($mtype_primary, array('text', 'message'))) {
                continue;
            }

            foreach ((array) $filepath as $file) {
                // read the first few lines to detect header-like structure
                $fp = fopen($file, 'r');
                do {
                    $line = fgets($fp);
                }
                while ($line !== false && trim($line) == '');

                if (!preg_match('/^From .+/', $line) && !preg_match('/^[a-z-_]+:\s+.+/i', $line)) {
                    continue;
                }

                $message = $lastline = '';
                fseek($fp, 0);
                while (($line = fgets($fp)) !== false) {
                    // importing mbox file, split by From - lines
                    if ($lastline === '' && strncmp($line, 'From ', 5) === 0 && strlen($line) > 5) {
                        if (!empty($message)) {
                            // unquote ">From " lines in message body
                            $message = preg_replace('/\n>([>]*)From /', "\n\\1From ", $message);
                            if ($RCMAIL->storage->save_message(null, rtrim($message))) {
                                $imported++;
                            }
                            else {
                                rcube::raise_error("Failed to import message to " . $RCMAIL->storage->get_folder(), false, true);
                            }
                            $message = '';
                        }
                        continue;
                    }

                    $message .= $line;
                    $lastline = rtrim($line);
                }

                if (!empty($message) && $RCMAIL->storage->save_message(null, rtrim($message))) {
                    $imported++;
                }

                // remove temp files extracted from zip
                if (is_array($filepath)) {
                    unlink($file);
                }
            }
        }

        if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
            $size = $RCMAIL->show_bytes(parse_bytes(ini_get('upload_max_filesize')));
            $msg  = $RCMAIL->gettext(array('name' => 'filesizeerror', 'vars' => array('size' => $size)));
        }
        else if ($err) {
            $OUTPUT->show_message('fileuploaderror', 'error');
        }
    }  // end foreach

    if ($imported) {
        $OUTPUT->show_message($RCMAIL->gettext(array('name' => 'importmessagesuccess', 'nr' => $imported, 'vars' => array('nr' => $imported))), 'confirmation');
        $OUTPUT->command('command', 'list');
    }
    else {
        $OUTPUT->show_message('importmessageerror', 'error');
    }
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // if filesize exceeds post_max_size then $_FILES array is empty,
    // show filesizeerror instead of fileuploaderror
    if ($maxsize = ini_get('post_max_size'))
        $msg = $RCMAIL->gettext(array('name' => 'filesizeerror', 'vars' => array('size' => $RCMAIL->show_bytes(parse_bytes($maxsize)))));
    else
        $msg = $RCMAIL->gettext('fileuploaderror');

    $OUTPUT->command('display_message', $msg, 'error');
}

// send html page with JS calls as response
$OUTPUT->send('iframe');


function rcmail_zip_extract($path)
{
    if (!class_exists('ZipArchive', false)) {
        return;
    }

    $rcmail   = rcmail::get_instance();
    $temp_dir = $rcmail->config->get('temp_dir');
    $zip      = new ZipArchive;
    $files    = array();

    if ($zip->open($path)) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry    = $zip->getNameIndex($i);
            $tmpfname = tempnam($temp_dir, 'zipimport');

            if (copy("zip://$path#$entry", $tmpfname)) {
                $ctype = rcube_mime::file_content_type($tmpfname, $entry);
                list($mtype_primary, $mtype_secondary) = explode('/', $ctype);

                if (in_array($mtype_primary, array('text', 'message'))) {
                    $files[] = $tmpfname;
                }
                else {
                    unlink($tmpfname);
                }
            }
        }

        $zip->close();
    }

    return $files;
}
