<?php

class Email
{
    
    private $vars = array();
    private $template = false;
    private $template_path;
    private $template_folder;
    
    private $cache	= array();
    
    private $subject;

    private $senderName;
    private $senderEmail;

    private $recipientEmail;
    private $recipientName = '';

    private $bccEmail;
    
    private $replyToEmail  = '';
    private $replyToName   = '';
    
    private $template_data;
    
    private $files = array();

    private $body = false;

    private $html = true;
    private $inline_image_dir = false;
    
    public $errors = '';

    public $use_twig = false;
    
    function __construct($template, $use_twig=false)
    {    
        $this->template = $template; 
        $this->use_twig = $use_twig;
        
        if ($template) {
            $this->set_template($template);
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $this->set('http_host', $_SERVER['HTTP_HOST']);    
        }

        $Conf = Conf::fetch();
        $this->senderEmail = $Conf->email['accounts'];
        $this->senderName  = $Conf->email['from_name'];
        $this->bccEmail    = $Conf->email['sysadmin'];
        
    }

    public function set_template($template)
    {
        $Conf = Conf::fetch();

        $this->template = $template; 
        
        $this->template_path   = $Conf->site . '/templates/emails/'.$template;
        $this->template_folder = $Conf->site . '/templates/emails/';

        Console::log('Using email template: '.$this->template_path, 'template');
    }

    public function body($str=false)
    {
        if ($str === false) {
            return $this->body;
        }
        
        $this->body = $str;
    }
    
    
    public function subject($str=false)
    {
        if ($str === false) {
            return $this->subject;
        }
        
        $this->subject = $str;
    }
    
    public function senderName($str=false)
    {
        if ($str === false) {
            return $this->senderName;
        }
        
        $this->senderName = $str;
    }
    
    public function senderEmail($str=false)
    {
        if ($str === false) {
            return $this->senderEmail;
        }
        
        $this->senderEmail = $str;
    }
    
    public function recipientEmail($str=false)
    {
        if ($str === false) {
            return $this->recipientEmail;
        }
        
        $this->recipientEmail = $str;
    }
    
    public function recipientName($str=false)
    {
        if ($str === false) {
            return $this->recipientName;
        }
        
        $this->recipientName = $str;
    }
    
    public function replyToEmail($str=false)
    {
        if ($str === false) {
            return $this->replyToEmail;
        }
        
        $this->replyToEmail = $str;
    }
    
    public function replyToName($str=false)
    {
        if ($str === false) {
            return $this->replyToName;
        }
        
        $this->replyToName = $str;
    }
    
    public function set($key, $str=false)
    {
        if ($str === false) {
            return $this->vars[$key];
        }
        
        $this->vars[$key] = $str;
    }
    
    public function set_bulk($data)
    {
        if (is_array($data)) {
            
            foreach ($data as $key=>$val) {
                $this->set($key, $val);
            }
            
        }
    }

    public function bccEmail($str=false)
    {
        if ($str === false) {
            return $this->bccEmail;
        }
        
        $this->bccEmail = $str;
    }
     
    public function attachFile($name, $path, $mimetype, $cid=false)
    {
        $file = array();
        $file['name'] = $name;
        $file['path'] = $path;
        $file['mimetype'] = $mimetype;
        $file['cid'] = $cid;
        $this->files[] = $file;
    }

    public function include_inline_images($dir)
    {
        $this->inline_image_dir = $dir;
    }
    
    public function send()
    {
        $body = $this->build_message();
        
        $Postmark = Factory::get('Postmark'); 
            
        if ($this->replyToEmail()) $Postmark->reply_to($this->replyToEmail());
        if ($this->bccEmail()) $Postmark->bcc($this->bccEmail());

        $Postmark->from($this->senderEmail());
        $Postmark->to($this->recipientEmail());
        $Postmark->subject($this->subject());
        $Postmark->html_message($body);
            

        if (Util::count($this->files)) {
            foreach($this->files as $file) {
                $Postmark->attachment($file['name'], file_get_contents($file['path'].'/'.$file['name']), $file['mimetype'], $file['cid']); 
            }
        }

        if (!$Postmark->send()) {
            Console::log($Postmark->error_code, 'error');
            return false;
        }else{
            Console::log("Message sent!");
            return true;
        }

        return false;

    }
       
    
    private function build_message()
    {
        $path		= $this->template_path;
        $template   = $this->template;
        $data       = $this->vars;
		
        if (!$template) {
            return $this->body;
        }

		// test for data
		if (!is_array($data)){
			Console::log('No data sent to email templating engine.', 'notice');
			return false;
		}
				
			
		// check if template is cached
		if (isset($this->cache[$template])){
			// use cached copy
			$contents	= $this->cache[$template];		
		}else{
			// read and cache		
			if (file_exists($path)){
				$contents 	= file_get_contents($path);
				$this->cache[$template]	= addslashes($contents);
			}
		}
		
		if (isset($contents)){
			$this->template_data 	= $data;

            if ($this->use_twig) {

                $Loader   = new Twig_Loader_Filesystem($this->template_folder);
                $Twig     = new Twig_Environment($Loader);
                $contents = $Twig->render($this->template, $data);

            }else{
                $contents               = preg_replace_callback('/\$(\w+)/', array($this, "substitute_vars"), $contents);
                $this->template_data    = '';
    
            }


			
            if ($this->html) {
                $s = '/<title>(.*?)<\/title>/';
                if (preg_match($s, $contents, $matches)) {
                    if (isset($matches[1])) {
                        $this->subject($matches[1]);
                    }
                }
          
                // inline images
                if ($this->inline_image_dir) {
                    $this->_add_inline_images();
                }

            }
			
			return stripslashes($contents);
		}else{
			Console::log('Template does not exist: '. $template, 'error');
			return false;
		}
    }
    
    private function substitute_vars($matches)
    {
    	$tmp_template_data = $this->template_data;
    	if (isset($tmp_template_data[$matches[1]])){
    		return $tmp_template_data[$matches[1]];
    	}else{
    		Console::log('Template variable not found: '.$matches[1], 'notice');
    		return '';
    	}
    }

    private function _add_inline_images()
    {
        $files = Util::get_dir_contents($this->template_folder.$this->inline_image_dir);

        if (Util::count($files)) {

            $image_extensions = array('gif', 'jpg', 'png');

            foreach($files as $file) {
                if (in_array(Util::file_extension($file), $image_extensions)) {
                    $cid = 'cid:'.md5($file).$this->senderEmail;
                    $size = getimagesize($this->template_folder.$this->inline_image_dir.$file);
                    if (is_array($size)) {
                        $mime = $size['mime'];    
                    }else{
                        $mime = false;
                    }

                    Console::log($mime);

                    $this->attachFile($file, $this->template_folder.$this->inline_image_dir, $mime, $cid);
                    
                }
            }
        }
    }
    

}
