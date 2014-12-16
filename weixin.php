<?php
define("TOKEN", "weixin");//自己定义的token 就是个通信的私钥
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();//测试连接时候打开
$wechatObj->responseMsg();
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
	
	public function responseMsg()
    {
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
			$MsgType = $postObj->MsgType;//消息类型
            $time = time();
			/**********************
			如果是文本消息（表情属于文本信息）
			**********************/
			if($MsgType == "text"){
				$keyword = trim($postObj->Content);//消息内容
				$textTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[%s]]></MsgType>
								<Content><![CDATA[%s]]></Content>
								<FuncFlag>0</FuncFlag>
								</xml>";             
				if(!empty( $keyword ))
                {
					/*************************
						判断包涵的文字
					*************************/
					if(strstr($keyword,"bdzh"))//如果包涵bdzh
					{
						$words = explode('#',$keyword);
						
						if(count($words)!=3 || $words[0]!="bdzh")
						{
							$contentStr = "回复格式如下：\nbdzh#学号#密码\n进行绑定帐号";
						}
						else
						{
							//连接数据库
							$conn =mysql_connect("hdm-104.hichina.com","hdm1040866","a1bb5366") or die("connect failed" . mysql_error()); 
							mysql_select_db("hdm1040866_db",$conn);
							
							//读取表中纪录条数
							$sql = sprintf("select count(*) from %s where openid='%s'", "weixin",$fromUsername); 
							$result = mysql_query($sql, $conn);
							if ($result)
								$count = mysql_fetch_row($result);  
							else  
								die("query failed"); 
									
							if($count[0] == 0)//创建新的
							{
								$sql = sprintf("INSERT INTO weixin (openid,name,pass) VALUES ('%s','%s','%s') ",$fromUsername,$words[1],$words[2]); 
								mysql_query($sql, $conn);
								$contentStr = "绑定完成\n帐户：".$words[1]."\n密码：".$words[2];
							}
							else //更新原有
							{
								$sql = sprintf("UPDATE weixin SET name = '%s' , pass = '%s' WHERE openid = '%s'",$words[1],$words[2],$fromUsername); 
								mysql_query($sql, $conn);
								$contentStr = "更改完成\n帐户：".$words[1]."\n密码：".$words[2];
							}
							
							//关闭mysql
							mysql_free_result($result); 
							mysql_close($conn);
						}
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
					}
					/*************************
						正常判断相等的文字
					*************************/
					else switch($keyword)
					{
						case "考试时间":
							
							//连接数据库
							$conn =mysql_connect("hdm-104.hichina.com","hdm1040866","a1bb5366") or die("connect failed" . mysql_error()); 
							mysql_select_db("hdm1040866_db",$conn);
							
							//读取表中纪录条数
							$sql = sprintf("select count(*) from %s where openid='%s'", "weixin",$fromUsername); 
							$result = mysql_query($sql, $conn);
							if ($result)
								$count = mysql_fetch_row($result);  
							else  
								die("query failed"); 
									
							if($count[0] == 0)//没有
							{
								$contentStr = '请绑定帐户';
							}
							else //有，进行查找
							{
								$sql = sprintf("select name,pass from %s where openid='%s'", "weixin",$fromUsername); 
								$result_name = mysql_query($sql, $conn);
								if ($result_name)
									$name = mysql_fetch_row($result_name);  
								else  
									die("query failed"); 
								$contentStr = "学号".$name[0]." <a href=\"http://www.i3geek.com/hrbust/newpage.php?cmd=1&n=".$name[0]."&p=".$name[1]."\">立即查看考试时间</a>";
							}
							
							//关闭mysql
							mysql_free_result($result_name);
							mysql_free_result($result); 
							mysql_close($conn);
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "考试成绩":
							//连接数据库
							$conn =mysql_connect("hdm-104.hichina.com","hdm1040866","a1bb5366") or die("connect failed" . mysql_error()); 
							mysql_select_db("hdm1040866_db",$conn);
							
							//读取表中纪录条数
							$sql = sprintf("select count(*) from %s where openid='%s'", "weixin",$fromUsername); 
							$result = mysql_query($sql, $conn);
							if ($result)
								$count = mysql_fetch_row($result);  
							else  
								die("query failed"); 
									
							if($count[0] == 0)//没有
							{
								$contentStr = '请绑定帐户';
							}
							else //有，进行查找
							{
								$sql = sprintf("select name,pass from %s where openid='%s'", "weixin",$fromUsername); 
								$result_name = mysql_query($sql, $conn);
								if ($result_name)
									$name = mysql_fetch_row($result_name);  
								else  
									die("query failed"); 
								$contentStr = "学号".$name[0]." <a href=\"http://www.i3geek.com/hrbust/newpage.php?cmd=2&n=".$name[0]."&p=".$name[1]."\">立即查看考试成绩</a>";
							}
							
							//关闭mysql
							mysql_free_result($result_name);
							mysql_free_result($result); 
							mysql_close($conn);
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "绑定帐号":
							$contentStr = "回复回复格式如下：\nbdzh#学号#密码\n进行绑定帐号";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "修改帐号":
							$contentStr = "回复回复格式如下：\nbdzh#学号#密码\n进行修改帐号";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "帮助":
							$contentStr = "回复：\n'考试成绩' 查询考试成绩\n'考试时间' 查询考试安排\n\n回复格式如下：\nbdzh#学号#密码\n进行绑定或修改帐号";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "图文":
							//回复图文消息,ArticleCount图文消息个数,多条图文消息信息，默认第一个item为大图
							$ArticleCount = 3; 
							$textTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[%s]]></MsgType>
								<ArticleCount>%s</ArticleCount>
								<Articles>
								<item>
								<Title><![CDATA[%s]]></Title> 
								<Description><![CDATA[%s]]></Description>
								<PicUrl><![CDATA[%s]]></PicUrl>
								<Url><![CDATA[%s]]></Url>
								</item>
								<item>
								<Title><![CDATA[%s]]></Title>
								<Description><![CDATA[%s]]></Description>
								<PicUrl><![CDATA[%s]]></PicUrl>
								<Url><![CDATA[%s]]></Url>
								</item>
								<item>
								<Title><![CDATA[%s]]></Title>
								<Description><![CDATA[%s]]></Description>
								<PicUrl><![CDATA[%s]]></PicUrl>
								<Url><![CDATA[%s]]></Url>
								</item>
								</Articles>
								</xml>";
								$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "news", 
			                				$ArticleCount,'图文1','图文描述1','http://www.i3geek.com/wp-content/themes/HelloMetro/images/Footer.png',
			                				'http://www.i3geek.com','图文2','图文描述2','http://www.i3geek.com/wp-content/themes/HelloMetro/images/Footer.png',
			                				'http://www.i3geek.com','图文3','图文描述3','http://www.i3geek.com/wp-content/themes/HelloMetro/images/Footer.png',
			                				'http://www.i3geek.com');
							break;
						default:
							$contentStr = "更多请回复'帮助'";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
					}
					
                	echo $resultStr;
                }else{
                	//没有keyword
                }
					
					
					
					
			}
			/**********************
				   如果是事件
			**********************/
			else if($MsgType == 'event'){
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
				$Event = $postObj->Event;
	            /*************************
					关注（订阅）
				*************************/
	            if($Event == 'subscribe'){
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'text', "欢迎关注，回复'帮助'查看更多！");
		            echo $resultStr;
					
	                $EventKey = $postObj->EventKey;//事件KEY值，qrscene_为前缀，后面为二维码的参数值
	                if(!empty($EventKey)){//未关注时，扫描二维码
	                	$Ticket = $postObj->Ticket;//二维码的ticket，可用来换取二维码图片
		            }else{
	                	//正常关注
	                }
	            /*************************
					取消关注（取消订阅）
				*************************/		
	            }else if ($Event == 'unsubscribe'){
	                //取消关注了
				}
	            /*************************
					已关注时，扫描二维码事件
				*************************/    		
	            else if($Event == 'SCAN' || $Event == 'scan'){
	                $EventKey = $postObj->EventKey;//事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id
                	$Ticket = $postObj->Ticket;//二维码的ticket，可用来换取二维码图片
				}
	            /*************************
					菜单点击事件
				*************************/    
	            else if($Event == 'CLICK'){
	                $EventKey = $postObj->EventKey;//事件KEY值，与自定义菜单接口中KEY值对应
					
					/***********判断按钮**************/
					switch($EventKey)
					{
						case "考试时间":
							
							//连接数据库
							$conn =mysql_connect("hdm-104.hichina.com","hdm1040866","a1bb5366") or die("connect failed" . mysql_error()); 
							mysql_select_db("hdm1040866_db",$conn);
							
							//读取表中纪录条数
							$sql = sprintf("select count(*) from %s where openid='%s'", "weixin",$fromUsername); 
							$result = mysql_query($sql, $conn);
							if ($result)
								$count = mysql_fetch_row($result);  
							else  
								die("query failed"); 
									
							if($count[0] == 0)//没有
							{
								$contentStr = '请绑定帐户';
							}
							else //有，进行查找
							{
								$sql = sprintf("select name,pass from %s where openid='%s'", "weixin",$fromUsername); 
								$result_name = mysql_query($sql, $conn);
								if ($result_name)
									$name = mysql_fetch_row($result_name);  
								else  
									die("query failed"); 
								$contentStr = "学号".$name[0]." <a href=\"http://www.i3geek.com/hrbust/newpage.php?cmd=1&n=".$name[0]."&p=".$name[1]."\">立即查看考试时间</a>";
							}
							
							//关闭mysql
							mysql_free_result($result_name);
							mysql_free_result($result); 
							mysql_close($conn);
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "考试成绩":
							//连接数据库
							$conn =mysql_connect("hdm-104.hichina.com","hdm1040866","a1bb5366") or die("connect failed" . mysql_error()); 
							mysql_select_db("hdm1040866_db",$conn);
							
							//读取表中纪录条数
							$sql = sprintf("select count(*) from %s where openid='%s'", "weixin",$fromUsername); 
							$result = mysql_query($sql, $conn);
							if ($result)
								$count = mysql_fetch_row($result);  
							else  
								die("query failed"); 
									
							if($count[0] == 0)//没有
							{
								$contentStr = '请绑定帐户';
							}
							else //有，进行查找
							{
								$sql = sprintf("select name,pass from %s where openid='%s'", "weixin",$fromUsername); 
								$result_name = mysql_query($sql, $conn);
								if ($result_name)
									$name = mysql_fetch_row($result_name);  
								else  
									die("query failed"); 
								$contentStr = "学号".$name[0]." <a href=\"http://www.i3geek.com/hrbust/newpage.php?cmd=2&n=".$name[0]."&p=".$name[1]."\">立即查看考试成绩</a>";
							}
							
							//关闭mysql
							mysql_free_result($result_name);
							mysql_free_result($result); 
							mysql_close($conn);
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "绑定帐号":
							$contentStr = "回复回复格式如下：\nbdzh#学号#密码\n进行绑定帐号";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "修改帐号":
							$contentStr = "回复回复格式如下：\nbdzh#学号#密码\n进行修改帐号";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						case "帮助":
							$contentStr = "回复：\n'考试成绩' 查询考试成绩\n'考试时间' 查询考试安排\n\n回复格式如下：\nbdzh#学号#密码\n进行绑定或修改帐号";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
							break;
						default:
							$contentStr = "更多请回复'帮助'";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "text", $contentStr);
						break;
					}
		            echo $resultStr;
					
	            }else{
	                //其他事件类型
	            }
			}
			/*************************
				不是事件也不是文本
			*************************/  
		}else {
		//空post
			echo "";
			exit;
		}
    }
	
	

	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}
?>