<?php  
    //连接数据库
	$openid=1;
						$conn =mysql_connect("hdm-104.hichina.com","hdm1040866","a1bb5366") or die("connect failed" . mysql_error()); 
						mysql_select_db("hdm1040866_db",$conn);
	
						//读取表中纪录条数
						$sql = sprintf("select count(*) from %s where openid='%s'", "weixin",$openid); 
						$result = mysql_query($sql, $conn);
						if ($result)
							$count = mysql_fetch_row($result);  
						else  
							die("query failed"); 
							
						if($count[0] == 0)
						{
						$sql = sprintf("INSERT INTO weixin (openid,name,pass) VALUES ('%s',null,null) ",$openid); 
						mysql_query($sql, $conn);
							echo '无';
							}
						else echo '有';
						
						//关闭mysql
						mysql_free_result($result); 
						mysql_close($conn);
?>