<?php

define('MM_MAX_RECIPIENTS', 5);
define('MM_WEB_SERVICE_URI', 'http://mailmanager.cs.manchester.ac.uk');

require_once 'Zend/Http/Client.php';

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

  private $enable_email = false;

  public function __construct($dbhost, $username, $password, $dbname, $enable_email = false)
  {
    $this->dbhost = $dbhost;
	$this->username = $username;
	$this->password = $password;
	$this->dbname = $dbname;
	
	$this->enable_email = $enable_email;
  }
  
  public function set_subject($subject)
  {
    $this->subject = $subject;
  }
  
  public function get_subject()
  {
    return $this->subject;
  }
  
  public function set_body($body)
  {
    $this->body = $body;
  }
  
  public function get_body()
  {
    return $this->body;
  }
  
  public function add_recipient($email_address)
  {
    if (filter_var($email_address, FILTER_VALIDATE_EMAIL))
    {
      $this->recipients[] = $email_address;
    }
    else
    {
      throw new Exception('Invalid recipient');
    }
  }

  public function get_recipients()
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
  
  private function send_individual_email($email_address)
  {
    // TODO: Add call to web service
	$parameters = array();
	$parameters['username'] = $this->username;
	$parameters['password'] = $this->password;
	$parameters['host'] = $this->dbhost;
	$parameters['dbname'] = $this->dbname;
	
	$parameters['recipient'] = $email_address;
	$parameters['subject'] = $this->subject;
	$parameters['body'] = $this->body;
	
	$client = new Zend_Http_Client();
	$client->setUri(MM_WEB_SERVICE_URI);
	$client->setMethod(Zend_Http_Client::POST);
	$client->setParameterPost($parameters);
	
	if ($this->enable_email)
	{
	  $response = $client->request();
	}
  }
  
  public function send()
  {
    $this->validate();
	
	foreach ($this->recipients as $recipient)
	{
	  $this->send_individual_email($recipient);
	}
  }
}