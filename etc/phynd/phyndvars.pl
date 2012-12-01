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


# The include file for the PERL catalog script (this file) does not need php tags



$FASTPING = "sudo fastping";
$NMBLOOKUP = "/usr/bin/nmblookup";
$SMBCLIENT = "/usr/bin/smbclient";
$WINS = "";

$DATABASEHOST = "localhost";
$DATABASEUSER = "phynd";
$DATABASEPASS = "phynd";
$DATABASE = "phynd";


$DSN = "dbi:mysql:$DATABASE:$DATABASEHOST:3306";

$CATALOGIPLIST = "CATALOGiplist";
$CATALOGINDEX = "SearchIndex";
$CATALOGRESTRICTWORDS= "CATALOGrestrictwords";
$CATALOGNETBLOCKS = "CATALOGnetblocks";
$CATALOGEXCLUDEIP = "CATALOGexcludeip";
$CATALOGINCLUDEIP = "CATALOGincludeip";
$CATALOGINDEXTEMP = "temp";
$CATALOGSTATS = "CATALOGstats";
$CATALOGCATEGORIES = "CATALOGcategories";

$MAXLOAD = 7;

$ACCESSIPLIST = "ACCESSiplist";
$ACCESSNETBLOCKS = "ACCESSnetblocks";
$ACCESSEXCLUDEIP = "ACCESSexcludeip";
$ACCESSINCLUDEIP = "ACCESSincludeip";


$DENIEDLOG = "LOGdenied";


# total number of threads to create
# this is VERY VERY memory dependent
# on a 512MB test machine, the upper limit of threads is about 20....
# if you create too many threads, you will get segmentation faults, very randomly, and they will annoy the hell out of you

$totalthreads = 4;
