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
  private $rateLimitCutoff;
  private $rateLimitMaxEmails = 60;
  
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
    $this->rateLimitCutoff = date($this->mysqlDateTimeFormat, strtotime('-1 hour'));
	
	$this->createLogTable();
	
	$this->fromAddress = 'paul.waring@manchester.ac.uk';
	
	$additionalHeaders = $this->fromAddress;
	$additionalParameters = '-f' . $this->fromAddress;
  }
  
  private function getCurrentTime()
  {
    return date($this->mysqlDateTimeFormat);
  }

  private function createLogTable()
  {
    // First check if table exists
    $sql = 'SELECT id FROM mail_message_log';
    $result = $this->connection->query($sql);

    // Table does not exist, so create it
    if ($result === FALSE)
    {
      $schema = file_get_contents($this->log_schema_file);

      if (!empty($schema))
      {
        $result = $this->connection->query($schema);
      }
    }
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
      list($user, $domain) = explode('@', $emailAddress);
	  
      if (in_array($domain, $this->permittedDomains))
      {
        $this->recipients[] = $emailAddress;
      }
      else
      {
        throw new Exception('Recipient does not fall within list of permitted domains');
      }
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
    * Check that all requirements (including rate limiting) have been met before sending email.
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
	
	// 5. Rate limit total number of messages
	$emailsSent = $this->countEmailsSent();
	
	if ($emailsSent >= $this->rateLimitMaxEmails)
	{
	  throw new Exception('Rate limit exceeded');
	}
  }
  
  private function sendIndividualEmail($emailAddress)
  {
    $mailSent = false;
	
	if ($this->enableMail)
	{
      $mailSent = mail($emailAddress, $this->subject, $this->body, $this->additionalHeaders, $this->additionalParameters);
	}
	else
	{
	  $mailSent = true;
	}
  
    if ($mailSent)
	{
      $sql = 'INSERT INTO ' . $this->log_table . ' (recipient, subject, body, log_time) VALUES (?, ?, ?, ?)';
	  
	  $statement = $this->connection->prepare($sql);
	  
	  if ($statement !== FALSE)
	  {
	    $currentTime = $this->getCurrentTime();
	    $statement->bind_param('ssss', $emailAddress, $this->subject, $this->body, $currentTime);
	    $statement->execute();
	  }
	  else
	  {
	    throw new Exception('Could not prepare SQL query');
	  }
	  
	  print "Email sent to: $emailAddress\n";
	}
	else
	{
	  print "Email not sent to: $emailAddress\n";
	}
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