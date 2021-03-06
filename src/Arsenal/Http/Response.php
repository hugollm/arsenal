<?php
namespace Arsenal\Http;

class Response
{
    private $request;
    private $status = 200;
    private $body = '';
    private $headers = array();
    private $cookies = array();
    
    private $calculateEtag = false;
    private $etag = null;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function setStatus($status)
    {
        if( ! is_int($status) or $status < 100 or $status > 600)
            throw new \InvalidArgumentException('Invalid HTTP status code: '.$status);
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
    
    public function setCache($expires = null, $calculateEtag = true)
    {
        $this->setCalculateEtag($calculateEtag);
        if($expires)
            $this->setExpires($expires);
    }
    
    /*
        $expiration should be strtotime formatted (ex: 5 years). Default is 
        to be valid for just the current browser session (until the user
        closes the browser).
        
        $path default is the root of the domain (/), or base path if there's a
        request object attatched.
        
        $domain default is the current one.
    */
    public function setCookie($key, $val, $expiration = null, $path = null, $domain = null, $secureOnly = false, $httpOnly = true)
    {
        if($expiration !== null)
            $expiration = strtotime($expiration);
        if($path === null)
            $path = $this->request->getBasePath();
        
        $this->cookies[] = array(
            'key' => $key,
            'val' => $val,
            'expiration' => $expiration,
            'path' => $path,
            'domain' => $domain,
            'secureOnly' => $secureOnly,
            'httpOnly' => $httpOnly,
        );
    }
    
    /*
        To drop a cookie you must match the arguments used to create it.
    */
    public function dropCookie($key, $path = null, $domain = null, $secureOnly = false, $httpOnly = true)
    {
        $this->setCookie($key, false, '-5 years', $path, $domain, $secureOnly, $httpOnly);
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
    
    public function captureCurrent($clean = false)
    {
        $this->appendBody(ob_get_contents());
        if($clean)
            $this->clearAllBuffers();
    }
    
    public function sendHeaders()
    {
        header_remove('X-Powered-By');
        header('x', true, $this->status);
        foreach($this->headers as $key=>$val)
            header($key.': '.$val);
        foreach($this->cookies as $c)
            setcookie($c['key'], $c['val'], $c['expiration'], $c['path'], $c['domain'], $c['secureOnly'], $c['httpOnly']);
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
    
    public function redirect($to, $scheme = null)
    {
        $this->setBody('');
        if( ! $scheme)
            $scheme = $this->request->getScheme();
        
        if( ! preg_match('|^[a-z]+://|', $to))
            $to = $scheme.'://'.$this->request->getHost().$this->request->getBasePath().$this->normalizePath($to);
        
        $this->setHeader('Location', $to);
        $this->sendHard();
    }
    
    public function redirectBack($scheme = null)
    {
        $to = $this->request->getReferer();
        $this->redirect($to, $scheme);
    }
    
    public function redirectSelf($scheme = null)
    {
        $to = $this->request->getPathInfo();
        $this->redirect($to, $scheme);
    }
    
    public function sendNotModified()
    {
        $this->setStatus(304);
        $this->setBody('');
        $this->sendHard();
    }
    
    private function tryNotModified()
    {
        if($this->etag and $this->etag == $this->request->getEtag())
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
    
    private function normalizePath($path)
    {
        $path = trim($path, '/');
        while(strpos($path, '//') !== false)
            $path = str_replace('//', '/', $path);
        return '/'.$path;
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