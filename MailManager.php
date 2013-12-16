<?php

class MailManager
{
  private $connection = null;
  private $recipients = array();
  private $subject = '';
  private $body = '';

  private $log_schema_file = 'logging.sql';
  private $log_table = 'mail_manager_log';
  private $enableMail = false;
  
  private $permittedDomains = array('manchester.ac.uk');
  private $maxRecipientsOneMessage = 6;

  private $mysqlDateTimeFormat = 'Y-m-d H:i:s';
  
  private $fromAddress;
  
  private $additionalHeaders;
  private $additionalParameters;

  public function __construct($dbhost, $username, $password, $dbname, $enableMail = false)
  {
    $this->connection = new mysqli($dbhost, $username, $password, $dbname);
	
    if ($this->connection->connect_error)
    {
      throw new Exception('Could not connect to database');
    }
	
	$this->enableMail = $enableMail;
		
	$this->fromAddress = 'paul.waring@manchester.ac.uk';
	
	$additionalHeaders = $this->fromAddress;
	$additionalParameters = '-f' . $this->fromAddress;
  }
  
  private function getCurrentTime()
  {
    return date($this->mysqlDateTimeFormat);
  }

  private function countEmailsSent()
  {
    $sql = 'SELECT id FROM ' . $this->log_table . ' WHERE log_time < ?';
    $statement = $this->connection->prepare($sql);
    $statement->bind_param('s', $this->rateLimitCutoff);
    $statement->execute();
    $result = $statement->get_result();

    $emailsSent = $result->num_rows;

    $statement->close();

    return $emailsSent;
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

    // 4. Rate limit number of recipients
    if (count($this->recipients) > $this->maxRecipientsOneMessage)
    {
      throw new Exception('Too many recipients, maximum allowed: ' . $this->maxRecipientsOneMessage);
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