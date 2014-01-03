<?php

date_default_timezone_set('Europe/London');

require_once 'config.inc.php';
require_once 'MailManager.php';

try
{
  $mm = new MailManager($database_host, $database_user, $database_pass, $database_name);

  $mm->set_subject('Test Email');
  $mm->add_recipient('paul.waring@manchester.ac.uk');
  $mm->set_body('Test Body');

  $mm->send();
}
catch (Exception $e)
{
  print $e->getMessage() . "\n";
}