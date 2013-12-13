<?php

date_default_timezone_set('Europe/London');

require_once 'config.inc.php';
require_once 'MailManager.php';

$mm = new MailManager($database_host, $database_user, $database_pass, $database_name);

$mm->setSubject('Test Email');
$mm->addRecipient('paul.waring@manchester.ac.uk');
$mm->setBody('Test Body');

$mm->send();