<?php
//访问教务在线
/**
 * @author Garry_闫庚哲
 */
define('SCRIPT_ROOT',dirname(__FILE__).'/');
//header("Content-Type: text/html; charset=utf-8");

$cookieFile = SCRIPT_ROOT.'cookie.tmp';// $cookieFile 为加载验证码时保存的cookie文件名 

$act = trim($_REQUEST['act']);//判断状态
$j_username = trim($_REQUEST['j_username']);
$j_password = trim($_REQUEST['j_password']);
	
switch($act)
{
    case 'login':
        // 获取验证码
        $code = trim($_REQUEST['code']);
        // $loginParams为curl模拟登录时post的参数
        $loginParams= 'j_username='.$j_username.'&j_password='.$j_password.'&j_captcha='.$code;
        // $targetUrl curl 提交的目标地址
        $targetUrl = 'http://202.118.201.228/academic/j_acegi_security_check';
        // 参数重置
        $content = curlLogin($targetUrl, $cookieFile, $loginParams);
		if($content == true)
		{//登陆成功
		copy($cookieFile , SCRIPT_ROOT.$j_username.'.tmp'); //保存Cookie
		//考试成绩
		//get_examscore("http://127.0.0.1/score.html",$cookieFile);
		get_examscore("http://202.118.201.228/academic/manager/score/studentOwnScore.do",$cookieFile);
		echo '<br><br>';
		//考试时间
		get_examtime("http://202.118.201.228/academic/student/exam/index.jsdo",$cookieFile);		
		
		
		/*
			//获得课表
			echo getcontent("http://202.118.201.228/academic/manager/coursearrange/showTimetable.do?id=279295&yearid=34&termid=2&timetableType=STUDENT&sectionType=BASE",$cookieFile);
		*/
		
		}
		else
			echo "<font color = \"red\"> 登录失败！</font>";
    break;
    case 'authcode':
        // Content-Type 验证码的图片类型
        header('Content-Type:image/png');
        showAuthcode('http://202.118.201.228/academic/getCaptcha.do',$cookieFile);
        exit;
    break;
}

/**
 * 模拟登录
 * @param string $url 提交到的地址
 * @param string $cookieFile 保存cookie的文件
 * @param string $loginParams 提交时要post的参数
 * @return boolean $pos 登陆是否成功
 */
function curlLogin($url, $cookieFile, $loginParams)
{
    $ch = curl_init($url);
	curl_setopt($ch,CURLOPT_HEADER, 1); 
    curl_setopt($ch,CURLOPT_COOKIEFILE, $cookieFile); //同时发送Cookie
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $loginParams); //提交查询信息
    $content = curl_exec($ch);
    curl_close($ch);
	
	$pos= stripos($content, "http://202.118.201.228/academic/index_new.jsp");//判断是否存在登陆成功的转换
	if($pos)
		return true;
	else return false;
}

/**
 * 数据采集
 * @param string $url 提交到的地址
 * @param string $cookieFile 保存cookie的文件
 * @return string $content 返回的内容
 */
function getContent($url, $cookieFile)
{
    $ch = curl_init($url);
	curl_setopt($ch,CURLOPT_HEADER, 0); 
    curl_setopt($ch,CURLOPT_COOKIEFILE, $cookieFile); //同时发送Cookie
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
	
	//return charsetToUTF8($content);
	return $content;
}
/**
 * 编码转换成utf8的格式
 * @param string $mixed 带转换
 * @return string $mixed 转换内容
 */
function charsetToUTF8($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToUTF8($v);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'EUC-CN') {
                    $mixed[$k] = iconv('GBK', 'UTF-8', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        if ($encode == 'EUC-CN') {
            $mixed = iconv('GBK', 'UTF-8', $mixed);
        }
    }
    return $mixed;
}
/**
 * 加载目标网站图片验证码
 * @param string $authcode_url 目标网站验证码地址
 */
function showAuthcode( $authcode_url ,$cookieFile )
{
    $ch = curl_init($authcode_url);
    curl_setopt($ch,CURLOPT_COOKIEJAR, $cookieFile); // 把返回来的cookie信息保存在文件中
    curl_exec($ch);
    curl_close($ch);
}
/**
 * 格式化转换后的二维数组
 * @param string $table 待转换的数据
 */
function format_array($table)
{
	$array = get_td_array($table);
	$str = var_export($array,true);
	$result = eval('return '.iconv('gbk','utf-8',$str).';');
	return $result;
}
/**
 * 转换html表格为二维数组
 * @param string $table 待转换的数据
 */
