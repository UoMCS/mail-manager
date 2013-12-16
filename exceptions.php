<?php

date_default_timezone_set('Europe/London');

require_once 'config.inc.php';
require_once 'MailManager.php';

try
{
  $mm = new MailManager($database_host, $database_user, $database_pass, $database_name);
  $mm->addRecipient('test@example.org');
}
catch (Exception $e)
{
  print $e->getMessage() . "\n";
}

try
{
  $mm = new MailManager($database_host, $database_user, $database_pass, $database_name);
  $mm->addRecipient('doesnotexist@manchester.ac.uk');
  $mm->addRecipient('doesnotexist@manchester.ac.uk');
  $mm->addRecipient('doesnotexist@manchester.ac.uk');
  $mm->addRecipient('doesnotexist@manchester.ac.uk');
  $mm->addRecipient('doesnotexist@manchester.ac.uk');
  $mm->addRecipient('doesnotexist@manchester.ac.uk');
  $mm->addRecipient('doesnotexist@manchester.ac.uk');
  
  $mm->send();
}
catch (Exception $e)
{
  print $e->getMessage() . "\n";
}