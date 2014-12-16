<?php


$tokenObj = new token();
//$array =$tokenObj->GetUserDetail("oktkLj6dYYSEc9c4rW41Hhm3V8R0");
//$tokenObj->InitToken();
$tokenObj ->message("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=YDUVzBGsFPpw2sF_Kx-F3kP0kjIOt9IQWiMqHMGcla-lvZ7SewRQaS_TOdzsXpTe5K_IrDS95I8_mthqXGQWxhO08Dn8R36vJgOj-VR40wg","oktkLj6dYYSEc9c4rW41Hhm3V8R0");
//echo $array[1];

class token{
function message($url,$openid)
{
	$opts = array (
		'touser' => $openid,
		'msgtype'=> "text",
		'text' => array (
			'content' => 'Hello World'
		)
	);
	 
	$context = stream_context_create($opts);
	

	echo file_get_contents($url, false, stream_context_create($options));;
}
/*
//得到订阅用户 (返回数组)
	public function GetUserList()
	{
		$strjson = $this -> GetUrlReturn("https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s");
		$openidarr= $strjson->data->openid;
		//print_r($openidarr); 调试
		return $openidarr;
	}

	//得到订阅用户详情（返回对象）
	function GetUserDetail($openid)
	{
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=".$openid;
		$strjson = $this -> GetUrlReturn($url);
		return $strjson;
	}
*/
	/*
	*
	*  私有成员变量 存token值
	*  因为//access_token是公众号的全局唯一票据，公众号调用各接口时都需使用access_token。
	*  正常情况下access_token有效期为7200秒，重复获取将导致上次获取的access_token失效。
	*/
	public $_token;
	/*
	*
	* 私有方法
	*
	*/
	//得到Token对象并写入到配置文件
	function InitToken()
	{

		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx3084b898f1e50c78&secret=44a6c855b7c59078c34bf1005988f054";
		$a = file_get_contents($url);
		
		$strjson=json_decode($a);
		$token = $strjson->access_token;
		if (empty($token))
		{
			//修改 {"errcode":45009,"errmsg":"api freq out of limit"}
			echo "错误：取得token无效，可能是调用太频繁！";	  //$strjson
			throw new Exception('错误：取得token无效'); 
		}
		$obj = fopen("token.txt","w+");  //SAE禁用fopen本地文件，这里需要Storage
		fwrite($obj,$token);
		$this -> _token = $token;

	}

	//封装私有方法，调用得到Get的参数,$needToken默认为false, 不取值，这里有一个潜规则，%s为 self::$_token
	function GetUrlReturn($url, $needToken = false)
	{
		//第一次为空，则从文件中读取
		if (empty($this -> _token))
		{
			$obj = fopen("token.txt","r"); 
			$this -> _token = fgets($obj,1000);
		}
		//为空则重新取值
		if (empty($this -> _token) || $needToken)
		{
			$this ->InitToken(); 
		}
		echo $this ->_token;
		$newurl = sprintf($url, $this -> _token);
		
		$a = file_get_contents($newurl);
		echo $a;
		$strjson=json_decode($a);
		//var_dump($strjson);  //开启可调试
		if (!empty($strjson-> errcode))
		{
			switch ($strjson-> errcode){	
				case 40001:
					$this -> GetUrlReturn($url, true); //重新取值，可能是过期导致
					break;
				case 41001:
					throw new Exception("缺少access_token参数:".$strjson->errmsg); 
					break;
				default:
					throw new Exception($strjson->errmsg); //其他错误，抛出
					break;
			}
		}
		return $strjson;
	}
}
?>