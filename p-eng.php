<?php
/* ----------------本探针基于YaHei.net探针------------------- */
error_reporting(0); //抑制所有错误信息
@header("content-Type: text/html; charset=utf-8"); //语言强制
ob_start();

$title = "Info Page";
$version = "v0.4.2"; //版本号

define('HTTP_HOST', preg_replace('~^www\.~i', '', $_SERVER['HTTP_HOST']));

$time_start = microtime_float();

function memory_usage() 
{
	$memory	 = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
	return $memory;
}

// 计时
function microtime_float() 
{
	$mtime = microtime();
	$mtime = explode(' ', $mtime);
	return $mtime[1] + $mtime[0];
}

//单位转换
function formatsize($size) 
{
	$danwei=array(' B ',' K ',' M ',' G ',' T ');
	$allsize=array();
	$i=0;

	for($i = 0; $i <4; $i++) 
	{
		if(floor($size/pow(1024,$i))==0){break;}
	}

	for($l = $i-1; $l >=0; $l--) 
	{
		$allsize1[$l]=floor($size/pow(1024,$l));
		$allsize[$l]=$allsize1[$l]-$allsize1[$l+1]*1024;
	}

	$len=count($allsize);

	for($j = $len-1; $j >=0; $j--) 
	{
		$strlen = 4-strlen($allsize[$j]);
		if($strlen==1)
			$allsize[$j] = "<font color='#FFFFFF'>0</font>".$allsize[$j];
		elseif($strlen==2)
			$allsize[$j] = "<font color='#FFFFFF'>00</font>".$allsize[$j];
		elseif($strlen==3)
			$allsize[$j] = "<font color='#FFFFFF'>000</font>".$allsize[$j];

		$fsize=$fsize.$allsize[$j].$danwei[$j];
	}	
	return $fsize;
}

function valid_email($str) 
{
	return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
}

//检测PHP设置参数
function show($varName)
{
	switch($result = get_cfg_var($varName))
	{
		case 0:
			return '<font color="red">×</font>';
		break;
		
		case 1:
			return '<font color="green">√</font>';
		break;
		
		default:
			return $result;
		break;
	}
}


if ($_GET['act'] == "phpinfo") 
{
	phpinfo();
	exit();
} 
elseif($_GET['act'] == "Function")
{
	$arr = get_defined_functions();
	Function php()
	{
	}
	echo "<pre>";
	Echo "This shows all the functions supported by the system, including custom functions.\n";
	print_r($arr);
	echo "</pre>";
	exit();
}elseif($_GET['act'] == "disable_functions")
{
	$disFuns=get_cfg_var("disable_functions");
	if(empty($disFuns))
	{
		$arr = '<font color=red>×</font>';
	}
	else
	{ 
		$arr = $disFuns;
	}
	Function php()
	{
	}
	echo "<pre>";
	Echo "Here shows all functions that system disabled.\n";
	print_r($arr);
	echo "</pre>";
	exit();
}

//MySQL检测
if ($_POST['act'] == 'MySQL Check')
{
	$host = isset($_POST['host']) ? trim($_POST['host']) : '';
	$port = isset($_POST['port']) ? (int) $_POST['port'] : '';
	$login = isset($_POST['login']) ? trim($_POST['login']) : '';
	$password = isset($_POST['password']) ? trim($_POST['password']) : '';
	$host = preg_match('~[^a-z0-9\-\.]+~i', $host) ? '' : $host;
	$port = intval($port) ? intval($port) : '';
	$login = preg_match('~[^a-z0-9\_\-]+~i', $login) ? '' : htmlspecialchars($login);
	$password = is_string($password) ? htmlspecialchars($password) : '';
}
elseif ($_POST['act'] == 'Function Check')
{
	$funRe = "Function".$_POST['funName']."Support of test results:".isfun1($_POST['funName']);
} 
elseif ($_POST['act'] == 'Mail Check')
{
	$mailRe = "Send E-mail success!";
	if($_SERVER['SERVER_PORT']==80){$mailContent = "http://".$_SERVER['SERVER_NAME'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);}
	else{$mailContent = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);}
	$mailRe .= (false !== @mail($_POST["mailAdd"], $mailContent, "This is a test mail!\n\nhttp://lnmp.org")) ? "Success!":"Failed!";
}	
	
// 检测函数支持
function isfun($funName = '')
{
    if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return 'Error!';
	return (false !== function_exists($funName)) ? '<font color="green">√</font>' : '<font color="red">×</font>';
}
function isfun1($funName = '')
{
    if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return 'Error!';
	return (false !== function_exists($funName)) ? '√' : '×';
}

// 根据不同系统取得CPU相关信息
switch(PHP_OS)
{
	case "Linux":
		$sysReShow = (false !== ($sysInfo = sys_linux()))?"show":"none";
	break;
	
	case "FreeBSD":
		$sysReShow = (false !== ($sysInfo = sys_freebsd()))?"show":"none";
	break;
	
/*	
	case "WINNT":
		$sysReShow = (false !== ($sysInfo = sys_windows()))?"show":"none";
	break;
*/	
	
	default:
	break;
}

