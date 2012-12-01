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
<link rel="stylesheet" href="webpage.css" type="text/css">

<BODY BGCOLOR=#11263C leftmargin=10 topmargin=10>

</head>

<body>
<?

require_once("/etc/phynd/phyndvars.php");


@$page = $_GET['page'];
@$cat = $_GET['cat'];



$POSTPAGE = "phyndconfig-ip-post.php";


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

echo "

<div align=\"center\"><font size=5><b>Phynd Configuration - $title</b></font></div>
<Br><Br>

";

$dbase = mysql_connect($DATABASEHOST,$DATABASEUSER, $DATABASEPASS);		# connect to database
mysql_select_db($DATABASE);							# select the table





if ($cat == "includeip" || $cat == "excludeip")
{
$result = mysql_query("SELECT * FROM $TABLE ORDER BY `ip`");

echo "<table border=0 cellspacing=0>";

$rowbg = "#11263C";

while ($row = mysql_fetch_object($result))
{
if ($rowbg == "#11263C")
{ $rowbg = "#666666"; }
else { $rowbg = "#11263C"; }

echo "
<tr bgcolor=\"$rowbg\">
<td>$row->ip</td>
<td width=10></td>
<td><a href=\"$POSTPAGE?page=$page&cat=$cat&ip=$row->ip&action=delete\"><B>Delete</b></a></td>
</tr>

";

}

echo "
</table>

<br><br>

<form method=\"POST\" action=\"$POSTPAGE?page=$page&cat=$cat&action=add\">
<input type=\"hidden\" value=\"$page\" name=\"page\">
<input type=\"hidden\" value=\"$cat\" name=\"cat\">


Add a new entry: <INPUT TYPE=text NAME=\"ip\" SIZE=15 MAXLENGTH=15>&nbsp;<input type=\"submit\" value=\"Submit\" name=\"submit\">

</form>
";


}



else if ($cat == "restrictwords")
{
$result = mysql_query("SELECT * FROM $TABLE ORDER BY `word`");

echo "<table border=0 cellspacing=0>";

$rowbg = "#11263C";

while ($row = mysql_fetch_object($result))
{
if ($rowbg == "#11263C")
{ $rowbg = "#666666"; }
else { $rowbg = "#11263C"; }

echo "
<tr bgcolor=\"$rowbg\">
<td>$row->word</td>
<td width=10></td>
<td><a href=\"$POSTPAGE?page=$page&cat=$cat&word=$row->word&action=delete\"><B>Delete</b></a></td>
</tr>

";

}

echo "
</table>

<br><br>

<form method=\"POST\" action=\"$POSTPAGE?page=$page&cat=$cat&action=add\">
<input type=\"hidden\" value=\"$page\" name=\"page\">
<input type=\"hidden\" value=\"$cat\" name=\"cat\">


Add a new entry: <INPUT TYPE=text NAME=\"word\" SIZE=15 MAXLENGTH=40>&nbsp;<input type=\"submit\" value=\"Submit\" name=\"submit\">

</form>
";
}




else if ($cat == "netblocks")
{
$result = mysql_query("SELECT * FROM $TABLE ORDER BY `start`");

echo "<table border=0 cellspacing=0>";

$rowbg = "#ffffff";

while ($row = mysql_fetch_object($result))
{
if ($rowbg == "#11263C")
{ $rowbg = "#666666"; }
else { $rowbg = "#11263C"; }



echo "
<tr bgcolor=\"$rowbg\">
<td>$row->start</td>
<td width=10></td>
<td>$row->end</td>
<td width=10></td>
<td>
<a href=\"$POSTPAGE?page=$page&cat=$cat&ip1=$row->start&ip2=$row->end&action=delete\"><B>Delete</b></a>
</td>
</tr>

";

}

echo "
</table>

<br><br>

<form method=\"POST\" action=\"$POSTPAGE?page=$page&cat=$cat&action=add\">
<input type=\"hidden\" value=\"$page\" name=\"page\">
<input type=\"hidden\" value=\"$cat\" name=\"cat\">


<B>Add a new entry:</b>
<Br><br>
Start IP:<INPUT TYPE=text NAME=\"start\" SIZE=15 MAXLENGTH=15><Br><br>
End IP: <INPUT TYPE=text NAME=\"end\" SIZE=15 MAXLENGTH=15><Br>
<Br>

<input type=\"submit\" value=\"Submit\" name=\"submit\">

</form>
";













}



mysql_close($dbase);			# close the database
?>

<Br><Br>

<a href="index.php">Back to Phynd Configuration</a>

</body>

</html>
