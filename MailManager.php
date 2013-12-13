<?php

class MailManager
{
  private $dbhost = 'dbhost.cs.manchester.ac.uk';
  private $connection = null;
  private $recipients = array();
  private $subject = '';
  private $body = '';

  private $log_schema_file = 'logging.sql';
  
  private $permittedDomains = array('manchester.ac.uk');
  private $maxRecipientsOneMessage = 6;

  private $rateLimitCutoff;
  private $rateLimitMaxEmails = 60;

  public function __construct($username, $password, $dbname)
  {
    $this->connection = new mysqli($this->dbhost, $username, $password, $dbname);
	
    if ($connection->connect_error)
    {
      throw new Exception('Could not connect to database');
    }

    $this->rateLimitCutoff = date('Y-m-d H:i:s', strtotime('-1 hour'));
  }

  private function createLogTable()
  {
    // First check if table exists
    $sql = "SELECT id FROM mail_message_log";
    $result = $this->connection->query($sql);

    // Table does not exist, so create it
    if (!$result)
    {
      $schema = file_get_contents($this->log_schema_file);

      if (!empty($schema))
      {
        $result = $this->connect->query($schema);
      }
    }
  }

  private function countEmailsSent()
  {
    $sql = 'SELECT id FROM main_manager_log WHERE log_time < ?';
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
    * Check that all requirements have been met before sending email.
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
    if (count($this->recipients) > $this-maxRecipientsOneMessage 
    {
      throw new Exception('Too many recipients, maximum allowed: ' . $this->maxRecipients);
    }
  }
  
  public function send()
  {
    $this->validate();
	
    // Debugging for the moment
    print 'Email sent';
  }
}