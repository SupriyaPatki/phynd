#!/usr/bin/perl

# Phynd Network Indexer v5.0

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




####  UPDATE August 2003	David Borgeson <borgeson@engr.uconn.edu>   ####

# With the recent abandonment of the Phynd project, a few of us decided to revitalize the project with some things that we wished were in 3.0, but never came to be...
# The major changes in this release are as follows:
# Instead of putting indexed files in text files, all indexing is done to a MySQL database
# Beacuse of the efficiency of MySQL, we do not create separate databases for file types
# For this script, the IP's that the engine will search on are loaded from the database, and generated from a PHP script, which must be run before the catalog can start indexing
# Threading: Phynd is now multithreaded, so multiple spiders search all parts of the IP listings, eliminating the chance that 1 host may slow down the entire search process.
#This threading reduces indexing times by about 70%.  For example: a search with 4 threads on 755 IP addresses took 5.5 minutes. With 14 threads, it took merely 1.75 minutes. The non threaded version of Phynd would most likely take about 10 minutes. With threads, however, there is a memory tradeoff. On a 512MB system, PERL could only handle about 14 threads, saturating all available memory.  We found that 14 threads is more than enough for efficient indexing, and is a good balance between speed and memory usage.

# Updated by David Borgeson <borgeson@engr.uconn.edu>

######## WHAT WE WOULD LIKE TO SEE ############
# One screen status window of all threads and what they are doing
# A Windows port? I can't imagine why this wouldn't work out of the box in Windows...But I enjoy running it off linux..

###############################################
#
# Phynd Network Indexer
# Written by Sam Hopewell <hopews@rpi.edu>
# Copyright Sam Hopewell
#
# This code creates a text database of all files on a windows network
# in a given range of IP addresses.  To do this it uses two utilities
# from the Samba software suit ( http://www.samba.org ) nmblookup and
# smbclient.  In addition it is meant to use a modified version of
# ping in which the timeout delay has been lowered from 10 seconds to
# 1 second.  The source code to ping is available from the following
# ftp site:
#
#    ftp://ftp.uk.linux.org/pub/linux/Networking/base/
#
#

use DBI;					# use MySQL module

use Thread;				# use multithreading

require "/etc/phynd/phyndvars.pl";			# include variable header file


print "START";

my $ipoverrulehost = 0;
# 0 to use hostname as computer name,
# 1 to use IP as computer name


# connect to the database
my $db = DBI->connect($DSN, $DATABASEUSER, $DATABASEPASS) or die "Can\'t connect to the DB: $DBI::errstr\n";

# clear out the temp database
my $sth = $db->prepare("DELETE FROM `$CATALOGINDEXTEMP`");
$sth->execute;


$db->disconnect();



##############################################################################

$|=1;

# This function resolves a netbios hostname from an IP address
# ARGS:
#   $IP - The IP Address whos name to resolve
# RETURNS:
#   ($hostname,$workgroup) - on success
#   -1 - on failure

sub IPtoHost {

    my $IP = shift;
    my $hostname;
    my $workgroup;
    my $temp;

    $temp=`$FASTPING -c 1 $IP`;					# ping the IP address
    $thread = Thread->self->tid;
    print "$thread\tPing $IP\n";					# print out the thread number and IP address

    if ($temp =~ /100\% packet loss/)
    {
	return ('none','none');						# if the host is unreachable, return 'none'
    }
    my $response;
    if (!$WINS)
    {
	$response = `$NMBLOOKUP -A $IP`;			# look up the NetBIOS name of the computer
    }
    else
    {
    $response = `$NMBLOOKUP -U $WINS -R -A $IP`;
    }
    if ( $response =~ /\<ACTIVE\>/ ) {
	$response =~ /\s*(.+?)\s*\<00\> -  /;
	$hostname = $1;
	$response =~ /\s*(.+?)\s*\<00\> - \<GROUP\>/;
	$workgroup = $1;

	return ($hostname,$workgroup);				# return the NetBIOS name and workgroup
    }
    else
    {
	return ('none','none');						# otherwise, return 'none'
    }


};


# This calls smbclient and uses it to retrieve a list of shares
# ARGS:
#   $name - netbios hostname to connect to
# RETURNS:
#   @shares - a list of disk shares on the host

