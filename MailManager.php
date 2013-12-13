class MailManager
{
  private $dbhost = 'dbhost.cs.manchester.ac.uk';
  private $connection = null;
  private $recipients = array();
  private $subject = '';
  
  private $permittedDomains = array('manchester.ac.uk');

  public function __construct($username, $password, $dbname)
  {
    $this->connection = new mysqli($this->dbhost, $username, $password, $dbname);
	
	if ($connection->connect_error)
	{
	  throw new Exception('Could not connect to database');
	}
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
}