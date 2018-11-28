<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 2018-09-06
 * Time: 21:01
 */


namespace uploader;

use Upyun\Config;
use Upyun\Upyun;

class UploadUpyun extends Upload{
    public $serviceName;
    //操作员名称
    public $operator;
    //操作员密码
    public $password;
    //域名
    public $domain;
    public static $config;
    //arguments from php client, the image absolute path
    public $argv;

    /**
     * Upload constructor.
     *
     * @param $config
     * @param $argv
     */
    public function __construct($config, $argv)
    {
	    $tmpArr = explode('\\',__CLASS__);
	    $className = array_pop($tmpArr);
	    $ServerConfig = $config['storageTypes'][strtolower(substr($className,6))];
	    
        $this->serviceName = $ServerConfig['serviceName'];
        $this->operator = $ServerConfig['operator'];
        $this->password = $ServerConfig['password'];
        $this->domain = $ServerConfig['domain'];

        $this->argv = $argv;
        static::$config = $config;
    }
	
	/**
	 * 上传到又拍云
	 * @param $key
	 * @param $uploadFilePath
	 * @param $originFilename
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function upload($key, $uploadFilePath, $originFilename){
	    try {
		    $serviceConfig = new Config($this->serviceName, $this->operator, $this->password);
		    $client = new Upyun($serviceConfig);
		    $retArr = $client->write($key, fopen($uploadFilePath, 'r'));
		
		    if(!isset($retArr['x-upyun-content-length'])){
			    throw new \Exception(var_export($retArr, true)."\n");
		    }else{
			    $publicLink = $this->domain.'/'.$key;
			    //按配置文件指定的格式，格式化链接
			    $link = $this->formatLink($publicLink, $originFilename);
			    return $link;
		    }
	    } catch (NosException $e) {
		    //上传数错，记录错误日志
		    $this->writeLog($e->getMessage()."\n", 'error_log');
	    }
    }
}