//linux系统探测
function sys_linux()
{
    // CPU
    if (false === ($str = @file("/proc/cpuinfo"))) return false;
    $str = implode("", $str);
    @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
    @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
    @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
    @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
    if (false !== is_array($model[1]))
	{
        $res['cpu']['num'] = sizeof($model[1]);
		/*
        for($i = 0; $i < $res['cpu']['num']; $i++)
        {
            $res['cpu']['model'][] = $model[1][$i].'&nbsp;('.$mhz[1][$i].')';
            $res['cpu']['mhz'][] = $mhz[1][$i];
            $res['cpu']['cache'][] = $cache[1][$i];
            $res['cpu']['bogomips'][] = $bogomips[1][$i];
        }*/
		if($res['cpu']['num']==1)
			$x1 = '';
		else
			$x1 = ' ×'.$res['cpu']['num'];
		$mhz[1][0] = ' | Frequency:'.$mhz[1][0];
		$cache[1][0] = ' | L2 CACHE:'.$cache[1][0];
		$bogomips[1][0] = ' | Bogomips:'.$bogomips[1][0];
		$res['cpu']['model'][] = $model[1][0].$mhz[1][0].$cache[1][0].$bogomips[1][0].$x1;
        if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);
        if (false !== is_array($res['cpu']['mhz'])) $res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);
        if (false !== is_array($res['cpu']['cache'])) $res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);
        if (false !== is_array($res['cpu']['bogomips'])) $res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);
	}

    // NETWORK

    // UPTIME
    if (false === ($str = @file("/proc/uptime"))) return false;
    $str = explode(" ", implode("", $str));
    $str = trim($str[0]);
    $min = $str / 60;
    $hours = $min / 60;
    $days = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0) $res['uptime'] = $days." Day(s) ";
    if ($hours !== 0) $res['uptime'] .= $hours." Hour(s) ";
    $res['uptime'] .= $min." Minute(s) ";

    // MEMORY
    if (false === ($str = @file("/proc/meminfo"))) return false;
    $str = implode("", $str);
    preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
	preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

    $res['memTotal'] = round($buf[1][0]/1024, 2);
    $res['memFree'] = round($buf[2][0]/1024, 2);
    $res['memBuffers'] = round($buffers[1][0]/1024, 2);
	$res['memCached'] = round($buf[3][0]/1024, 2);
    $res['memUsed'] = $res['memTotal']-$res['memFree'];
    $res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;

    $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
	$res['memRealFree'] = $res['memTotal'] - $res['memRealUsed']; //真实空闲
    $res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0; //真实内存使用率

	$res['memCachedPercent'] = (floatval($res['memCached'])!=0)?round($res['memCached']/$res['memTotal']*100,2):0; //Cached内存使用率

    $res['swapTotal'] = round($buf[4][0]/1024, 2);
    $res['swapFree'] = round($buf[5][0]/1024, 2);
    $res['swapUsed'] = round($res['swapTotal']-$res['swapFree'], 2);
    $res['swapPercent'] = (floatval($res['swapTotal'])!=0)?round($res['swapUsed']/$res['swapTotal']*100,2):0;

    // LOAD AVG
    if (false === ($str = @file("/proc/loadavg"))) return false;
    $str = explode(" ", implode("", $str));
    $str = array_chunk($str, 4);
    $res['loadAvg'] = implode(" ", $str[0]);

    return $res;
}

