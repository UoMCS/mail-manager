<?php

define('MM_MAX_RECIPIENTS', 5);
define('MM_WEB_SERVICE_URI', 'http://mailmanager.cs.manchester.ac.uk');

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
  
  private $client_error_codes = array(400, 401, 429);
  private $server_error_codes = array(500);

  /**
   * Create a new instance of the class (one per unique email).
   *
   * @param string $dbhost Database host.
   * @param string $username Database username.
   * @param string $password Database password.
   * @param string $dbname Database name.
   */
  public function __construct($dbhost, $username, $password, $dbname)
  {
    $this->dbhost = $dbhost;
    $this->username = $username;
    $this->password = $password;
    $this->dbname = $dbname;
  }
  
  /**
   * Set the email subject.
   *
   * @param string $subject Email subject.
   */
  public function set_subject($subject)
  {
    $this->subject = $subject;
  }
  
  /**
   * Get the email subject.
   *
   * @return string
   */
  public function get_subject()
  {
    return $this->subject;
  }
  
  /**
   * Set the email body.
   *
   * @param string $body Email body.
   */
  public function set_body($body)
  {
    $this->body = $body;
  }
  
  /**
   * Get the email body.
   *
   * @return string
   */
  public function get_body()
  {
    return $this->body;
  }
  
  /**
   * Add a recipient for this email.
   *
   * @param string $email_address Recipient email address.
   */
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

  /**
   * Get all recipients for this email.
   *
   * @return array
   */
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
  
  /**
   * Send an individual email.
   *
   * @param string $email_address Email address to send to.
   */
  private function send_individual_email($email_address)
  {
    $parameters = array();
    $parameters['username'] = $this->username;
    $parameters['password'] = $this->password;
    $parameters['host'] = $this->dbhost;
    $parameters['dbname'] = $this->dbname;
    
    $parameters['recipient'] = $email_address;
    $parameters['subject'] = $this->subject;
    $parameters['body'] = $this->body;
    
    $client = curl_init(MM_WEB_SERVICE_URI);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($client, CURLOPT_POST, TRUE);
    curl_setopt($client, CURLOPT_POSTFIELDS, $parameters);
    
    // Remember, with cURL there are two types of failure:
    // 1. Failure to make the HTTP request (e.g. host name does not exist).
    // 2. Failure status code (e.g. 4xx or 5xx).
    $response = curl_exec($client);
    
    if ($response === FALSE)
    {
      $error = curl_error($client);
      throw new Exception($error);
    }
    else
    {
      // We managed to make the request, now check what the status was
      $response_headers = curl_getinfo($client);
        
      if (in_array($response_headers['http_code'], $this->client_error_codes))
      {
        throw new Exception('Client error: ' . $response_headers['http_code']);
      }
      elseif (in_array($response_headers['http_code'], $this->server_error_codes))
      {
        throw new Exception('Server error: ' . $response_headers['http_code']);
      }
    }
    
    curl_close($client);
  }
  
  /**
   * Send the email
   */
  public function send()
  {
    $this->validate();
    
    foreach ($this->recipients as $recipient)
    {
      $this->send_individual_email($recipient);
    }
  }
}