function get_td_array($table)
{
	$td_array='';
	$table = preg_replace("(;\d\d\d)","",$table);//去除教室前的数字
	$table = str_replace("th","td",$table);
	//$table = str_replace("<","",$table);
	//$table = str_replace(">","",$table);	
	//$table = str_replace(" ","",$table);
	$table = preg_replace("'<table[^>]*?>'si","",$table);
	$table = preg_replace("'<tr[^>]*?>'si","",$table);
	$table = preg_replace("'<td[^>]*?>'si","",$table);
	$table = str_replace("</tr>","{tr}",$table);
	$table = str_replace("</td>","{td}",$table);
	//去掉html标记
	$table = preg_replace("'<[/!]*?[^<>]*?>'si","",$table); 
	//去掉空白字符
	$table = preg_replace("'([rn])[s]+'","",$table);
	//$table = str_replace(" ","",$table);
	//$table = str_replace("\n","",$table);
	//$table = str_replace(" ","",$table);
	
	$table = explode('{tr}', $table);
	array_pop($table);
	foreach($table as $key=>$tr){
		$td = explode('{td}',$tr);
		array_pop($td);
		$td_array[] = $td;
	}
	return $td_array;
}

/**
 * 获得考试时间
 * @param string $url 网址
 */
function get_examtime($url, $cookieFile)
{
	$table = getcontent($url,$cookieFile);
	$examtime = format_array($table);
	echo '<table border=1>';
	echo '<caption>考试时间</caption>';
	foreach ($examtime as $key => $value)
	{
		if($key <1 || $key>= (count($examtime)-1) ) continue;
		echo '<tr>';
		echo '<td>'.$value[0].'</td>';
		echo '<td>'.$value[1].'</td>';
		echo '<td>'.$value[2].'</td>';
		echo '<td>'.$value[3].'</td>';
		echo '<td>'.$value[4].'</td>';
		echo '</tr>';
	}
	echo '</table>';
}

/**
 * 获得考试成绩
 * @param string $url 网址
 */
function get_examscore($url, $cookieFile)
{
	$table = getcontent($url,$cookieFile);
	$examscore = get_td_array($table);
	if(count($examscore)<2) {
	echo '无考试成绩';
	return;
	}
	
	echo '<table border=1>';
	echo '<caption>考试成绩</caption>';
	foreach ($examscore as $key => $value)
	{
		if($key <1) continue;
		echo '<tr>';
		echo '<td>'.$value[0].'</td>';
		echo '<td>'.$value[1].'</td>';
		echo '<td>'.$value[3].'</td>';
		if(intval($value[6]) < 60 && $key>1)
			echo "<td><font color = \"red\">".$value[6]."</font></td>";
		else echo '<td>'.$value[6].'</td>';
		echo '<td>'.$value[7].'</td>';
		echo '<td>'.$value[8].'</td>';
		echo '<td>'.$value[9].'</td>';
		echo '<td>'.$value[10].'</td>';
		echo '<td>'.$value[11].'</td>';
		echo '<td>'.$value[12].'</td>';
		echo '</tr>';
	}
	echo '</table>';
}





?>

<!--
	页面显示布局设置
-->
<head>        
<Meta http-equiv="Content-Type" Content="text/html; Charset=utf-8">
<Meta http-equiv="Content-Language" Content="zh-CN">
</head>
<form method ="post">
	<table>
		<tr>
			<table>
				<tr>
					<input type="hidden" name="act" value="login">
				</tr>
				
				<tr>
					<td width="80px">用户名：</td>
					<td><input type="text" name="j_username" id="name" value=<?=$j_username ?> ></td>
				</tr>
				
				<tr>
					<td>密　码：</td>
					<td><input type="password" name="j_password" id="password" value=<?=$j_password ?> ></td>
				</tr>
				
				<tr>
					<td>验证码：</td>
					<td><input type="text" name="code" /></td>
					<td><iframe src="?act=authcode" style='width: 100px; height:40px ' frameborder=0 name="code"></iframe></td>
					<td><input type="button" value="刷新图片" onclick="top.frames['code'].location.reload()"></td>
				</tr>
			</table>
		</tr>
		<tr align="center">
			<td><input type="submit" name="submit" value="确定"></td>
			<td><input type="button" value="默认登陆" onclick="a();"></td>
		</tr>
	</table>
</form>

<A href="newpage.php?act=login&n=1214010330" rel="external nofollow" target="_blank">测试</a>

<script language="JavaScript">
function a(){
	document.getElementById("name").value = "1214010330";
	document.getElementById("password").value = "a1bb5366";
}
</script>

