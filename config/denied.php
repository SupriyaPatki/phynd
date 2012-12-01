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

<link rel="stylesheet" href="webpage.css" type="text/css">


<title>DENIED Log</title>
<BODY BGCOLOR=#11263C leftmargin=10 topmargin=10>
<div align="center">
<font size=6><b>Phynd Configuration - DENIED Log</b></font>
</div>

<Br><br>

<?

require_once("/etc/phynd/phyndvars.php");

if (isset($_GET['whois']))
{
$IP = $_GET['whois'];

exec("whois $IP" , $whois);

for ($count=0 ; $count < sizeof($whois) -2 ; $count++ )
{
print "$whois[$count]<br>\n";
}

echo "

<br><Br>

<a href=\"denied.php\">Back to DENIED Log</a>

</body>
</html>

";
exit;


}










$dbase = mysql_connect($DATABASEHOST, $DATABASEUSER, $DATABASEPASS);
mysql_select_db($DATABASE);

$result = mysql_query("SELECT * FROM $DENIEDLOG");


echo "
<table border=0 cellspacing=0>

<tr bgcolor=\"#11263C\">
<td><B><font size=3>IP Address</font></b></td>
<td width=15></td>
<td><B><font size=3>Searched For</font></b></td>
<td width=15></td>
</tr>



";




$rowbg = "#11263C";

while ($row = mysql_fetch_object($result) )
{
if ($rowbg == "#11263C")
{ $rowbg = "#666666"; }
else { $rowbg = "#11263C"; }

$IP = $row->ip;

$host = gethostbyaddr($IP);

echo "
<tr bgcolor=\"$rowbg\">
<td><A HREF=\"?whois=$IP\">$host</a></td>
<Td></td>
<td>$row->searchfor</td>
<td></td>
<td>$row->time</td>
</tr>

";

}

echo "
</table>

";


?>

<br><Br>

<a href="index.php">Back to Phynd Configuration</a>

</body>
</html>
