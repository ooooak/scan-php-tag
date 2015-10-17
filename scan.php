<?php


set_time_limit(0);
error_reporting(-1);

class UnClosed
{

	private static $ignore = [
		'js','css','svg','ttf',
		'eot','woff','png','jpg',
		'gif'
	];

	private static $regx = '/\?\>(\n+|\s+|$)$/';
	// private static $match_space_only = '/\?\>\s$/';

	/**
	 * [init description]
	 * @param  [string] $dir path
	 * @return [array] 	array of files that have php tag at the end.
	 */
	public static function scan( $path )
	{
		$files = self::scan_files( $path );

		return self::filter($files);
	}

	/**
	 * set custom options
	 * @param Array $opt [description]
	 */
	public static function set( Array $opt )
	{

		if (self::array_has($opt, 'regx'))
		{
			self::$regx = $opt['regx']; 
		}

		if (self::array_has($opt, 'ignore'))
		{
			self::$ignore = self::$ignore + explode('|', $opt['ignore']);
		}

	} 

	private static function array_has($array, $key)
	{
		return isset($array[$key]);
	}

	/**
	 * TODO: implement in php
	 * Get all the files from Given directory.
	 * @param  [type] $dir
	 * @return [type]
	 */
	private static function scan_files( $dir )
	{
		return array_filter(explode("\n", shell_exec("find $dir -type f")));
	}

	private static function filter( $files )
	{
		$bad_files = [];

		foreach ($files as $file) 
		{
			list($err, $content) = self::get_content($file);
			if ($err){
				continue;
			}

			preg_match(self::$regx, $content, $match);

			if (self::validate_match($match))
			{
				$bad_files[] = $file;
			}
		}

		return $bad_files;
	}

	private static function validate_match( $match )
	{

		if (isset($match[0]) && strlen($match[0]) > 2 && in_array(trim($match[0]), ['?>','<?','<?php']))
		{

			return true;
		}

		return false;
	}

	private static function get_content( $file )
	{
		$err = [true, ''];
		if (empty($file) or !is_readable($file)){
			return $err;
		}

		$chunk = explode('.', $file);
		$ext = end($chunk);

		if (in_array(trim($ext), self::$ignore)){
			return $err;
		}

		$content = file_get_contents($file);

		return [false, $content];
	}
}




