<?php

class Response
{
    public static function setStatus($status)
    {
        switch ($status) {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
        }
        
        if(isset($text)) {
            $protocol = Request::getProtocol();
            header("$protocol $status $text", true);
            header("Status: $status $text", true); // for fast_cgi
        }
        else
            throw new InvalidArgumentException('Unknown HTTP status code: '.$status);
    }
    
    public static function setMime($mime)
    {
        header('Content-type: '.$mime, true);
    }
    
    /*
        Replaces the current reponse body (current buffer) with the specified
        string.
    */
    public static function setBody($body)
    {
        ob_clean();
        echo $body;
    }
    
    /*
        Gets the body of the current response (current buffer).
    */
    public static function getBody()
    {
        return ob_get_contents();
    }
    
    /*
        Handles the client caching of the current request.
        If the request was identified as not modified, a correct status
        is set here.
    */
    public static function setCache($etag, $expires = null)
    {
        header('Etag: "'.$etag.'"', true);
        if($etag === Request::getEtag())
        {
            self::setStatus(304);
            self::setBody('');
        }
        
        if($expires)
        {
            $expires = new DateTime($expires);
            $expiresHeader = gmdate('D, d M Y H:i:s', $expires->getTimestamp()).' GMT';
            $seconds = $expires->getTimestamp() - time();
            header("Expires: $expiresHeader", true);
            header("Pragma: cache", true);
            header("Cache-Control: max-age=$seconds", true);
        }
    }
    
    public static function send($status = null, $body = null, $mime = null, $cache = null)
    {
        if($status)
            self::setStatus($status);
        if($mime)
            self::setMime($mime);
        if($body)
            self::setBody($body);
        if($cache === true)
            self::setCache(sha1(self::getBody()));
        if(is_string($cache))
            self::setCache(sha1(self::getBody()), $cache);
        die;
    }
    
    public static function sendFile($path, $mime = null, $cache = null)
    {
        if( ! is_file($path))
            self::send(404);
        
        if( ! $mime)
            $mime = self::getMime($path);
        
        $contents = file_get_contents($path);
        self::send(200, $contents, $mime, $cache);
    }
    
    public static function getMime($file)
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
        
        $ext = substr($file, strrpos($file, '.')+1);
        
        if(isset($mimes[$ext]))
            return $mimes[$ext];
        else
            return 'application/octet-stream';
    }
    
    public static function redirect($uri, $scheme = null, $time = null)
    {
        if( ! $scheme)
            $scheme = Request::getScheme();
        
        // if uri is not absolute (http://...)
        if(strpos($uri, '://') === false)
        {
            if(strpos($uri, '/') !== 0)
                $uri = '/'.$uri;
            $uri = $scheme.'://'.Request::getHost().Request::getUriBase().$uri;
        }
        
        if($time)
            header("Refresh: ".$time."; URL=".$uri);
        else
        {
            header("Location: ".$uri);
            self::send();
        }
    }
    
    public static function redirectBack($fallback = null, $scheme = null, $time = null)
    {
        $url = Request::getReferer();
        if( ! $url and $fallback)
            $url = $fallback;
        self::redirect($url, $scheme, $time);
    }
    
    public static function redirectSelf($scheme = null, $time = null)
    {
        self::redirect(Request::getUriArgs(), $scheme, $time);
    }
}