<?php

date_default_timezone_set('Europe/London');

require_once 'config.inc.php';
require_once 'MailManager.php';

// Recipient not in list of permitted domains
try
{
  $mm = new MailManager($database_host, $database_user, $database_pass, $database_name);
  
  $mm->set_subject('Test');
  $mm->set_body('Body');
  
  $mm->add_recipient('test@example.org');
  
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
  
  $mm->set_subject('Test');
  $mm->set_body('Body');
  
  $mm->add_recipient('doesnotexist@manchester.ac.uk');
  $mm->add_recipient('doesnotexist@manchester.ac.uk');
  $mm->add_recipient('doesnotexist@manchester.ac.uk');
  $mm->add_recipient('doesnotexist@manchester.ac.uk');
  $mm->add_recipient('doesnotexist@manchester.ac.uk');
  $mm->add_recipient('doesnotexist@manchester.ac.uk');
  
  $mm->send();
}
catch (Exception $e)
{
  print $e->getMessage() . "\n";
}