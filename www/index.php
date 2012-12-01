<?
session_start();

require_once("include.php");

$THISPAGE = $_SERVER['PHP_SELF'];


?>



<table border=0>
<tr>
<td>
<p><b><span class="zagol">SEARCH</span></b></p>

<form name="Search" method="POST" action="<?=$THISPAGE?>">

 <TABLE ALIGN="left" CELLPADDING="5" border="1">
    <TR>
      <TD width="385" height="101">
      <div>
        <div align="left"><span class="content">Search for


<select name="searchtype">
          <option value="all" SELECTED>All of the Words</option>
          <option value="phrase">The Phrase</option>
          <option value="any">Any of the Words</option>
</SELECT>

in

<select name="database">

<? GenerateDropDownDatabases(); ?>

</select>

<br><br>using the query
<input type=text name="searchfor" value="" maxlength="250">
</span>
<br><br>
<center><input type=submit value="Search"></center>
</div>


        </div></td>
    </tr>
  </table>

</form>


</td>
</tr>

<?


if (isset($_POST['searchfor']))		# a new search
{
	@session_register("searchfor","searchtype","database");

	$searchtype = $_POST['searchtype'];
	$_SESSION['searchtype'] = $searchtype;

	$database = $_POST['database'];
	$_SESSION['database'] = $database;

	$searchfor = $_POST['searchfor'];
	$_SESSION['searchfor'] = $searchfor;

	$startrow = 0;

	$IP = $_SERVER["REMOTE_ADDR"];







}
else if (isset($_POST['next200']) && isset($_POST['startrow']))		# user is continuing the previous search
{

	$searchfor = $_SESSION['searchfor'];
	$startrow = $_POST['startrow'];
	$searchtype = $_SESSION['searchtype'];
	$database = $_SESSION['database'];
}
else if (!isset($_POST['searchfor']))	# initial page load, so we just display the boxes and exit
{
echo "

</table>

</div>



</body>
</html>

";

exit();
}
else			# user trying to fuck with us, or the session has timed out
{
echo "
<tr>
<td>

<div class=\"zagol\">
<b>
An error has occured....please try your search again...
</b>
</div>
</td>
</tr>

</table>

</div>

</body>
</html>

";
}



if (($load >= $MAXLOAD) )
{
echo "
<tr>
<td>
<div class=\"zagol\">
<b>
Sorry, the server is at its maximum load.  Try again in a couple of minutes.<br>Oh yeah, did 
that hurt your feelings?  Well you broke the server.  I hope you're goddamn happy.</b>
</div>
</td>
</tr>


</table>
</div>
</body>
</html>
";

exit;
}

if (!AllowAccess($searchfor) )
{ exit; }					# check if the user is allowed access...if they are...search....if not...log to the DENIED log
						# the if statement makes sure that if the functions fails, the search does not proceed


$tom = $_SERVER['REMOTE_ADDR'];		# brent, I added this to mess with somebody....you can delete it if you want...its just temporary

echo "

<tr height=20>
<td>
</td>
</tr>

";

echo "

<tr>
<td>
";



$query = GenerateQuery($searchfor,$startrow,$searchtype,$database);

#print $query;

$dbase = DatabaseConnect();

$temptime = split(" ",microtime());
$starttime = $temptime[0] + $temptime[1];

$result = mysql_query($query);

$temptime = split(" ",microtime());
$totaltime = $temptime[0] + $temptime[1] - $starttime;
$totaltime = round($totaltime,3);






if ($startrow == 0)		# if this is a new search, and is allowed, log it
{
$logsearch = mysql_query("UPDATE `$CATALOGSTATS` SET value=(value+1) WHERE `statistic` = 'numsearches' ");

$logtimeofsearch = mysql_query("INSERT INTO `$LOGSEARCH` (`timeofsearch`) VALUES ( NOW() ) ");


}



if (!$result)
{
echo "Sorry, there was a database error....<br><br>";
echo mysql_error($result);
echo "
</td>
</tr>
</table>
</div>
</body>
</html>
";

exit;

}


if (mysql_num_rows($result) == 0)
{
echo "
<div class=\"zagol\">
<B>
Search returned no results...
</b></div>
</td>
</tr>
</table>
</div>
</body>
</html>

";

exit;
}
elseif (mysql_num_rows($result) < 200)
{	$stopsearch = 1;	}
else
{	$stopsearch = 0;	}


$browsertype = $_SERVER["HTTP_USER_AGENT"];

if (strstr($browsertype, "Konqueror"))
{
$header = "smb://";

}
else
{
$header = "file://";
}



echo "


<TABLE ALIGN=\"left\" CELLPADDING=\"2\" BORDER>
<TR><TH><div class=\"content\">File Location</div></TH><TH><div class=\"content\">Size</div></TH><TH><div class=\"content\">Workgroup</div></TH></TR>
";

while ($row = mysql_fetch_object($result))
{
$computer = $row->computer;
$dirwithdelimeter = $row->directory;
$filename = $row->filename;
$size = GetSize($row->size);
$workgroup = $row->workgroup;


echo "

<tr>
<td>
<div class=\"link\">
";

$directory = explode("\\",$dirwithdelimeter);

echo "//<a target=_NEW href=\"$header$computer\">$computer</a>/";

for ($count = 0; $count < count($directory); $count++)
{
$link = "$header$computer/";

	for($countlink = 0; $countlink < $count + 1; $countlink++)
		{
		$link = $link . $directory[$countlink] . "/";
		}

print "<a target=_NEW href=\"$link\">$directory[$count]</a>" . "/";
}

echo "

<a href=\"$link$filename\">$filename</a>

</div>
</td>

<td nowrap>
<div class=\"content\">
$size
</div>
</td>

<td nowrap>
<div class=\"content\">
$workgroup
</div>
</td>


</tr>

";

}	# end while loop

