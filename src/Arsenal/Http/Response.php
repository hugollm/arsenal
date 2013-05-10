<?php
namespace Arsenal\Http;

class Response
{
    private $request = null;
    private $status = 200;
    private $body = '';
    private $headers = array();
    
    private $calculateEtag = false;
    private $etag = null;
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function setContext(Request $request)
    {
        $this->request = $request;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    public function setBody($body)
    {
        $this->body = (string)$body;
    }
    
    public function appendBody($body)
    {
        $this->body .= (string)$body;
    }
    
    public function setHeader($key, $val)
    {
        $this->headers[$key] = $val;
    }
    
    public function setContentType($mime, $encoding = null)
    {
        $header = $mime;
        if($encoding)
            $header .= '; '.$encoding;
        $this->setHeader('Content-Type', $header);
    }
    
    public function setEtag($etag, $weak = false)
    {
        $header = '"'.$etag.'"';
        if($weak)
            $header = 'W/'.$header;
        $this->setHeader('Etag', $header);
        $this->etag = $etag;
    }
    
    public function setCalculateEtag($bool)
    {
        $this->calculateEtag = $bool;
    }
    
    public function setExpires($time)
    {
        $dt = new \DateTime($time);
        $header = gmdate('D, d M Y H:i:s', $dt->getTimestamp()).' GMT';
        $this->setHeader('Expires', $header);
    }
    
    public function setCache($calculateEtag, $expires = null)
    {
        $this->setCalculateEtag($calculateEtag);
        if($expires)
            $this->setExpires($expires);
    }
    
    public function setRedirect($url)
    {
        $this->setHeader('Location', $url);
    }
    
    public function setRefresh($url, $wait)
    {
        $dt = new \DateTime($wait);
        $seconds = $dt->getTimestamp() - time();
        $this->setHeader('Refresh', "$seconds; URL=$url");
    }
    
    public function dropHeader($key)
    {
        unset($this->headers[$key]);
    }
    
    public function captureInclude($file)
    {
        ob_start();
        include $file;
        $body = ob_get_clean();
        $this->appendBody($body);
    }
    
    public function captureExec($callback)
    {
        if( ! is_callable($callback))
            throw new \InvalidArgumentException('Invalid callback for captureExec');
        
        ob_start();
        call_user_func($callback);
        $body = ob_get_clean();
        $this->appendBody($body);
    }
    
    public function sendHeaders()
    {
        header_remove('X-Powered-By');
        header('x', true, $this->status);
        foreach($this->headers as $key=>$val)
            header($key.': '.$val);
    }
    
    public function sendBody()
    {
        echo $this->body;
    }
    
    public function send()
    {
        if($this->calculateEtag)
            $this->setEtag(sha1($this->body));
        $this->tryNotModified();
        
        $this->sendHeaders();
        $this->sendBody();
    }
    
    /*
        This method sends only the contents in this object, clearing all other
        buffers and terminating imediately.
    */
    public function sendHard()
    {
        if($this->calculateEtag)
            $this->setEtag(sha1($this->body));
        $this->tryNotModified();
        
        $this->clearAllBuffers();
        $this->sendHeaders();
        $this->sendBody();
        die;
    }
    
    public function sendFile($filename, $download = false)
    {
        if( ! is_file($filename))
        {
            $this->setStatus(404);
            $this->sendHard();
        }
        
        $basename = basename($filename);
        $mime = $this->guessMime($filename);
        
        $this->setContentType($mime);
        $this->setHeader('Content-Transfer-Encoding', 'chunked');
        $this->setHeader('Content-Length', filesize($filename));
        $this->setHeader('Content-Disposition', 'inline; filename="'.$basename.'"');
        
        if($this->calculateEtag)
            $this->setEtag(sha1_file($filename));
        $this->tryNotModified();
        
        if($download)
        {
            // $this->setHeader('Content-Type', 'application/octet-stream');
            $this->setHeader('Content-Disposition', 'attachment; filename="'.$basename.'"');
        }
        
        $this->clearAllBuffers();
        $this->sendHeaders();
        $this->readFileChunked($filename);
        die;
    }
    
    public function sendRedirect($url, $status = 200)
    {
        $this->setBody('');
        $this->setStatus($status);
        $this->setRedirect($url);
        $this->sendHard();
    }
    
    public function sendRedirectBack()
    {
        if( ! $this->request)
            throw new \RuntimeException('To redirect back you have to set a Request with setContext()');
        
        $url = $this->request->getReferer();
        $this->sendRedirect($url);
    }
    
    public function sendNotModified()
    {
        $this->setStatus(304);
        $this->setBody('');
        $this->sendHard();
    }
    
    private function tryNotModified()
    {
        if($this->etag and $this->request and $this->etag == $this->request->getEtag())
            $this->sendNotModified();
    }
    
    private function clearAllBuffers()
    {
        while(ob_get_level() > 1)
            ob_end_clean();
        ob_clean();
    }
    
    /*
        This can send large files in the output buffer, because it handles
        it by chunks.
    */
    private function readFileChunked($filename, $chunkSize = 1048576)
    {
        $file = fopen($filename, 'rb');
        while( ! feof($file))
        {
            echo fread($file, $chunkSize);
            ob_flush();
            flush();
        }
        fclose($file);
    }
    
    private function guessMime($filename)
    {
        $mimes = array(
            
            // web
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',
            
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        
        $ext = substr($filename, strrpos($filename, '.')+1);
        
        if(isset($mimes[$ext]))
            return $mimes[$ext];
        else
            return 'application/octet-stream';
    }
}