<?php

class curl {

	private static $cookie = '';
	private $url = '';

	public static $Refere = 'https://www.bevol.cn/';

	/**
	 * [request 执行一次curl请求]
	 * @param  [string] $url        [请求的URL]
	 * @param  [string] $method     [请求方法]
	 * @param  array  $fields     [执行POST请求时的数据]
	 * @return [stirng]             [请求结果]
	 */
	public static function request($url, $cookie = '', $method = 'GET', $is_header = 0, $fields = array())
	{
		$ch = curl_init($url);
		if($is_header == 1)
			curl_setopt($ch, CURLOPT_HEADER, 1);
		else
			curl_setopt($ch, CURLOPT_HEADER, 0);
		// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) App leWebKit/537.51.2 (KHTML, like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53');
		curl_setopt ($ch,CURLOPT_REFERER, self::$Refere);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_COOKIE, self::getCookie($cookie));
		if ($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		}
		$result = curl_exec($ch);
		if($result) {
			if($is_header == 1) {
				$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$res['header'] = substr($result, 0, $headerSize);
				$res['body'] = $result;
				return $res;
			}

			return $result;
		}
		else {
			return curl_error($ch);
		}
	}

	protected static function getCookie($cookie)
	{
		if(!$cookie || !is_array($cookie))
			return $cookie;
		$cookie = http_build_query($cookie);

		return implode(';', explode('&', $cookie));
	}
}
