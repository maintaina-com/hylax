#!/usr/bin/php
<?php

// No need for auth.
@define('AUTH_HANDLER', true);

require_once dirname(__FILE__) . '/../lib/Application.php';
$hylax = new Hylax_Application(array('init' => true));

// Make sure no one runs this from the web.
if (!Horde_Cli::runningFromCLI()) {
    exit("Must be run from the command line\n");
}

/* Load the CLI environment - make sure there's no time limit, init some
 * variables, etc. */
$cli = &new Horde_Cli();
$cli->init();

/* Create the fax information array. Set fax_type to 0 for incoming. */
$info = array('fax_type' => 0,
              'fax_user' => '');

/* Get the arguments. The first argument is the filename from which the job ID
 * is obtained, in the format 'recvq/faxNNNNN.tif'. */
$args = Console_Getopt::readPHPArgv();
if (isset($args[1])) {
    $info['fax_id'] = $args[1];
}
if (isset($args[2])) {
    $file = $args[2];
    $info['job_id'] = (int)substr($file, 9, -4);
}

$fax_info = $cli->readStdin();
$fax_info = explode("\n", $fax_info);
foreach ($fax_info as $line) {
    $line = trim($line);
    if (preg_match('/Pages: (\d+)/', $line, $matches)) {
        $info['fax_pages'] = $matches[1];
    } elseif (preg_match('/Sender: (.+)/', $line, $matches)) {
        $info['fax_number'] = $matches[1];
    } elseif (preg_match('/Received: (\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $line, $d)) {
        $time = mktime($d[4], $d[5], $d[6], $d[2], $d[3], $d[1]);
        $info['fax_created'] = $time;
    }
}

$t = $hylax_storage->createFax($info, true);
var_dump($t);
