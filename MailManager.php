<?php

define('MM_MAX_RECIPIENTS', 5);

/**
 * Class for abstracting the sending of email. Some local checks are performed
 * in order to catch obvious/simple errors which do not require database access,
 * followed by a connection to a web service which performs extra checks, including
 * authentication and rate limiting, before sending the email.
 */
class MailManager
{
  private $recipients = array();
  private $subject = '';
  private $body = '';
  
  private $dbhost;
  private $username;
  private $password;
  private $dbname;

  private $enableMail = false;

  public function __construct($dbhost, $username, $password, $dbname, $enableMail = false)
  {
    $this->dbhost = $dbhost;
	$this->username = $username;
	$this->password = $password;
	$this->dbname = $dbname;
	
	$this->enableMail = $enableMail;
  }
  
  public function setSubject($subject)
  {
    $this->subject = $subject;
  }
  
  public function getSubject()
  {
    return $this->subject;
  }
  
  public function setBody($body)
  {
    $this->body = $body;
  }
  
  public function getBody()
  {
    return $this->body;
  }
  
  public function addRecipient($emailAddress)
  {
    if (filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
    {
      $this->recipients[] = $emailAddress;
    }
    else
    {
      throw new Exception('Invalid recipient');
    }
  }

  public function getRecipients()
  {
    return $this->recipients;
  }
  
  /**
    * Check that all requirements have been met before attempting to send email.
    */
  public function validate()
  {
    // Basic checks:
    // 1. Do we have at least one recipient?
    if (count($this->recipients) < 1)
    {
      throw new Exception('No recipients specified');
    }
	
    // 2. Do we have a subject?
    if (empty($this->subject))
    {
      throw new Exception('No subject specified');
    }
	
    // 3. Do we have a message body?
    if (empty($this->body))
    {
      throw new Exception('No message body specified');
    }
	
	// 4. Simple check for maximum number of recipients
	if (count($this->recipients) > MM_MAX_RECIPIENTS)
	{
	  throw new Exception('Too many recipients, maximum allowed is: ' . MM_MAX_RECIPIENTS);
	}
  }
  
  private function sendIndividualEmail($emailAddress)
  {
    // TODO: Add call to web service
  }
  
  public function send()
  {
    $this->validate();
	
	foreach ($this->recipients as $recipient)
	{
	  $this->sendIndividualEmail($recipient);
	}
  }
}