<?php
namespace Arsenal\Misc;

class Crypt
{
    public function setDefaultSalt($salt)
    {
        $this->defaultSalt = $salt;
    }
    
    public function bcrypt($string)
    {
        $salt = sha1(mt_rand());
        $subsalt = substr($salt, 0, 21);
        $string = $this->saltedScramble($string, $subsalt);
        return crypt($string, '$2y$08$'.$salt.'$');
    }
    
    public function bcryptMatch($string, $hash)
    {
        $subsalt = substr($hash, 7, 21);
        $string = $this->saltedScramble($string, $subsalt);
        return crypt($string, $hash) === $hash;
    }
    
    public function encrypt($string, $key)
    {
        $cipher = MCRYPT_RIJNDAEL_128;
        $mode = MCRYPT_MODE_CBC;
        
        $ivSize = mcrypt_get_iv_size($cipher, $mode);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        
        $data = $string;
        $key = md5($key);
        $checksum = substr(hash('sha256', $data), 0, 10);
        
        $encrypted = mcrypt_encrypt($cipher, $key, $data, $mode, $iv);
        return base64_encode($iv.$checksum.$encrypted);
    }
    
    public function decrypt($string, $key)
    {
        $string = base64_decode($string);
        
        $cipher = MCRYPT_RIJNDAEL_128;
        $mode = MCRYPT_MODE_CBC;
        
        $ivSize = mcrypt_get_iv_size($cipher, $mode);
        $iv = substr($string, 0, $ivSize);
        
        $encrypted = substr($string, $ivSize+10);
        $key = md5($key);
        $checksum = substr($string, $ivSize, 10);
        
        $data = mcrypt_decrypt($cipher, $key, $encrypted, $mode, $iv);
        $data = rtrim($data, "\0"); // decrypt may generate some blank garbage
        
        if($checksum !== substr(hash('sha256', $data), 0, 10))
            return false;
        
        return $data;
    }
    
    private function saltedScramble($string, $salt)
    {
        $string = strrev($string);
        $salt = strrev($salt);
        $newstr = '';
        for($i=0; $i<strlen($string); $i++)
            $newstr .= $salt[$i % strlen($salt)].$string[$i];
        $newstr .= $salt;
        return $newstr;
    }
}