mysql_close($dbase);

echo "

</table>


</td>
</tr>
";


if (!$stopsearch)
{

$thispage = $_SERVER['PHP_SELF'];

	if ($startrow == 0)
	{
	$startrow = 200;
	}
	else
	{
	$startrow += 200;
	}

echo "

<tr height=10>
<td></td>
</tr>

<tr>
<Td>

<form name=\"ContinueSearch\" method=\"POST\" action=\"$thispage\">

<input type=hidden name=\"next200\" value=\"200\">
<input type=hidden name=\"startrow\" value=\"$startrow\">
<input type=submit value=\"Next 200 Results\">

</form>

</td>
</tr>

";
}

echo "
<tr>
<td>
<br><br><b>Search completed in $totaltime seconds.</b>
</td>

</tr>

</table>

</div>
</body>
</html>

";












################	 FUNCTIONS ###################


function GenerateQuery($searchfor,$startrow,$searchtype,$database)
{
global $CATALOGINDEX,$CATALOGCATEGORIES,$DATABASEHOST, $DATABASEUSER,$DATABASEPASS,$DATABASE;
# searchtype: all phrase any

$searchfor = str_replace("*","_",$searchfor);
$searchwords = explode (" ", $searchfor);

$column = "filename";

if ($database ==  "all")
{
$whichdatabase = $CATALOGINDEX;

//$databaseconstraint = " 1";
}
else if ($database == "dir")
{
	//$databaseconstraint = " (filename = '' AND size = 0 )";
	$column = "directory";

	$whichdatabase = $CATALOGINDEX;

}
else
{

$dbase = DatabaseConnect();

$result = mysql_query("SELECT * FROM `$CATALOGCATEGORIES` WHERE `name` = '$database' ");

if (!$result)
{
echo "Unable to retrieve category listings....";
exit;
}

$row = mysql_fetch_object($result);

$extensions = explode(":" , $row->extensions);

$whichdatabase = $extensions[0];

mysql_close($dbase);

}

if ($searchtype == "all")
{ $searchdelimeter = "AND"; }
elseif ($searchtype == "any")
{ $searchdelimeter = "OR"; }



switch ($searchtype)
{

case "all":
case "any":
	$searchstring = "(`$column` like '%$searchwords[0]%'";

	for ($i = 1; $i < sizeof($searchwords); $i++)
	{
	$searchstring = $searchstring . " $searchdelimeter `$column` like '%$searchwords[$i]%'";
	}

	$searchstring .= ")";

	break;

case "phrase":

	$searchstring = "($column like '%$searchfor%')";
	break;
}


$query = "select * from `$whichdatabase` where $searchstring limit $startrow,200";

#echo "<br><Br><br><Br>$query<br><br>";

return $query;
}




function GetSize($size)
{

switch ($size)
	{
	case 0:
	$hrsize = "0 B";
	break;

	case ($size < 1024):
	$hrsize = $size . " B";
	break;

	case ($size < 1048576):
	$hrsize = ($size / pow(2,10));
	$hrsize = round($hrsize,0);
	$hrsize = $hrsize . " K";
	break;

	case ($size > 1048575):
	$hrsize = ($size / pow(2,20));
	$hrsize = round($hrsize,2);
	$hrsize = $hrsize . " MB";
	break;
	}

return $hrsize;


}



function GenerateDropDownDatabases()
{
global $CATALOGCATEGORIES;


$dbase = DatabaseConnect();

$result = mysql_query("SELECT * from `$CATALOGCATEGORIES`");

if (!$result)
{
echo "Unable to retrieve category listings....";
exit;

}


while ($row = mysql_fetch_object($result) )
{
echo "<option value = \"$row->name\">$row->name</option>\n";

}


echo "
<option value=\"dir\">Directories</option>
<option value=\"all\">All Files</option>
";

}


function AllowAccess($searchval)
{
global $ACCESSIPLIST,$DENIEDLOG;

$dbase =DatabaseConnect();

$IP = $_SERVER["REMOTE_ADDR"];

$query ="SELECT * FROM `$ACCESSIPLIST` WHERE `ip` = '$IP' ";
$result = mysql_query($query);

if (!$result)
{
echo "Unable to verify your access privleges.....";
exit;
}


	if (mysql_num_rows($result) == 0)
    {

	print "

<tr height=20>
<td>
<Br>
<h1><b>YANKESS SUCK!!!!!!!!!!!!!!!!</b></h1>
<br>

<div class=\"zagol\"><b>Sorry, Phynd is restricted....The appropriate query information has been logged along with your IP address.</div>

</td>
</tr>

</table>

</div>

</body>

</html>


";
mysql_query("INSERT INTO `$DENIEDLOG` (`ip` , `time` , `searchfor`) VALUES ('$IP' , NOW() , '$searchval') ");


mysql_close($dbase);

	exit;


    }
else
{
return true;
}



exit;

}



function DatabaseConnect()
{
global $DATABASEHOST, $DATABASEUSER,$DATABASEPASS,$DATABASE;


$tempdatabase = mysql_connect($DATABASEHOST, $DATABASEUSER, $DATABASEPASS);
mysql_select_db($DATABASE);

return $tempdatabase;

}









?>
