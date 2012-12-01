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

<?
require_once("/etc/phynd/phyndvars.php");

$dbase = mysql_connect('localhost', 'phynd', 'phynd');		# connect to database
mysql_select_db('phynd');										# select the table

mysql_query("DELETE FROM `$ACCESSIPLIST`");							# clear the ACCESSiplist


$result = mysql_query("SELECT * FROM $ACCESSNETBLOCKS");				#grab all the netblock data






while ($row = mysql_fetch_object($result))							# loop through all the netblocks
{
$IPs = $row->start;												# get the start and end of the block
$IPe = $row->end;

$IPstart = explode("." , $IPs);										# separate the addresses
$IPend = explode("." , $IPe);


$swap = 0;													# if the addresses are out of order, swap them

if ( $IPstart[0] > $IPend[0] )
{
$swap=1;
}
else if (( $IPstart[1] > $IPend[1] ) && ($IPstart[0] == $IPend[0] ) )
{
$swap=1;
}
else if (( $IPstart[2] > $IPend[2] ) && ($IPstart[0] == $IPend[0] ) && ($IPstart[1] == $IPend[1] ) )
{
$swap = 1;
}
else if (( $IPstart[3] > $IPend[3] ) && ($IPstart[0] == $IPend[0] ) && ($IPstart[1] == $IPend[1] ) && ($IPstart[2] == $IPend[2] ))
{
$swap = 1;
}

if ($swap == 1)			# swap the start and end addresses so we can increment them
{
$temp = $IPstart;
$IPstart = $IPend;
$IPend = $temp;
}








$eight = $IPstart[0];					# separate the starting IP so we can increment it
$sixteen = $IPstart[1];
$twentyfour = $IPstart[2];
$thirtytwo = $IPstart[3];

$IP = "$eight.$sixteen.$twentyfour.$thirtytwo";

mysql_query("INSERT INTO $ACCESSIPLIST (`IP`) VALUES ('$IP')");			# insert the value into ACCESSiplist

while (1)								# make the IP's from the netblock
{

	$thirtytwo++;							# increment the netblock

	if ($thirtytwo == 256)					# if the 0.0.0.X flows over, increment 0.0.X.0, and keep going
	{
	$thirtytwo = 0;
	$twentyfour++;
	}

	if ($twentyfour == 256)					# if the 0.0.X.0 flows over, increment 0.X.0.0, and keep going
	{
	$twentyfour = 0;
	$sixteen++;
	}

	if ($sixteen == 256)						# if the 0.X.0.0 flows over, increment X.0.0.0, and keep going
	{
	$sixteen = 0;
	$eight++;
	}

	if ($eight == 256)						# if the X.0.0.0 flows over, exit out, because we are obviously at the end
	{
	print "IP out of range....exiting...";
	break;
	}

	$IP = "$eight.$sixteen.$twentyfour.$thirtytwo";

	mysql_query("INSERT INTO $ACCESSIPLIST (`IP`) VALUES ('$IP')");			# insert the value into ACCESSiplist

	# do all this while the IP being inserted isn't equal to the end of the netblock we are on
	# if we have reached the ending IP address, then break out of the loop

	if (($eight == $IPend[0] && $sixteen == $IPend[1] && $twentyfour == $IPend[2] && $thirtytwo == $IPend[3]))
	{
	break;
	}


	}	# end while loop for one netblock


}		# end the while loop that goes through the netblocks




$result = mysql_query("SELECT * FROM $ACCESSEXCLUDEIP");			# grab all the data from ACCESSexcludeip

while ($row = mysql_fetch_object($result) )
{

$excludedIP = $row->ip;

$delete = mysql_query("DELETE FROM `$ACCESSIPLIST` WHERE `ip` = '$excludedIP' ");	# delete the excluded IP from ACCESSiplist

}	# end the while loop grabbing data from the EXCLUDE IP list





$result = mysql_query("SELECT * FROM $ACCESSINCLUDEIP");			# grab all the data from ACCESSincludeip

while ($row = mysql_fetch_object($result) )
{

$IP = $row->ip;

$insert = mysql_query("INSERT INTO `$ACCESSIPLIST` (`ip`) VALUES('$IP') ");	# include any extra addresses in ACCESSiplist

}	# end the while loop grabbing data from the INCLUDE IP list




mysql_close($dbase);			# close the database


?>

<html>
<link rel="stylesheet" href="webpage.css" type="text/css">


<title>Phynd Configuration</title>
<BODY BGCOLOR=#11263C leftmargin=10 topmargin=10>
ACCESSiplist has been updated.....
<br><br>
<a href="index.php">Back to the Phynd Configuration</a>
</body>
</html>

