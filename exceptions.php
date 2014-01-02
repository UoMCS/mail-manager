<?php

date_default_timezone_set('Europe/London');

require_once 'config.inc.php';
require_once 'MailManager.php';

// Recipient not in list of permitted domains
try
{
  $mm = new MailManager($database_host, $database_user, $database_pass, $database_name);
  
  $mm->setSubject('Test');
  $mm->setBody('Body');
  
  $mm->addRecipient('test@example.org');
  
  $mm->send();
}
catch (Exception $e)
{
  print $e->getMessage() . "\n";
}

// Too many recipients
try
{
  $mm = new MailManager($database_host, $database_user, $database_pass, $database_name);
  
  $mm->setSubject('Test');
  $mm->setBody('Body');
  
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