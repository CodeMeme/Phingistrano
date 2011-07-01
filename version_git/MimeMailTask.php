<?php
 
require_once 'phing/Task.php';

/**
 *  Send MIME multipart e-mail message.  Loosely based on the MailTask provided by phing
 *
 *  <mimemail from="sender@example.org"
 *            to="user1@example.org"
 *            cc="user2@example.org,user3@example.org"
 *            bcc="restricteduser@example.org"
 *            subject="build complete"
 *            html="true"
 *            template="somefile.html" />
 */
class MimeMailTask extends Task
{
    /**
     * Indicate if message is html.  Defaults to false.
     * @var boolean
     */
    protected $html = false;
    
    /**
     * Message subject.  Defaults to '(No subject)'
     * @var string
     */
    protected $subject = null;
    
    /**
     * Message body.
     * @var string
     */
    protected $message = null;
    
    /**
     * Message as separate template filename.  Overrides $message.
     * @var string
     */
    protected $template = null;
    
    /**
     * Sender email address.  Required.
     * @var string
     */
    protected $from = null;
    
    /**
     * Direct recipient email address(es) as comma-delimited list.
     * @var string
     */
    protected $to = array();
    
    /**
     * CC'd recipient email address(es) as comma-delimited list.
     * @var string
     */
    protected $cc = array();
    
    /**
     * BCC'd recipient email address(es) as comma-delimited list.
     * @var string
     */
    protected $bcc = array();
    
    public function init()
    {
        require_once 'Mail.php';
        if (false == class_exists('Mail')) {
            throw new BuildException("This task depends on PEAR::Mail");
        }
        
        require_once 'Mail/mime.php';
        if (false == class_exists('Mail_mime')) {
            throw new BuildException("This task depends on PEAR::Mail_Mime");
        }
    }
    
    public function main()
    {
        $mime = new Mail_mime();
        
        // Add message as html or plain text
        if ($this->isHtml()) {
            $mime->setHTMLBody($this->getMessage());
        }
        else {
            $mime->setTXTBody($this->getMessage());
        }
        
        // Add recipients
        foreach ($this->getTo() as $recipient) {
            $mime->addTo($recipient);
        }
        foreach ($this->getCc() as $recipient) {
            $mime->addCc($recipient);
        }
        foreach ($this->getBcc() as $recipient) {
            $mime->addBcc($recipient);
        }
        
        // Add sender and subject
        $mime->setFrom($this->getFrom());
        $mime->setSubject($this->getSubject());
        
        // Send the damnedable thing!
        $body = $mime->get();
        $headers = $mime->headers();
        $this->log("Assembled headers: " . print_r($headers, true));
        $mail =& Mail::factory('mail');
        $status = $mail->send($this->getTo(), $headers, $body);
        
        // Handle the outcome of our send attempt
        if ($status === true) {
            $recipCount = count($this->getTo()) + count($this->getCc()) + count($this->getBcc());
            $this->log("Sent MIME mail to $recipCount recipients");
        }
        else {
            throw new BuildException('Failed to send MIME mail: ' . $status->getMessage());
        }
    }
    
    /**
     * Is message plain text or html
     */
    public function setHtml($flag)
    {
        $this->html = (bool) $flag;
    }
    
    public function getHtml()
    {
        return $this->html;
    }
    
    public function isHtml()
    {
        return $this->getHtml();
    }
    
    /**
     * Subject
     */
    public function setSubject($subject)
    {
        $this->subject = (string) $subject;
    }
    
    public function getSubject()
    {
        if (empty($this->subject)) {
            return '(No subject)';
        }
        return $this->subject;
    }
    
    /**
     * Message template (overrides message text)
     */
    public function setTemplate($filename)
    {
        if (!is_readable($filename)) {
            throw new BuildException("Template '$filename' does not exist or is not readable");
        }
        $this->template = $filename;
    }
    
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     * Message text
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
    }
    
    public function getMessage()
    {
        if(null !== $this->getTemplate()) {
            $content = file_get_contents( $this->getTemplate() );
            $this->setMessage( $this->project->replaceProperties($content) );
        }
        return $this->message;
    }
    
    /**
     * Supports the <mimemail>Message</mimemail> syntax.
     */
    public function addText($message)
    {
        $this->setMessage($message);
    }
    
    /**
     * Sender
     */
    public function setFrom($sender)
    {
        # note: filter_var requires PHP >= 5.2.0
        $this->from = filter_var($sender, FILTER_VALIDATE_EMAIL);
    }
    
    public function getFrom()
    {
        if (empty($this->from)) {
            throw new BuildException('Missing "from" attribute');
        }
        return $this->from;
    }

    /**
     * Direct recipient(s)
     */
    public function setTo($recipients)
    {
        foreach (explode(',', $recipients) as $recipient) {
            $filteredRecip = filter_var($recipient, FILTER_VALIDATE_EMAIL);
            if ($filteredRecip) {
                $this->to[] = $filteredRecip;
            }
            else {
                throw new BuildException('Bad recipient for "to" attribute: ' . $recipient);
            }
        }
    }
    
    public function getTo()
    {
        return $this->to;
    }
    
    /**
     * CC'd recipient(s)
     */
    public function setCc($recipients)
    {
        foreach (explode(',', $recipients) as $recipient) {
            $filteredRecip = filter_var($recipient, FILTER_VALIDATE_EMAIL);
            if ($filteredRecip) {
                $this->cc[] = $filteredRecip;
            }
            else {
                throw new BuildException('Bad recipient for "cc" attribute: ' . $recipient);
            }
        }
    }
    
    public function getCc()
    {
        return $this->cc;
    }
    
    /**
     * BCC'd recipient(s)
     */
    public function setBcc($recipients)
    {
        foreach (explode(',', $recipients) as $recipient) {
            $filteredRecip = filter_var($recipient, FILTER_VALIDATE_EMAIL);
            if ($filteredRecip) {
                $this->bcc[] = $filteredRecip;
            }
            else {
                throw new BuildException('Bad recipient for "bcc" attribute: ' . $recipient);
            }
        }
    }
    
    public function getBcc()
    {
        return $this->bcc;
    }
}
