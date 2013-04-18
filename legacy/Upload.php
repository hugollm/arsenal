<?php

class Upload
{
    private $name;
    private $mime;
    private $file;
    private $error;
    private $size;
    
    private $allowedExtensions = array();
    private $forbiddenExtensions = array();
    private $maxSize = null;
    private $allowEmpty = true;
    
    public function __construct($index)
    {
        if(empty($_FILES[$index]))
            throw new UnexpectedValueException('$_FILES[\''.$index.'\'] does not exist.');
        
        if(is_array($_FILES[$index]['name'])) // multiple file upload. ex: photo[0], photo[1]...
            $file = $this->getFirstFile($_FILES[$index]);
        else
            $file = $_FILES[$index];
        
        $this->name = $file['name'];
        $this->mime = $file['type'];
        $this->file = $file['tmp_name'];
        $this->error = $file['error'];
        $this->size = $file['size'];
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getSafeName()
    {
        $name = $this->name;
        while(strpos($name, '..') !== false)
            $name = str_replace('..', '.', $name);
        $name = preg_replace('|[^a-zA-Z0-9_.-]|', '_', $name);
        return $name;
    }
    
    public function generateRandomName()
    {
        return sha1(mt_rand()).'.'.$this->getExtension();
    }
    
    public function getExtension()
    {
        $ext = (strrpos($this->name, '.') !== false) ? substr($this->name, strrpos($this->name, '.')+1) : null;
        return strtolower($ext);
    }
    
    public function getFile()
    {
        if($this->getError())
            throw new RunTimeException('Cannot get upload file with error: '.$this->getErrorMessage().'.');
        return $this->file;
    }
    
    public function getContents()
    {
        return file_get_contents($this->getFile());
    }
    
    public function getError()
    {
        if($this->error)
            return $this->error;
        
        if( ! $this->allowEmpty and $this->size === 0)
            return 'empty';
        
        if($this->allowedExtensions and ! in_array($this->getExtension(), $this->allowedExtensions))
            return 'notAllowed';
        if($this->allowedExtensions and in_array($this->getExtension(), $this->forbiddenExtensions))
            return 'forbidden';
        
        if( ! $this->getSafeName())
            return 'name';
        
        if($this->maxSize and $this->size > $this->maxSize)
            return 'size';
        
        if( ! is_uploaded_file($this->file))
            return 'file';
        
        return false;
    }
    
    public function getErrorMessage()
    {
        $error = $this->getError();
        
        if( ! $error)
            return false;
        if($error === 1)
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        if($error === 2)
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        if($error === 3)
            return 'The uploaded file was only partially uploaded';
        if($error === 4)
            return 'No file was uploaded';
        if($error === 6)
            return 'Missing a temporary folder';
        if($error === 7)
            return 'Failed to write file to disk';
        if($error === 8)
            return 'A PHP extension stopped the file upload';
        if($error === 'empty')
            return 'File cannot be empty.';
        if($error === 'notAllowed')
            return 'File extension not in the allowed list';
        if($error === 'forbidden')
            return 'Forbidden file extension';
        if($error === 'name')
            return 'Suspicious or invalid file name';
        if($error === 'size')
            return 'File size exceeded defined max size';
        if($error === 'file')
            return 'File is not an upload';
        
        return 'Unknown error';
    }
    
    public function getMime()
    {
        return $this->mime ?: 'application/octet-stream';
    }
    
    public function getSize()
    {
        return $this->size;
    }
    
    public function getMaxSize()
    {
        return $this->maxSize;
    }
    
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }
    
    public function getForbiddenExtensions()
    {
        return $this->forbiddenExtensions;
    }
    
    public function setAllowedExtensions(array $extensions)
    {
        $extensions = array_map('strtolower', $extensions);
        $this->allowedExtensions = $extensions;
    }
    
    public function setForbiddenExtensions(array $extensions)
    {
        $extensions = array_map('strtolower', $extensions);
        $this->forbiddenExtensions = $extensions;
    }
    
    public function setMaxSize($format)
    {
        $this->maxSize = $this->iniFormatToBytes($format);
    }
    
    public function forbidEmpty()
    {
        $this->allowEmpty = false;
    }
    
    public function isValid()
    {
        return ! $this->getError();
    }
    
    public function save($filename)
    {
        if($this->getError())
            throw new RunTimeException('Cannot save upload with error: '.$this->getErrorMessage().'.');
        return move_uploaded_file($this->file, $filename);
    }
    
    private function getFirstFile($fileArray)
    {
        return array(
            'name' => current($fileArray['name']),
            'type' => current($fileArray['type']),
            'tmp_name' => current($fileArray['tmp_name']),
            'error' => current($fileArray['error']),
            'size' => current($fileArray['size']),
        );
    }
    
    private function iniFormatToBytes($format)
    {
        if(is_numeric($format))
            return $format;
            
        $bytes = (int)$format;
        $letter = strtolower(substr($format, -1));
        
        if($letter == 'k')
            $bytes = $bytes * 1024;
        if($letter == 'm')
            $bytes = $bytes * 1024 * 1024;
        if($letter == 'g')
            $bytes = $bytes * 1024 * 1024 * 1024;
        
        return $bytes;
    }
}