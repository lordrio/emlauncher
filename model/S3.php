<?php
require_once APP_ROOT.'/model/Config.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class S3 {

	protected static $singleton = null;
	protected $config;
	protected $client;

	protected function __construct()
	{
		$this->config = Config::get('aws');
		error_log(implode(',', array(
			'key' => $this->config['key'],
			'secret' => $this->config['secret'])));
		// $this->client = Aws\S3\S3Client::factory(
		// 	array(
		// 		// 'key' => $this->config['key'],
		// 		// 'secret' => $this->config['secret'],
		// 		));
		$this->client = new S3Client([
			'version' => 'latest',
			'region'  => 'ap-southeast-1'
		]);
	}
	protected function singleton()
	{
		if(static::$singleton===null){
			static::$singleton = new static();
		}
		return static::$singleton;
	}

	public static function uploadData($key,$data,$type,$acl='private')
	{
		error_log($key);
		error_log($data);
		error_log($type);
		error_log($acl);
		$s3 = static::singleton();
		$r = $s3->client->putObject(
			array(
				'Bucket' => $s3->config['bucket_name'],
				'Key' => $key,
				'ACL' => $acl,
				'ContentType' => $type,
				'Body' => Guzzle\Http\EntityBody::factory($data),
				));
		return $r;
	}

	public static function uploadFile($key,$filename,$type,$acl='private')
	{
		$s3 = static::singleton();
		$fp = fopen($filename,'rb');

		try {
			// Upload data.
			$result = $s3->client->putObject([
				'Bucket' => $s3->config['bucket_name'],
				'Key'    => $key + ".txt",
				'Body'   => 'Hello, world!',
				'ACL'    => 'public-read'
			]);
		
			// Print the URL to the object.
			error_log($result['ObjectURL'] . PHP_EOL);
		} catch (S3Exception $e) {
			error_log($e->getMessage() . PHP_EOL);
		}

		$r = $s3->client->putObject(
			array(
				'Bucket' => $s3->config['bucket_name'],
				'Key' => $key,
				'ACL' => $acl,
				'ContentType' => $type,
				'Body' => $fp,
				));
		// Guzzleが中で勝手にfcloseしやがるのでここでfcloseしてはならない
		// fclose($fp)
		return $r;
	}

	public static function rename($srckey,$dstkey,$acl='private')
	{
		$s3 = static::singleton();
		$bucket = $s3->config['bucket_name'];

		// copy
		$s3->client->copyObject(
			array(
				'Bucket' => $bucket,
				'Key' => $dstkey,
				'ACL' => $acl,
				'CopySource' => "{$bucket}/{$srckey}",
				));
		// delete
		$s3->client->deleteObject(
			array(
				'Bucket' => $bucket,
				'Key' => $srckey,
				));
	}

	public static function delete($key)
	{
		$s3 = static::singleton();
		$bucket = $s3->config['bucket_name'];
		$s3->client->deleteObject(
			array(
				'Bucket' => $bucket,
				'Key' => $key,
				));
	}

	public static function url($key,$expires=null)
	{
		$s3 = static::singleton();
		$bucket = $s3->config['bucket_name'];
		if($expires===null){
			return "https://{$bucket}.s3.amazonaws.com/{$key}";
		}
		return $s3->client->getObjectUrl($bucket,$key,$expires);
	}

}

