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


<title>Phynd Configuration</title>
<BODY BGCOLOR=#11263C leftmargin=10 topmargin=10>
<div align="center">
<font size=6><b>Phynd Configuration</b></font>
</div>
<Br><Br>
<B>
<font size=4>Catalog Configuration</font>
</b>
<Br><Br>

<?
$configpage = "phyndconfig-ip.php";


echo "

<a href=\"$configpage?page=catalog&cat=netblocks\">Netblocks</a>

<Br>

<a href=\"$configpage?page=catalog&cat=includeip\">Include IP's</a>

<Br>

<a href=\"$configpage?page=catalog&cat=excludeip\">Exclude IP's</a>

<Br>

<a href=\"$configpage?page=catalog&cat=restrictwords\">Restrict Words</a>

<Br><Br>


<a href=\"buildCATALOGiplist.php\"><b>Build Catalog IPList</b></a>

<Br><Br><Br>

<font size=4><b>Access Configuration</b></font>

<Br><br>

<a href=\"$configpage?page=access&cat=netblocks\">Netblocks</a>

<Br>

<a href=\"$configpage?page=access&cat=includeip\">Include IP's</a>

<Br>

<a href=\"$configpage?page=access&cat=excludeip\">Exclude IP's</a>
<Br><Br>

<a href=\"buildACCESSiplist.php\"><b>Build Access IPList</b></a>

<Br><Br>

<a href=\"denied.php\"><b>Show DENIED Log</b></a>











</body>
</html>


";

?>
