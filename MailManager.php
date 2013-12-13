class MailManager
{
  private $dbhost = 'dbhost.cs.manchester.ac.uk';
  private $connection = null;
  private $recipients = array();
  private $subject = '';
  private $body = '';
  
  private $permittedDomains = array('manchester.ac.uk');

  public function __construct($username, $password, $dbname)
  {
    $this->connection = new mysqli($this->dbhost, $username, $password, $dbname);
	
    if ($connection->connect_error)
    {
      throw new Exception('Could not connect to database');
    }
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
  
  /**
    * Check that all requirements have been met before sending email.
    */
  public function validate()
  {
    // Basic checks:
    // 1. Do we have at least one recipient?
    if (count($this->recipients()) < 1)
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
  }
  
  public function send()
  {
    $this->validate();
	
    // Debugging for the moment
    print 'Email sent';
  }
}