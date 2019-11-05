<?php
namespace app\models;

class Email {
    protected $to;
    protected $from;
    protected $headers = "";
    protected $subject;
    protected $cc;
    protected $layout;
    protected $data = [];
    public $content;

    public function __construct(string $to, string $from,string $subject = 'No Subject') {
        $this->to = $to;
        $this->from = $from;
        $this->subject = $subject;
    }

    public function setHeaders(string $headers) {
        $this->headers = $headers;
    }

    public function setCc(array $cc) {
        $this->cc = $cc;
    }

    public function setLayout(string $layout) {
        $this->layout = getLayoutPath().'/'.getConfig('layout')[$layout].'.tpl.php';
    }

    public function setData(array $data) {
        $this->data = $data;
    }

    public function generateContentWithLayout() {
        extract($this->data);
        ob_start();
        require $this->layout;
        $this->content = ob_get_contents();
        ob_end_clean();
    }

    function send(): bool {
        $this->headers .= 'From: '.$this->from."\r\n";
        $this->headers .= 'To: '.$this->from."\r\n";
        if (isset($this->cc)){
            $this->headers .= 'Cc: ';
            foreach ($this->cc as $cc)
                $this->headers .= $cc.' ';
            $this->headers .= "\r\n";
        }
        return mail($this->to, $this->subject, $this->content, $this->headers);
    }

    
}

