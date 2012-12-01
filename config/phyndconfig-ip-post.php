<?
# Phynd Network Indexer

#######################################################
# Phynd Network Indexer

# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#######################################################

##	Phynd Network Indexer Configuration
##	Written by David Borgeson <borgeson@engr.uconn.edu>
##	Copyright David Borgeson	2003
?>

<html>
<head>
<title>Phynd Configuration</title>
<?


require_once("/etc/phynd/phyndvars.php");


$POSTPAGE = "phyndconfig-ip-post.php";

@$page = $_GET['page'];
@$cat = $_GET['cat'];



switch ($page)
{

case "access":

	switch ($cat)
	{
	case "includeip":
	$TABLE = $ACCESSINCLUDEIP;
	$title = "ACCESS Include IP's";
	break;

	case "excludeip":
	$TABLE = $ACCESSEXCLUDEIP;
	$title = "ACCESS Exclude IP's";
	break;

	case "netblocks":
	$TABLE = $ACCESSNETBLOCKS;
	$title = "ACCESS Netblocks";
	break;

	default:
	exit;
	}

break;


case "catalog":

	switch ($cat)
	{
	case "includeip":
	$TABLE = $CATALOGINCLUDEIP;
	$title = "CATALOG Include IP's";
	break;

	case "excludeip":
	$TABLE = $CATALOGEXCLUDEIP;
	$title = "CATALOG Exclude IP's";
	break;

	case "restrictwords":
	$TABLE = $CATALOGRESTRICTWORDS;
	$title = "CATALOG Restrict Words";
	break;

	case "netblocks":
	$TABLE = $CATALOGNETBLOCKS;
	$title = "CATALOG Netblocks";
	break;


	default:
	exit;
	}

break;

default:
exit;

}


$dbase = mysql_connect($DATABASEHOST,$DATABASEUSER, $DATABASEPASS);		# connect to database
mysql_select_db($DATABASE);							# select the table



if ($_GET['action'] == "add" && ($_GET['cat'] == "includeip" || $_GET['cat'] == "excludeip"))	# want to add to an iplist
	{
	$ip = $_POST['ip'];

	$query ="INSERT INTO `$TABLE` (`ip`) VALUES ('$ip') ";
	mysql_query($query);
	echo mysql_error();
	}


else if ($_GET['action'] == "delete" && ($_GET['cat'] == "includeip" || $_GET['cat'] == "excludeip"))	# want to delete fron an iplist
	{
	mysql_query("DELETE FROM `$TABLE` WHERE `ip` = '$ip'");
	}


else if ($_GET['action'] == "add" && $_GET['cat'] == "netblocks")	# want to add to netblocks
	{
	$ip1 = $_POST['start'];
	$ip2 = $_POST['end'];

	mysql_query("INSERT INTO $TABLE (`start` , `end`) VALUES ('$ip1' , '$ip2') ");


	}
else if ($_GET['action'] == "add" && $_GET['cat'] == "restrictwords")
	{

	$word = $_POST['word'];
	mysql_query("INSERT INTO $TABLE (`word`) VALUES ('$word') ");


	}

else if ($_GET['action'] == "delete" && $_GET['cat'] == "restrictwords")
	{

	$word = $_GET['word'];
	mysql_query("DELETE FROM `$TABLE` WHERE `word` = '$word' ");
	echo mysql_error();


	}







else		# want to delete from netblocks
	{

	$ip1 = $_GET['ip1'];
	$ip2 = $_GET['ip2'];

	mysql_query("DELETE FROM `$TABLE` WHERE `start` = '$ip1' AND `end` = '$ip2' ");
	echo mysql_error();
	}


$backpage = $_SERVER['HTTP_REFERER'];



mysql_close($dbase);			# close the database




echo "<meta http-equiv=\"refresh\" content=\"0; url=$backpage\">";

?>
</head>

<body>

</body>

</html>