sub HostShareList
{
    my $name = shift;


#    print "Searching ".$name . "\n";
    my $response = `$SMBCLIENT -L \"$name\" -N -U phynd`;		# get the list of shares on the computer
    my @lines = split( '\n', $response );
    my $i = 0;
    my @shares;
    while ( $i <= $#lines )
    {
	if ( $lines[$i] =~ /Sharename\s+Type\s+Comment/ )
	{
	    $i+=2;
	    while ( $lines[$i] !~ /^\s*$/ )
	    {
		if ( $lines[$i] =~ /^\s+(.+?)\s+Disk/ )
		{
			if ( $1 !~ /\$$/ )
			{
			push @shares, $1;
			}
		}
	    $i++;
	    }
	}
    $i++;
    }

    return @shares;									# return the share directories
};

# This retrieves and writes a list of all files in a share
# ARGS:
#   $host - netbios hostname of the computer to connect to
#   $share - sharename to connect to
#   $workgroup - workgroup of the computer

sub GetShareDir
{
	my $host = shift;
	my $share = shift;
	my $workgroup = shift;
	my $currdir = "";
	my $infinity = 0;
	open DIR, "$SMBCLIENT \"\\\\\\\\$host\\\\$share\" -N -c \"recurse;dir\" |" or die "$host caused Phynd to crash...$!\n\n";

# get a recursive listing of the share directory

$stoptime = time() + 50;				# add 30 seconds to the current time

		while (<DIR>)
		{
		    if (time() > $stoptime)		# stop scanning if we can't finish in 30 seconds (more than reasonable)
{break;}

	
		    if ( /Password\:/ ) {last; }
		    if ( /Added interface ip=/ ) { next; }
		    if ( /Got a positive name query response from/ ) { next; }
		    if ( /directory recursion is now on/ ) { next; }
		    if ( /\d+ blocks of size \d+\. \d+ blocks available/ ) { next; }
		    if ( /^\s*$/ ) { next; }
		    if ( /^\s*(.+?)\s+[ASDRH]{0,5}\s*(\d+)\s{2}\w{3}\s\w{3}\s{1,2}\d{1,2}\s\d{2}:\d{2}:\d{2}\s\d{4}$/ )
			{
#				$line = "\L\\\\$host\\$share$currdir\\$1:$2:$workgroup\n";

			    $size = $2;

			    if ( ( $size != 0 ) )		# this indexes files since size > 0

				    {
					$filename = $1;				# replace " and ' with \" and \' for mySQL
					$filename =~ s/\'/\\\'/g;
					$filename =~ s/\"/\\\"/g;

					$currdirescaped = $currdir;		# replace " ' and \ for directories
					$currdirescaped =~ s/\\/\\\\/g;
					$currdirescaped =~ s/\'/\\\'/g;
					$currdirescaped  =~ s/\"/\\\"/g;

					$host =~ s/\'/\\\'/g;			# replace " and ' with \" and \' for mySQL
					$host =~ s/\"/\\\"/g;
					$host =~ s/\\/\\\\/g;

					$shareescaped = $share;		# replace " and ' with \" and \' for mySQL
					$shareescaped =~ s/\'/\\\'/g;
					$shareescaped =~ s/\"/\\\"/g;

					$workgroup =~ s/\'/\\\'/g;
					$workgroup =~ s/\"/\\\"/g;


					$directory = $shareescaped . $currdirescaped;
					
# add a file entry into the database

my $sth = $dbh->prepare("INSERT INTO `$CATALOGINDEXTEMP` (`computer`,`directory` , `filename` , `size` , `workgroup` ) VALUES ('$host', '$directory', '$filename' , '$size' , '$workgroup')");

$sth->execute;
				    }

			}
			elsif ( /^(\\.+?)\\?$/ )			# this indexes the directories
			{
				$tmp = $1;
				$currdir =~ /\\([^\\]+)$/;
				if ( $currdir ne '' and $tmp =~ /\\\Q$1\E$/ )
				{
				$infinity++;
				}
				else
				{
					$infinity = 0;
				}
				if( $infinity == 20 )		# set limit for directory recursion
				{
				    print "\n : ERROR IR=$host,$share Directory too deep"; last;
				}

				$currdir = $tmp;
		#		print "\L\\\\$host\\$share$currdir:0:$workgroup\n";


					$currdirescaped = $currdir;			# replace escaped characters
					$currdirescaped =~ s/\\/\\\\/g;
					$currdirescaped =~ s/\'/\\\'/g;
					$currdirescaped  =~ s/\"/\\\"/g;

					$host =~ s/\'/\\\'/g;
					$host =~ s/\"/\\\"/g;


					$shareescaped = $share;
					$shareescaped =~ s/\'/\\\'/g;
					$shareescaped =~ s/\"/\\\"/g;

					$workgroup =~ s/\'/\\\'/g;
					$workgroup =~ s/\"/\\\"/g;

# add a directory entry into the database

my $sth = $dbh->prepare("INSERT INTO `$CATALOGINDEXTEMP` (`computer` ,  `directory` , `filename` , `size` , `workgroup` ) VALUES ('$host', '$shareescaped$currdirescaped', '' , '0' , '$workgroup')");

$sth->execute;

			}
		    }

		close DIR;		# close the share listing


}




# Scans through all IP addresses in the iplist - this is the first function called

sub IPlist
{
my $numrows = $stopval - $startval + 1;

$dbh = DBI->connect($DSN, $DATABASEUSER, $DATABASEPASS) or die "Can\'t connect to the DB: $DBI::errstr\n";

$query = "SELECT * FROM $CATALOGIPLIST LIMIT $startval,$numrows";		# gets all the IP's in this range to scan

$sth = $dbh->prepare($query);
$sth->execute;



while ($row = $sth->fetchrow_hashref)		# adds all the IP's to an array
{
push(@iplist, "$row->{ip}");
}



for ($count = 0; $count < $numrows; $count++)		# loops through all the IP's to scan
{

$IPa1 = $iplist[$count];

		chomp($IPa1);
		($host, $wgroup) = IPtoHost($IPa1); #amp		# gets the hostname of the IP
		if ($host eq 'none' ) {next;}
                if ($ipoverrulehost)
		{
		    @sharelist = HostShareList( $IPa1 ); # amp		# gets the share list of the computer
		}
		else
		{
		@sharelist = HostShareList( $host ); #amp
		}
if (@sharelist) 
{
print "\n$IPa1 : $wgroup : $host : ",join( ", ", @sharelist ); 		# prints out the computer we are scanning
}

		foreach $share ( @sharelist )
		{
		    if ($ipoverrulehost)
			{
			 
		GetShareDir( $IPa1, $share, $wgroup,$dbh); #amp	# gets the share directory

}
		    else
		    {

			GetShareDir( $host, $share, $wgroup,$dbh); #amp	# gets the share directory
			}

		}


}


$dbh->disconnect();


};





print "\nPhynd Network Indexer, v2.0\n";
print "Scan started at ", scalar localtime(time);
print "\nScanning IP Addresses...\n\n";

# connect to the database
my $db = DBI->connect($DSN, $DATABASEUSER, $DATABASEPASS) or die "Can\'t connect to the DB: $DBI::errstr\n";

#get the number of rows (IP addresses to scan)
my $sth = $db->prepare("SHOW TABLE STATUS FROM phynd like '$CATALOGIPLIST' ");
$sth->execute;

$row = $sth->fetchrow_hashref;

# set the number of IP's to search
$numlines = $row->{Rows};


if ($numlines < $totalthreads)			# workaround if there are more threads than IP addresses, my algorithm doesn't like that at the moment, but if you have more threads than IP's, thats pretty weak....well you get the idea
{
$totalthreads = $numlines;# - 1;
}



# calculate the number of IP addresses to be spread over each thread
$IPperthread = int($numlines / $totalthreads);

#Each thread calls the IPlist function, which in turn scans through a section of the IP blocks
#The $startval is the first entry to scan, and the function scans through the $stopval

# if the number if threads divides evenly into the number of IP's, spawn a thread for each section
if (($totalthreads * $IPperthread) == $numlines)
{
	for ($j = 0; $j < $totalthreads; $j++)
	{
	new Thread "IPlist",$startval = ($IPperthread) * $j , $stopval = (($IPperthread) * ($j + 1)) - 1 ;
	}
}

# if the number of threads does not divide evenly into the number of IP's, create all but 1 thread, and then create the last thread to search the last section of the IP's
else
{
	$IPperthread = int($numlines / ($totalthreads));		# divide up the work of the threads
	for ($j = 0; $j < $totalthreads - 1; $j++)		# create all but 1 thread
	{
	new Thread "IPlist",$startval = ($IPperthread) * $j , $stopval = (($IPperthread) * ($j + 1)) - 1;
	}

	# create the last thread to search the last part of the IP's
	new Thread "IPlist" , $startval = ($IPperthread * ($totalthreads - 1)), $stopval = $numlines - 1;

}

# join all the threads so they clean up nicely as they exit
$_->join() foreach threads->list;




print "\nSleeping for 30s\n";

sleep(30);


print "\n\nScan ended at ", scalar localtime(time)," \n";




######################################



# connect to the database
#my $db = DBI->connect($DSN, $DATABASEUSER, $DATABASEPASS) or die "Can\'t connect to the DB: $DBI::errstr\n";


#remove restricted words from the database
my $sth = $db->prepare("SELECT * FROM `$CATALOGRESTRICTWORDS` ");
$sth->execute;

while ($row = $sth->fetchrow_hashref)
{
$word = $row->{word};

my $sa= $db->prepare("DELETE FROM `$CATALOGINDEXTEMP` WHERE filename LIKE '%$word%' ");
$sa->execute;

}





my $sth = $db->prepare("SELECT * FROM $CATALOGCATEGORIES");
$sth->execute;


while ($row = $sth->fetchrow_hashref)
{
$extensionlist = $row->{extensions};

@extensions = split(":" , $extensionlist);

$subtable = $extensions[0];

my $sth = $db->prepare("
CREATE TABLE `$DATABASE`.`temp$subtable` (
`computer` varchar( 40 ) NOT NULL default '',
`directory` varchar( 255 ) NOT NULL default '',
`filename` varchar( 255 ) NOT NULL default '',
`size` int( 10 ) unsigned NOT NULL default '0',
`workgroup` varchar( 40 ) NOT NULL default '',
KEY `directory` ( `directory` ) ,
KEY `filename` ( `filename` )
) TYPE = MYISAM ");

$sth->execute;



foreach $extn (@extensions)
{
my $sth = $db->prepare("
INSERT INTO `$DATABASE`.`temp$subtable`
SELECT * FROM `$DATABASE`.`$CATALOGINDEXTEMP` WHERE `filename` LIKE '%.$extn%'
");
$sth->execute;

}



# delete the old
my $sth = $db->prepare("DROP TABLE `$subtable`");
$sth->execute;

# rename the temporary catalog to the searchable one
my $sth = $db->prepare("ALTER TABLE `temp$subtable` RENAME `$subtable`");
$sth->execute;

}



# delete the old CATALOG Index
my $sth = $db->prepare("DROP TABLE `$CATALOGINDEX`");
$sth->execute;


# rename the temporary catalog to the searchable one
my $sth = $db->prepare("ALTER TABLE `$CATALOGINDEXTEMP` RENAME `$CATALOGINDEX`");
$sth->execute;







$totalsize = 0;
$totalfiles = 0;
$directories = 0;

my $sa = $db->prepare("SELECT size FROM `$CATALOGINDEX` ");
$sa->execute;

while ($row = $sa->fetchrow_hashref)
{
$filesize = $row->{size};

$directories++ if $row->{size} == "";			# grab all the directories
$totalsize += $filesize;						# aggregate all the file sizes
$totalfiles++;							# count all the entries in the database

}

$totalfiles = $totalfiles - $directories;			# to get total files, subtract (entries - directories)

my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$totalfiles' WHERE `statistic` = 'totalfiles' ");
$sa->execute;

my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$totalsize' WHERE `statistic` = 'totalsize' ");
$sa->execute;






# create the temporary catalog, with no data

my $sth = $db->prepare("
CREATE TABLE `$DATABASE`.`$CATALOGINDEXTEMP` (
`computer` varchar( 40 ) NOT NULL default '',
`directory` varchar( 255 ) NOT NULL default '',
`filename` varchar( 255 ) NOT NULL default '',
`size` int( 10 ) unsigned NOT NULL default '0',
`workgroup` varchar( 40 ) NOT NULL default '',
KEY `directory` ( `directory` ) ,
KEY `filename` ( `filename` )
) TYPE = MYISAM ");

$sth->execute;


#$db->disconnect();


































# this section of code renames the temporary database, and then clears a new temporary database
# if you change the structure of the CATALOG, then you have to edit this to reflect those changes



# delete the old CATALOG Index
#my $sth = $db->prepare("DROP TABLE `$CATALOGINDEX`");
#$sth->execute;


# rename the temporary catalog to the searchable one
#my $sth = $db->prepare("ALTER TABLE `$CATALOGINDEXTEMP` RENAME `$CATALOGINDEX`");
#$sth->execute;

# create the temporary catalog, with no data
#my $sth = $db->prepare("
#CREATE TABLE `$DATABASE`.`$CATALOGINDEXTEMP` (
#`computer` varchar( 40 ) NOT NULL default '',
#`directory` varchar( 255 ) NOT NULL default '',
#`filename` varchar( 255 ) NOT NULL default '',
#`size` int( 10 ) unsigned NOT NULL default '0',
#`workgroup` varchar( 40 ) NOT NULL default '',
#KEY `directory` ( `directory` ) ,
#KEY `filename` ( `filename` )
#) TYPE = MYISAM ");

#$sth->execute;


#remove restricted words from the database
#my $sth = $db->prepare("SELECT * FROM `$CATALOGRESTRICTWORDS` ");
#$sth->execute;

#while ($row = $sth->fetchrow_hashref)
#{
#$word = $row->{word};

#my $sa= $db->prepare("DELETE FROM `$CATALOGINDEX` WHERE filename LIKE '%$word%' ");
#$sa->execute;

#}







# generate statistics....place the results in the database

# Audio files - mp3:ogg:shn:wav
# Video Files - mov:mpg:avi:mpeg:asx:asf:wmv:rm
# Compressed Files - zip:arj:rar:ace:gz:tar
# Documents - doc:pdf:html:htm:txt:rtf
# Image Files - jpg:gif:bmp:png:pcx

#my $sa = $db->prepare("SELECT size FROM `$CATALOGINDEX` WHERE filename LIKE '%mp3%' OR filename LIKE '%ogg%' OR filename LIKE '%shn' OR filename LIKE '%wav%' ");
#$sa->execute;

#$audiofiles = $sa->rows;


#my $sa = $db->prepare("SELECT size FROM `$CATALOGINDEX` WHERE filename LIKE '%mov%' OR filename LIKE '%mpg%' OR filename LIKE '%avi' OR filename LIKE '%mpeg%' OR filename LIKE '%asx%' OR filename LIKE '%asf' OR filename LIKE '%wmv%' OR filename LIKE '%rm%' ");
#$sa->execute;

#$videofiles = $sa->rows;


#my $sa = $db->prepare("SELECT size FROM `$CATALOGINDEX` WHERE filename LIKE '%zip%' OR filename LIKE '%arj%' OR filename LIKE '%rar' OR filename LIKE '%ace%' OR filename LIKE '%gz%' OR filename LIKE '%tar' ");
#$sa->execute;

#$compressedfiles = $sa->rows;


#my $sa = $db->prepare("SELECT size FROM `$CATALOGINDEX` WHERE filename LIKE '%doc%' OR filename LIKE '%pdf%' OR filename LIKE '%html' OR filename LIKE '%htm%' OR filename LIKE '%txt%' OR filename LIKE '%rtf' ");
#$sa->execute;

#$documentfiles = $sa->rows;


#my $sa = $db->prepare("SELECT size FROM `$CATALOGINDEX` WHERE filename LIKE '%jpg%' OR filename LIKE '%gif%' OR filename LIKE '%bmp' OR filename LIKE '%png%' OR filename LIKE '%pcx%' ");
#$sa->execute;

#$imagefiles = $sa->rows;



#$totalsize = 0;
#$totalfiles = 0;
#$directories = 0;

#my $sa = $db->prepare("SELECT size FROM `$CATALOGINDEX` ");
#$sa->execute;

#while ($row = $sa->fetchrow_hashref)
#{
#$filesize = $row->{size};

#$directories++ if $row->{size} == "";			# grab all the directories
#$totalsize += $filesize;						# aggregate all the file sizes
#$totalfiles++;							# count all the entries in the database

#}

#$totalfiles = $totalfiles - $directories;			# to get total files, subtract (entries - directories)






#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$audiofiles' WHERE `statistic` = 'audiofiles' ");
#$sa->execute;

#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$videofiles' WHERE `statistic` = 'videofiles' ");
#$sa->execute;

#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$documentfiles' WHERE `statistic` = 'documentfiles' ");
#$sa->execute;

#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$imagefiles' WHERE `statistic` = 'imagefiles' ");
#$sa->execute;

#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$directories' WHERE `statistic` = 'directories' ");
#$sa->execute;

#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$compressedfiles' WHERE `statistic` = 'compressedfiles' ");
#$sa->execute;

#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$totalfiles' WHERE `statistic` = 'totalfiles' ");
#$sa->execute;

#my $sa = $db->prepare("UPDATE `$CATALOGSTATS` SET `value` = '$totalsize' WHERE `statistic` = 'totalsize' ");
#$sa->execute;













