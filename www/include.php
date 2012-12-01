<?
require_once("/etc/phynd/phyndvars.php");

exec("cut -b 1-4 /proc/loadavg",$load);
$load = $load[0];

if (($load > 3)&&($load <=5 ))
{
	$color=" COLOR=\"#FFA500\"";
}
elseif ( $load > $MAXLOAD )
{
	$color=" COLOR=\"#F20000\"";
}
else
{
	$color=" COLOR=\"#00FF33\"";
}


$dbase = mysql_connect($DATABASEHOST, $DATABASEUSER, $DATABASEPASS);
mysql_select_db($DATABASE);

$result = mysql_query("SHOW TABLE STATUS FROM $DATABASE like '$CATALOGINDEX' ");

if (!$result)
{
echo "Error retrieving data....";
exit;
}

$row = mysql_fetch_object($result);

$lastupdated = $row->Update_time;

$time=time()-strtotime($lastupdated);

$days=(int)($time / 86400);
$hours=(int)($time % 86400) / 3600;
$minutes=(int)(($time % 86400) % 3600) / 60;

if(round($days)) $dataage.=(int)$days."d ";
if(round($hours)) $dataage.=(int)$hours."h ";
if(round($minutes)) $dataage.=(int)$minutes."m";


?>

<html>
<head>
<title>
Phynd
</title>

<link rel="stylesheet" href="site.css" type="text/css">

</head>

<BODY BGCOLOR=#11263C leftmargin=0 topmargin=0 marginwidth=0 marginheight=0 style="background-repeat: repeat-x">

<font size=+5>Phynd - Basic Configuration</font>


<div id="Layer1" style="position:absolute; width:114px; height:210px; z-index:2; left: 14px; top: 114px;">
  <p align="left"><span class="content"> Connection From:<br>
   <font color="#00FF33"><?=$_SERVER["REMOTE_ADDR"]?></font><br>
    <br>
    Database age:<br>
    <font color="#00FF33"><?=$dataage?></font><br>
    <br>
    </span><span class="content">Current Load:<br>
    <font <?=$color?>><?=$load?></font><br>
    <br>
    Maximum Load:<br>
    <font color="#00FF33"><?=$MAXLOAD?></font><br>
    </span></p>
  <br>
</div>

<div id="Maincontent" style="position:absolute; left:142px; top:114px; width:627px; height:451px; z-index:1">