//FreeBSD系统探测
function sys_freebsd()
{
	//CPU
	if (false === ($res['cpu']['num'] = get_key("hw.ncpu"))) return false;
	$res['cpu']['model'] = get_key("hw.model");
	//LOAD AVG
	if (false === ($res['loadAvg'] = get_key("vm.loadavg"))) return false;
	//UPTIME
	if (false === ($buf = get_key("kern.boottime"))) return false;
	$buf = explode(' ', $buf);
	$sys_ticks = time() - intval($buf[3]);
	$min = $sys_ticks / 60;
	$hours = $min / 60;
	$days = floor($hours / 24);
	$hours = floor($hours - ($days * 24));
	$min = floor($min - ($days * 60 * 24) - ($hours * 60));
	if ($days !== 0) $res['uptime'] = $days."Days";
	if ($hours !== 0) $res['uptime'] .= $hours."Hours";
	$res['uptime'] .= $min."Minutes";
	//MEMORY
	if (false === ($buf = get_key("hw.physmem"))) return false;
	$res['memTotal'] = round($buf/1024/1024, 2);

	$str = get_key("vm.vmtotal");
	preg_match_all("/\nVirtual Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buff, PREG_SET_ORDER);
	preg_match_all("/\nReal Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buf, PREG_SET_ORDER);

	$res['memRealUsed'] = round($buf[0][2]/1024, 2);
	$res['memCached'] = round($buff[0][2]/1024, 2);
	$res['memUsed'] = round($buf[0][1]/1024, 2) + $res['memCached'];
	$res['memFree'] = $res['memTotal'] - $res['memUsed'];
	$res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memUsed']/$res['memTotal']*100,2):0;

	$res['memRealPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0;

	return $res;
}

//取得参数值 FreeBSD
function get_key($keyName)
{
	return do_command('sysctl', "-n $keyName");
}

//确定执行文件位置 FreeBSD
function find_command($commandName)
{
	$path = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
	foreach($path as $p) 
	{
		if (@is_executable("$p/$commandName")) return "$p/$commandName";
	}
	return false;
}

//执行系统命令 FreeBSD
function do_command($commandName, $args)
{
	$buffer = "";
	if (false === ($command = find_command($commandName))) return false;
	if ($fp = @popen("$command $args", 'r')) 
	{
		while (!@feof($fp))
		{
			$buffer .= @fgets($fp, 4096);
		}
		return trim($buffer);
	}
	return false;
}

//windows系统探测
function sys_windows()
{
	if (PHP_VERSION >= 5)
	{
		$objLocator = new COM("WbemScripting.SWbemLocator");
		$wmi = $objLocator->ConnectServer();
		$prop = $wmi->get("Win32_PnPEntity");
	}
	else
	{
		return false;
	}

	//CPU
	$cpuinfo = GetWMI($wmi,"Win32_Processor", array("Name","L2CacheSize","NumberOfCores"));
	$res['cpu']['num'] = $cpuinfo[0]['NumberOfCores'];
	if (null == $res['cpu']['num']) 
	{
		$res['cpu']['num'] = 1;
	}/*
	for ($i=0;$i<$res['cpu']['num'];$i++)
	{
		$res['cpu']['model'] .= $cpuinfo[0]['Name']."<br />";
		$res['cpu']['cache'] .= $cpuinfo[0]['L2CacheSize']."<br />";
	}*/
	$cpuinfo[0]['L2CacheSize'] = ' ('.$cpuinfo[0]['L2CacheSize'].')';
	if($res['cpu']['num']==1)
		$x1 = '';
	else
		$x1 = ' ×'.$res['cpu']['num'];
	$res['cpu']['model'] = $cpuinfo[0]['Name'].$cpuinfo[0]['L2CacheSize'].$x1;
	// SYSINFO
	$sysinfo = GetWMI($wmi,"Win32_OperatingSystem", array('LastBootUpTime','TotalVisibleMemorySize','FreePhysicalMemory','Caption','CSDVersion','SerialNumber','InstallDate'));
	$sysinfo[0]['Caption']=iconv('GBK', 'UTF-8',$sysinfo[0]['Caption']);
	$sysinfo[0]['CSDVersion']=iconv('GBK', 'UTF-8',$sysinfo[0]['CSDVersion']);
	$res['win_n'] = $sysinfo[0]['Caption']." ".$sysinfo[0]['CSDVersion']." Serial Number:{$sysinfo[0]['SerialNumber']} At".date('Y-m-d H:i:s',strtotime(substr($sysinfo[0]['InstallDate'],0,14)))."Install";
	//UPTIME
	$res['uptime'] = $sysinfo[0]['LastBootUpTime'];

	$sys_ticks = 3600*8 + time() - strtotime(substr($res['uptime'],0,14));
	$min = $sys_ticks / 60;
	$hours = $min / 60;
	$days = floor($hours / 24);
	$hours = floor($hours - ($days * 24));
	$min = floor($min - ($days * 60 * 24) - ($hours * 60));
	if ($days !== 0) $res['uptime'] = $days."Days";
	if ($hours !== 0) $res['uptime'] .= $hours."Hours";
	$res['uptime'] .= $min."Minutes";

	//MEMORY
	$res['memTotal'] = round($sysinfo[0]['TotalVisibleMemorySize']/1024,2);
	$res['memFree'] = round($sysinfo[0]['FreePhysicalMemory']/1024,2);
	$res['memUsed'] = $res['memTotal']-$res['memFree'];	//上面两行已经除以1024,这行不用再除了
	$res['memPercent'] = round($res['memUsed'] / $res['memTotal']*100,2);

	$swapinfo = GetWMI($wmi,"Win32_PageFileUsage", array('AllocatedBaseSize','CurrentUsage'));

	// LoadPercentage
	$loadinfo = GetWMI($wmi,"Win32_Processor", array("LoadPercentage"));
	$res['loadAvg'] = $loadinfo[0]['LoadPercentage'];

	return $res;
}

function GetWMI($wmi,$strClass, $strValue = array())
{
	$arrData = array();

	$objWEBM = $wmi->Get($strClass);
	$arrProp = $objWEBM->Properties_;
	$arrWEBMCol = $objWEBM->Instances_();
	foreach($arrWEBMCol as $objItem) 
	{
		@reset($arrProp);
		$arrInstance = array();
		foreach($arrProp as $propItem) 
		{
			eval("\$value = \$objItem->" . $propItem->Name . ";");
			if (empty($strValue)) 
			{
				$arrInstance[$propItem->Name] = trim($value);
			} 
			else
			{
				if (in_array($propItem->Name, $strValue)) 
				{
					$arrInstance[$propItem->Name] = trim($value);
				}
			}
		}
		$arrData[] = $arrInstance;
	}
	return $arrData;
}

//比例条
function bar($percent)
{
?>
	<div class="bar"><div class="barli" style="width:<?php echo $percent?>%">&nbsp;</div></div>
<?php
}

$uptime = $sysInfo['uptime'];
$stime = date("Y-n-j H:i:s");
$df = round(@disk_free_space(".")/(1024*1024*1024),3);
$dt = round(@disk_total_space(".")/(1024*1024*1024),3);

$load = $sysInfo['loadAvg'];	//系统负载


//判断内存如果小于1GB，就显示M，否则显示GB单位
if($sysInfo['memTotal']<1024)
{
	$memTotal = $sysInfo['memTotal']." MB";
	$mt = $sysInfo['memTotal']." MB";
	$mu = $sysInfo['memUsed']." MB";
	$mf = $sysInfo['memFree']." MB";
	$mc = $sysInfo['memCached']." MB";	//cache化内存
	$mb = $sysInfo['memBuffers']." MB";	//缓冲
	$st = $sysInfo['swapTotal']." MB";
	$su = $sysInfo['swapUsed']." MB";
	$sf = $sysInfo['swapFree']." MB";
	$swapPercent = $sysInfo['swapPercent'];
	$memRealUsed = $sysInfo['memRealUsed']." MB"; //真实内存使用
	$memRealFree = $sysInfo['memRealFree']." MB"; //真实内存空闲
	$memRealPercent = $sysInfo['memRealPercent']; //真实内存使用比率
	$memPercent = $sysInfo['memPercent']; //内存总使用率
	$memCachedPercent = $sysInfo['memCachedPercent']; //cache内存使用率
}
else
{
	$memTotal = round($sysInfo['memTotal']/1024,3)." GB";
	$mt = round($sysInfo['memTotal']/1024,3)." GB";
	$mu = round($sysInfo['memUsed']/1024,3)." GB";
	$mf = round($sysInfo['memFree']/1024,3)." GB";
	$mc = round($sysInfo['memCached']/1024,3)." GB";
	$mb = round($sysInfo['memBuffers']/1024,3)." GB";
	$st = round($sysInfo['swapTotal']/1024,3)." GB";
	$su = round($sysInfo['swapUsed']/1024,3)." GB";
	$sf = round($sysInfo['swapFree']/1024,3)." GB";
	$swapPercent = $sysInfo['swapPercent'];
	$memRealUsed = round($sysInfo['memRealUsed']/1024,3)." GB"; //真实内存使用
	$memRealFree = round($sysInfo['memRealFree']/1024,3)." GB"; //真实内存空闲
	$memRealPercent = $sysInfo['memRealPercent']; //真实内存使用比率
	$memPercent = $sysInfo['memPercent']; //内存总使用率
	$memCachedPercent = $sysInfo['memCachedPercent']; //cache内存使用率
}

//网卡流量
$strs = @file("/proc/net/dev"); 

for ($i = 2; $i < count($strs); $i++ )
{
	preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );
/*	$NetInput[$i] = formatsize($info[2][0]);
	$NetOut[$i]  = formatsize($info[10][0]);
*/ 
	$tmo = round($info[2][0]/1024/1024, 5); 
	$tmo2 = round($tmo / 1024, 5);
	$NetInput[$i] = $tmo2;
	$tmp = round($info[10][0]/1024/1024, 5); 
	$tmp2 = round($tmp / 1024, 5);
	$NetOut[$i] = $tmp2;

}

//ajax调用实时刷新
if ($_GET['act'] == "rt")
{
	$arr=array('freeSpace'=>"$df",'TotalMemory'=>"$mt",'UsedMemory'=>"$mu",'FreeMemory'=>"$mf",'CachedMemory'=>"$mc",'Buffers'=>"$mb",'TotalSwap'=>"$st",'swapUsed'=>"$su",'swapFree'=>"$sf",'loadAvg'=>"$load",'uptime'=>"$uptime",'freetime'=>"$freetime",'bjtime'=>"$bjtime",'stime'=>"$stime",'memRealPercent'=>"$memRealPercent",'memRealUsed'=>"$memRealUsed",'memRealFree'=>"$memRealFree",'memPercent'=>"$memPercent%",'memCachedPercent'=>"$memCachedPercent",'barmemCachedPercent'=>"$memCachedPercent%",'swapPercent'=>"$swapPercent",'barmemRealPercent'=>"$memRealPercent%",'barswapPercent'=>"$swapPercent%",'NetOut2'=>"$NetOut[2]",'NetOut3'=>"$NetOut[3]",'NetOut4'=>"$NetOut[4]",'NetOut5'=>"$NetOut[5]",'NetOut6'=>"$NetOut[6]",'NetOut7'=>"$NetOut[7]",'NetOut8'=>"$NetOut[8]",'NetOut9'=>"$NetOut[9]",'NetOut10'=>"$NetOut[10]",'NetInput2'=>"$NetInput[2]",'NetInput3'=>"$NetInput[3]",'NetInput4'=>"$NetInput[4]",'NetInput5'=>"$NetInput[5]",'NetInput6'=>"$NetInput[6]",'NetInput7'=>"$NetInput[7]",'NetInput8'=>"$NetInput[8]",'NetInput9'=>"$NetInput[9]",'NetInput10'=>"$NetInput[10]");
	$jarr=json_encode($arr); 
	echo $_GET['callback'],'(',$jarr,')';
	exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Server Info</title>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- Powered by: Yahei.Net -->
<style type="text/css">
<!--
* {font-family: Tahoma, "Microsoft Yahei", Arial; }
body{text-align: center; margin: 0 auto; padding: 0; background-color:#FFFFFF;font-size:12px;font-family:Tahoma, Arial}
h1 {font-size: 26px; font-weight: normal; padding: 0; margin: 0; color: #444444;}
h1 small {font-size: 11px; font-family: Tahoma; font-weight: bold; }
a{color: #000000; text-decoration:none;}
a.black{color: #000000; text-decoration:none;}
b{color: #999999;}
table{clear:both;padding: 0; margin: 0 0 10px;border-collapse:collapse; border-spacing: 0;}
th{padding: 3px 6px; font-weight:bold;background:#3066a6;color:#FFFFFF;border:1px solid #3066a6; text-align:left;}
.th_1{padding: 3px 6px; font-weight:bold;background:#666699;color:#FFFFFF;border:1px solid #3066a6; text-align:left;}
.th_2{padding: 3px 6px; font-weight:bold;background:#417291;color:#FFFFFF;border:1px solid #3066a6; text-align:left;}
.th_3{padding: 3px 6px; font-weight:bold;background:#067201;color:#FFFFFF;border:1px solid #3066a6; text-align:left;}
.th_4{padding: 3px 6px; font-weight:bold;background:#666666;color:#FFFFFF;border:1px solid #CCCCCC; text-align:left;}
.th_5{padding: 3px 6px; font-weight:bold;background:#333333;color:#FFFFFF;border:1px solid #CCCCCC; text-align:left;}
.th_6{padding: 3px 6px; font-weight:bold;background:#FF6600;color:#FFFFFF;border:1px solid #FF6600; text-align:left;}
tr{padding: 0; background:#F7F7F7;}
td{padding: 3px 6px; border:1px solid #CCCCCC;}
input{padding: 2px; background: #FFFFFF; border-top:1px solid #666666; border-left:1px solid #666666; border-right:1px solid #CCCCCC; border-bottom:1px solid #CCCCCC; font-size:12px}
input.btn{font-weight: bold; height: 20px; line-height: 20px; padding: 0 6px; color:#666666; background: #f2f2f2; border:1px solid #999;font-size:12px}
.bar {border:1px solid #999999; background:#FFFFFF; height:5px; font-size:2px; width:89%; margin:2px 0 5px 0;padding:1px;overflow: hidden;}
.bar_1 {border:1px dotted #999999; background:#FFFFFF; height:5px; font-size:2px; width:89%; margin:2px 0 5px 0;padding:1px;overflow: hidden;}
.barli_red{background:#ff6600; height:5px; margin:0px; padding:0;}
.barli_blue{background:#0099FF; height:5px; margin:0px; padding:0;}
.barli_green{background:#36b52a; height:5px; margin:0px; padding:0;}
.barli_1{background:#999999; height:5px; margin:0px; padding:0;}
.barli{background:#36b52a; height:5px; margin:0px; padding:0;}
#page {width: 920px; padding: 0 20px; margin: 0 auto; text-align: left;}
#header{position: relative; padding: 10px;}
#footer {padding: 15px 0; text-align: center; font-size: 11px; font-family: Tahoma, Verdana;}
#lnmplink {position: absolute; top: 20px; left: 115px; text-align: right; font-weight: bold; color: #06C;}
#lnmplink a {color: #0000FF; text-decoration: underline;}
.w_small{font-family: Courier New;}
.w_number{color: #f800fe;}
.sudu {padding: 0; background:#5dafd1; }
.suduk { margin:0px; padding:0;}
.resYes{}
.resNo{color: #FF0000;}
.word{word-break:break-all;}
-->
</style>
<script language="JavaScript" type="text/javascript" src="http://lib.sinaapp.com/js/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript"> 
<!--
$(document).ready(function(){getJSONData();});
function getJSONData()
{
	setTimeout("getJSONData()", 1000);
	$.getJSON('?act=rt&callback=?', displayData);
}
function displayData(dataJSON)
{
	$("#freeSpace").html(dataJSON.freeSpace);
	$("#TotalMemory").html(dataJSON.TotalMemory);
	$("#UsedMemory").html(dataJSON.UsedMemory);
	$("#FreeMemory").html(dataJSON.FreeMemory);
	$("#CachedMemory").html(dataJSON.CachedMemory);
	$("#Buffers").html(dataJSON.Buffers);
	$("#TotalSwap").html(dataJSON.TotalSwap);
	$("#swapUsed").html(dataJSON.swapUsed);
	$("#swapFree").html(dataJSON.swapFree);
	$("#swapPercent").html(dataJSON.swapPercent);
	$("#loadAvg").html(dataJSON.loadAvg);
	$("#uptime").html(dataJSON.uptime);
	$("#freetime").html(dataJSON.freetime);
	$("#stime").html(dataJSON.stime);
	$("#bjtime").html(dataJSON.bjtime);
	$("#memRealUsed").html(dataJSON.memRealUsed);
	$("#memRealFree").html(dataJSON.memRealFree);
	$("#memRealPercent").html(dataJSON.memRealPercent);
	$("#memPercent").html(dataJSON.memPercent);
	$("#barmemPercent").width(dataJSON.memPercent);
	$("#barmemRealPercent").width(dataJSON.barmemRealPercent);
	$("#memCachedPercent").html(dataJSON.memCachedPercent);
	$("#barmemCachedPercent").width(dataJSON.barmemCachedPercent);
	$("#barswapPercent").width(dataJSON.barswapPercent);
	$("#NetOut2").html(dataJSON.NetOut2);
	$("#NetOut3").html(dataJSON.NetOut3);
	$("#NetOut4").html(dataJSON.NetOut4);
	$("#NetOut5").html(dataJSON.NetOut5);
	$("#NetOut6").html(dataJSON.NetOut6);
	$("#NetOut7").html(dataJSON.NetOut7);
	$("#NetOut8").html(dataJSON.NetOut8);
	$("#NetOut9").html(dataJSON.NetOut9);
	$("#NetOut10").html(dataJSON.NetOut10);
	$("#NetInput2").html(dataJSON.NetInput2);
	$("#NetInput3").html(dataJSON.NetInput3);
	$("#NetInput4").html(dataJSON.NetInput4);
	$("#NetInput5").html(dataJSON.NetInput5);
	$("#NetInput6").html(dataJSON.NetInput6);
	$("#NetInput7").html(dataJSON.NetInput7);
	$("#NetInput8").html(dataJSON.NetInput8);
	$("#NetInput9").html(dataJSON.NetInput9);
	$("#NetInput10").html(dataJSON.NetInput10);	
}
-->
</script>
</head>
<body>

<div id="page">
    <div id="header">
        <h1>Info Page</h1>
        <div id="lnmplink">for <a href="http://lnmp.org" target="_blank">LNMP One Key Installation Package</a> <a href="http://bbs.vpser.net/forum-25-1.html" target="_blank">Support (Chinese)</a></div>
    </div>

<!--服务器相关参数-->
<table width="100%" cellpadding="3" cellspacing="0">
  <tr><th colspan="4">Server Information</th></tr>
  <tr>
    <td>Server Domain Name/IP</td>
    <td colspan="3"><?php echo $_SERVER['SERVER_NAME'];?>(<?php if('/'==DIRECTORY_SEPARATOR){echo $_SERVER['SERVER_ADDR'];}else{echo @gethostbyname($_SERVER['SERVER_NAME']);} ?>)</td>
  </tr>
  <tr>
    <td>Server Identity</td>
    <td colspan="3"><?php if($sysInfo['win_n'] != ''){echo $sysInfo['win_n'];}else{echo @php_uname();};?></td>
  </tr>
  <tr>
    <td width="13%">Server OS</td>
    <td width="37%"><?php $os = explode(" ", php_uname()); echo $os[0];?> &nbsp;Kernel version: <?php if('/'==DIRECTORY_SEPARATOR){echo $os[2];}else{echo $os[1];} ?></td>
    <td width="13%">Server Software</td>
    <td width="37%"><?php echo $_SERVER['SERVER_SOFTWARE'];?></td>
  </tr>
  <tr>
    <td>Server Language</td>
    <td><?php echo getenv("HTTP_ACCEPT_LANGUAGE");?></td>
    <td>Server Port</td>
    <td><?php echo $_SERVER['SERVER_PORT'];?></td>
  </tr>
  <tr>
	  <td>Server Host Name</td>
	  <td><?php if('/'==DIRECTORY_SEPARATOR ){echo $os[1];}else{echo $os[2];} ?></td>
	  <td>Absolute Path</td>
	  <td><?php echo $_SERVER['DOCUMENT_ROOT']?str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']):str_replace('\\','/',dirname(__FILE__));?></td>
	</tr>
  <tr>
	  <td>Administrator Mail</td>
	  <td><?php echo $_SERVER['SERVER_ADMIN'];?></td>
		<td>Prober Path</td>
		<td><?php echo str_replace('\\','/',__FILE__)?str_replace('\\','/',__FILE__):$_SERVER['SCRIPT_FILENAME'];?></td>
	</tr>
</table>

<?if("show"==$sysReShow){?>
<table width="100%" cellpadding="3" cellspacing="0" align="center">
  <tr><th colspan="6">Server Statistics</th></tr>
  <tr>
    <td width="13%" >Now</td>
    <td width="37%" ><span id="stime"><?php echo $stime;?></span></td>
    <td width="13%" >Server Runtime</td>
    <td width="37%" colspan="3"><span id="uptime"><?php echo $uptime;?></span></td>
  </tr>
  <tr>
    <td>Storage</td>
    <td><?php echo $dt;?>&nbsp;GB</td>
    <td><a href="#" title="It shows the site's directory of free space, not all free space of server.">Free space</a></td>
    <td colspan="3"><font color='#CC0000'><span id="freeSpace"><?php echo $df;?></span></font>&nbsp;GB</td>
  </tr>
  <tr>
    <td width="13%">CPU model [<?php echo $sysInfo['cpu']['num'];?> Core]</td>
    <td width="87%" colspan="5"><?php echo $sysInfo['cpu']['model'];?></td>
  </tr>
	  <tr>
		<td>Memory Usage <br \><br \><a href="http://www.vpser.net/other/linux-vps-ram.html" target="_blank"><font color=red>How to read this?(Chinese)</font></a></td>
		<td colspan="5">
<?php
$tmp = array(
    'memTotal', 'memUsed', 'memFree', 'memPercent',
    'memCached', 'memRealPercent',
    'swapTotal', 'swapUsed', 'swapFree', 'swapPercent'
);
foreach ($tmp AS $v) {
    $sysInfo[$v] = $sysInfo[$v] ? $sysInfo[$v] : 0;
}
?>
          Memory：All
          <font color='#CC0000'><?php echo $memTotal;?> </font>
           , Used
          <font color='#CC0000'><span id="UsedMemory"><?php echo $mu;?></span></font>
          , Free
          <font color='#CC0000'><span id="FreeMemory"><?php echo $mf;?></span></font>
          , Utilization
		  <span id="memPercent"><?php echo $memPercent;?></span>
          <div class="bar"><div id="barmemPercent" class="barli_green" style="width:<?php echo $memPercent?>%">&nbsp;</div> </div>
<?php
//判断如果cache为0，不显示
if($sysInfo['memCached']>0)
{
?>		
		  Cached: <span id="CachedMemory"><?php echo $mc;?></span>
		  , Utilization
          <span id="memCachedPercent"><?php echo $memCachedPercent;?></span>
		  %	| Buffers  <span id="Buffers"><?php echo $mb;?></span>
          <div class="bar"><div id="barmemCachedPercent" class="barli_blue" >&nbsp;</div></div>

          In Use: Used
          <span id="memRealUsed"><?php echo $memRealUsed;?></span>
		  , Free
          <span id="memRealFree"><?php echo $memRealFree;?></span>
		  , Utilization
          <span id="memRealPercent"><?php echo $memRealPercent;?></span>
          %
          <div class="bar_1"><div id="barmemRealPercent" class="barli_1" >&nbsp;</div></div> 
<?php
}
//判断如果SWAP区为0，不显示
if($sysInfo['swapTotal']>0)
{
?>	
          SWAP：All
          <?php echo $st;?>
          , Used
          <span id="swapUsed"><?php echo $su;?></span>
          , Free
          <span id="swapFree"><?php echo $sf;?></span>
          , Utilization
          <span id="swapPercent"><?php echo $swapPercent;?></span>
          %
          <div class="bar"><div id="barswapPercent" class="barli_red" >&nbsp;</div> </div>

<?php
}	
?>		  
	  </td>
	</tr>
	  <tr>
		<td>System Average Load</td>
		<td colspan="5" class="w_number"><span id="loadAvg"><?php echo $load;?></span></td>
	</tr>
</table>
<?}?>

<?php if (false !== ($strs = @file("/proc/net/dev"))) : ?>
<table width="100%" cellpadding="3" cellspacing="0" align="center">
    <tr><th colspan="3">Network Statistics</th></tr>
<?php for ($i = 2; $i < count($strs); $i++ ) : ?>
<?php preg_match_all( "/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $strs[$i], $info );?>
     <tr>
        <td width="13%"><?php echo $info[1][0]?> : </td>
        <td width="43%">Incoming : <font color='#CC0000'><span id="NetInput<?php echo $i?>"><?php echo $NetInput[$i]?></span></font> GB</td>
        <td width="43%">Outgoing : <font color='#CC0000'><span id="NetOut<?php echo $i?>"><?php echo $NetOut[$i]?></span></font> GB</td>
    </tr>
<?php endfor; ?>
</table>
<?php endif; ?>

<table width="100%" cellpadding="3" cellspacing="0" align="center">
  <tr>
    <th colspan="4" class="th_1">PHP compiled module check</th>
  </tr>
  <tr>
    <td colspan="4"><span class="w_small">
<?php
$able=get_loaded_extensions();
foreach ($able as $key=>$value) {
	if ($key!=0 && $key%13==0) {
		echo '<br />';
	}
	echo "$value&nbsp;&nbsp;";
}
?></span>
    </td>
  </tr>
</table>
<table width="100%" cellpadding="3" cellspacing="0" align="center">
  <tr><th colspan="4" class="th_1">PHP Parameters</th></tr>
  <tr>
    <td width="32%">PHPinfo：</td>
    <td width="18%">
		<?php
		$phpSelf = $_SERVER[PHP_SELF] ? $_SERVER[PHP_SELF] : $_SERVER[SCRIPT_NAME];
		$disFuns=get_cfg_var("disable_functions");
		?>
    <?php echo (false!==eregi("phpinfo",$disFuns))? '<font color="red">×</font>' :"<a href='$phpSelf?act=phpinfo' target='_blank'>PHPINFO</a>";?>
    </td>
    <td width="32%">PHP Version：</td>
    <td width="18%"><?php echo PHP_VERSION;?></td>
  </tr>
  <tr>
    <td>Run PHP:</td>
    <td><?php echo strtoupper(php_sapi_name());?></td>
    <td>Memory Limit:</td>
    <td><?php echo show("memory_limit");?></td>
  </tr>
  <tr>
    <td>PHP Safe Mode:</td>
    <td><?php echo show("safe_mode");?></td>
    <td>Post Max Size:</td>
    <td><?php echo show("post_max_size");?></td>
  </tr>
  <tr>
    <td>Upload Max Filesize:</td>
    <td><?php echo show("upload_max_filesize");?></td>
    <td>Floating-point data of significant digits:</td>
    <td><?php echo show("precision");?></td>
  </tr>
  <tr>
    <td>Max Execution Time:</td>
    <td><?php echo show("max_execution_time");?> Seconds</td>
    <td>Socket TimeOut:</td>
    <td><?php echo show("default_socket_timeout");?> Seconds</td>
  </tr>
  <tr>
    <td>PHP Doc Root:</td>
    <td><?php echo show("doc_root");?></td>
    <td>User Dir:</td>
    <td><?php echo show("user_dir");?></td>
  </tr>
  <tr>
    <td>Enable Dl:</td>
    <td><?php echo show("enable_dl");?></td>
    <td>Include Path:</td>
    <td><?php echo show("include_path");?></td>
  </tr>
  <tr>
    <td>Display Errors:</td>
    <td><?php echo show("display_errors");?></td>
    <td>Register Globals:</td>
    <td><?php echo show("register_globals");?></td>
  </tr>
  <tr>
    <td>Magic Quotes Gpc:</td>
    <td><?php echo show("magic_quotes_gpc");?></td>
    <td>"&lt;?...?&gt;"Short Open Tag:</td>
    <td><?php echo show("short_open_tag");?></td>
  </tr>
  <tr>
    <td>"&lt;% %&gt;"ASP Tags:</td>
    <td><?php echo show("asp_tags");?></td>
    <td>Ignore Repeated Errors:</td>
    <td><?php echo show("ignore_repeated_errors");?></td>
  </tr>
  <tr>
    <td>Ignore Repeated Source:</td>
    <td><?php echo show("ignore_repeated_source");?></td>
    <td>Report Memleaks:</td>
    <td><?php echo show("report_memleaks");?></td>
  </tr>
  <tr>
    <td>Magic Quotes Gpc:</td>
    <td><?php echo show("magic_quotes_gpc");?></td>
    <td>Magic Quotes Runtime:</td>
    <td><?php echo show("magic_quotes_runtime");?></td>
  </tr>
  <tr>
    <td>Allow URL Fopen:</td>
    <td><?php echo show("allow_url_fopen");?></td>
    <td>Register Argc Argv:</td>
    <td><?php echo show("register_argc_argv");?></td>
  </tr>
  <tr>
    <td>Cookie:</td>
    <td><?php echo isset($_COOKIE)?'<font color="green">√</font>' : '<font color="red">×</font>';?></td>
    <td>Spell check(ASpell Library):</td>
    <td><?php echo isfun("aspell_check_raw");?></td>
  </tr>
   <tr>
    <td>High-precision math(BCMath):</td>
    <td><?php echo isfun("bcadd");?></td>
    <td>PREL(PCRE):</td>
    <td><?php echo isfun("preg_match");?></td>
   <tr>
    <td>PDF:</td>
    <td><?php echo isfun("pdf_close");?></td>
    <td>SNMP:</td>
    <td><?php echo isfun("snmpget");?></td>
  </tr> 
   <tr>
    <td>VMailMgr:</td>
    <td><?php echo isfun("vm_adduser");?></td>
    <td>Curl:</td>
    <td><?php echo isfun("curl_init");?></td>
  </tr> 
   <tr>
    <td>SMTP:</td>
    <td><?php echo get_cfg_var("SMTP")?'<font color="green">√</font>' : '<font color="red">×</font>';?></td>
    <td>SMTP:</td>
    <td><?php echo get_cfg_var("SMTP")?get_cfg_var("SMTP"):'<font color="red">×</font>';?></td>
  </tr> 
	<tr>
		<td>Enable Functions:</td>
		<td colspan="3"><a href='<?php echo $phpSelf;?>?act=Function' target='_blank' class='static'>Click here to view more!</a></td>		
	</tr>
	<tr>
		<td>Disable Functions:</td>
		<td colspan="3" class="word">
<?php 
$disFuns=get_cfg_var("disable_functions");
if(empty($disFuns))
{
	echo '<font color=red>×</font>';
}
else
{ 
	//echo $disFuns;
	$disFuns_array =  explode(',',$disFuns);
	foreach ($disFuns_array as $key=>$value) 
	{
		if ($key!=0 && $key%5==0) {
			echo '<br />';
	}
	echo "$value&nbsp;&nbsp;";
}	
}

?>
		</td>
	</tr>
</table>
<!--组件信息-->
<table width="100%" cellpadding="3" cellspacing="0" align="center">
  <tr><th colspan="4" class="th_1">Component Support</th></tr>
  <tr>
    <td width="32%">FTP:</td>
    <td width="18%"><?php echo isfun("ftp_login");?></td>
    <td width="32%">XML:</td>
    <td width="18%"><?php echo isfun("xml_set_object");?></td>
  </tr>
  <tr>
    <td>Session:</td>
    <td><?php echo isfun("session_start");?></td>
    <td>Socket:</td>
    <td><?php echo isfun("socket_accept");?></td>
  </tr>
  <tr>
    <td>Calendar:</td>
    <td><?php echo isfun('cal_days_in_month');?>
	</td>
    <td>Allow URL FOpen:</td>
    <td><?php echo show("allow_url_fopen");?></td>
  </tr>
  <tr>
    <td>GD library:</td>
    <td>
    <?php
        if(function_exists(gd_info)) {
            $gd_info = @gd_info();
	        echo $gd_info["GD Version"];
	    }else{echo '<font color="red">×</font>';}
	?></td>
    <td>Zlib:</td>
    <td><?php echo isfun("gzclose");?></td>
  </tr>
  <tr>
    <td>IMAP:</td>
    <td><?php echo isfun("imap_close");?></td>
    <td>JDToGregorian:</td>
    <td><?php echo isfun("JDToGregorian");?></td>
  </tr>
  <tr>
    <td>Preg Match:</td>
    <td><?php echo isfun("preg_match");?></td>
    <td>WDDX:</td>
    <td><?php echo isfun("wddx_add_vars");?></td>
  </tr>
  <tr>
    <td>Iconv:</td>
    <td><?php echo isfun("iconv");?></td>
    <td>mbstring:</td>
    <td><?php echo isfun("mb_eregi");?></td>
  </tr>
  <tr>
    <td>BCADD:</td>
    <td><?php echo isfun("bcadd");?></td>
    <td>LDAP:</td>
    <td><?php echo isfun("ldap_close");?></td>
  </tr>
  <tr>
    <td>MCrypt：</td>
    <td><?php echo isfun("mcrypt_cbc");?></td>
    <td>Mhash Count:</td>
    <td><?php echo isfun("mhash_count");?></td>
  </tr>
</table>

<!--第三方组件信息-->
<table width="100%" cellpadding="3" cellspacing="0" align="center">
  <tr><th colspan="4" class="th_1">Third-Party Components</th></tr>
  <tr>
    <td width="32%">Zend Verson</td>
    <td width="18%"><?php $zend_version = zend_version();if(empty($zend_version)){echo '<font color=red>×</font>';}else{echo $zend_version;}?></td>
    <td width="32%">
<?php
$PHP_VERSION = PHP_VERSION;
$PHP_VERSION = substr($PHP_VERSION,2,1);
if($PHP_VERSION > 2)
{
	echo "ZendGuardLoader[Enable]";
}
else
{
	echo "Zend Optimizer";
}
?>
	</td>
    <td width="18%"><?php if($PHP_VERSION > 2){echo (get_cfg_var("zend_loader.enable"))?'<font color=green>√</font>':'<font color=red>×</font>';} else{if(function_exists('zend_optimizer_version')){	echo zend_optimizer_version();}else{	echo (get_cfg_var("zend_optimizer.optimization_level")||get_cfg_var("zend_extension_manager.optimizer_ts")||get_cfg_var("zend.ze1_compatibility_mode")||get_cfg_var("zend_extension_ts"))?'<font color=green>√</font>':'<font color=red>×</font>';}}?></td>
  </tr>
  <tr>
    <td>eAccelerator</td>
    <td><?php if((phpversion('eAccelerator'))!=''){echo phpversion('eAccelerator');}else{ echo "<font color=red>×</font>";} ?></td>
    <td>ioncube</td>
    <td><?php if(extension_loaded('ionCube Loader')){   $ys = ioncube_loader_iversion();   $gm = ".".(int)substr($ys,3,2);   echo ionCube_Loader_version().$gm;}else{echo "<font color=red>×</font>";}?></td>
  </tr>
  <tr>
    <td>XCache</td>
    <td><?php if((phpversion('XCache'))!=''){echo phpversion('XCache');}else{ echo "<font color=red>×</font>";} ?></td>
    <td>APC</td>
    <td><?php if((phpversion('APC'))!=''){echo phpversion('APC');}else{ echo "<font color=red>×</font>";} ?></td>
  </tr>
</table>

<!--数据库支持-->
<table width="100%" cellpadding="3" cellspacing="0" align="center">
  <tr><th colspan="4" class="th_2">Database Support</th></tr>
  <tr>
    <td width="32%">MySQL:</td>
    <td width="18%"><?php echo isfun("mysql_close");?>
    <?php
    if(function_exists("mysql_get_server_info")) {
        $s = @mysql_get_server_info();
        $s = $s ? '&nbsp; mysql_server Verson：'.$s : '';
	    $c = '&nbsp; mysql_client Verson：'.@mysql_get_client_info();
        echo $s;
    }
    ?>
	</td>
    <td width="32%">ODBC:</td>
    <td width="18%"><?php echo isfun("odbc_close");?></td>
  </tr>
  <tr>
    <td>Oracle:</td>
    <td><?php echo isfun("ora_close");?></td>
    <td>SQL Server:</td>
    <td><?php echo isfun("mssql_close");?></td>
  </tr>
  <tr>
    <td>dBASE:</td>
    <td><?php echo isfun("dbase_close");?></td>
    <td>mSQL:</td>
    <td><?php echo isfun("msql_close");?></td>
  </tr>
  <tr>
    <td>SQLite:</td>
    <td><?php if(extension_loaded('sqlite3')) {$sqliteVer = SQLite3::version();echo '<font color=green>√</font>　';echo "SQLite3　Ver ";echo $sqliteVer[versionString];}else {echo isfun("sqlite_close");if(isfun("sqlite_close") == '<font color="green">√</font>') {echo "&nbsp; 版本： ".@sqlite_libversion();}}?></td>
    <td>Hyperwave:</td>
    <td><?php echo isfun("hw_close");?></td>
  </tr>
  <tr>
    <td>Postgre SQL:</td>
    <td><?php echo isfun("pg_close"); ?></td>
    <td>Informix:</td>
    <td><?php echo isfun("ifx_close");?></td>
  </tr>
  <tr>
    <td>DBA:</td>
    <td><?php echo isfun("dba_close");?></td>
    <td>DBM:</td>
    <td><?php echo isfun("dbmclose");?></td>
  </tr>
  <tr>
    <td>FilePro:</td>
    <td><?php echo isfun("filepro_fieldcount");?></td>
    <td>SyBase:</td>
    <td><?php echo isfun("sybase_close");?></td>
  </tr> 
</table>

<form action="<?php echo $_SERVER[PHP_SELF]."#bottom";?>" method="post">
<!--MySQL数据库连接检测-->
<table width="100%" cellpadding="3" cellspacing="0" align="center">
	<tr><th colspan="3" class="th_2">MySQL Database Connection Check</th></tr>
  <tr>
    <td width="15%"></td>
    <td width="60%">
      Address: <input type="text" name="host" value="localhost" size="10" />
      Port: <input type="text" name="port" value="3306" size="10" />
      Username: <input type="text" name="login" size="10" />
      Password: <input type="password" name="password" size="10" />
    </td>
    <td width="25%">
      <input class="btn" type="submit" name="act" value="MySQL Check" />
    </td>
  </tr>
</table>
  <?php
  if ($_POST['act'] == 'MySQL Check') {
  	if(function_exists("mysql_close")==1) {
  		$link = @mysql_connect($host.":".$port,$login,$password);
  		if ($link){
  			echo "<script>alert('Success.')</script>";
  		} else {
  			echo "<script>alert('Can't connect MySql Database!')</script>";
  		}
  	} else {
  		echo "<script>alert('The Server Don't Support MySQL Database!')</script>";
  	}
  }
	?>
<!--函数检测-->
<table width="100%" cellpadding="3" cellspacing="0" align="center">
	<tr><th colspan="3" class="th_4">Function Check</th></tr>
  <tr>
    <td width="15%"></td>
    <td width="60%">
      Please enter a function you want to test:
      <input type="text" name="funName" size="50" />
    </td>
    <td width="25%">
      <input class="btn" type="submit" name="act" align="right" value="Function Check" />
    </td>
  </tr>
  <?php
  if ($_POST['act'] == 'Function Check') {
  	echo "<script>alert('$funRe')</script>";
  }
  ?>
</table>
<!--邮件发送检测-->
<table width="100%" cellpadding="3" cellspacing="0" align="center">
  <tr><th colspan="3" class="th_5">Send Mail Check</th></tr>
  <tr>
    <td width="15%"></td>
    <td width="60%">
      Please enter an e-mail address you want to test:
      <input type="text" name="mailAdd" size="50" />
    </td>
    <td width="25%">
    <input class="btn" type="submit" name="act" value="Mail Check" />
    </td>
  </tr>
  <?php
  if ($_POST['act'] == 'Mail Check') {
  	echo "<script>alert('$mailRe')</script>";
  }
  ?>
</table>
</form>
<a id="bottom"></a>

<div id="footer">
&copy; 2012 <a href="http://lnmp.org" target="_blank">LNMP One Key Installation Package</a><br />This Prober was based on Yahei Prober.<br />
<?php $run_time = sprintf('%0.4f', microtime_float() - $time_start);?>
Processed in <?php echo $run_time?> seconds. <?php echo memory_usage();?> memory usage.

</div>

</div>
</body>
</html>