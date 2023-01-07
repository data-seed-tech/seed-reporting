<?php
require_once("../connection.inc");
require_once("../appCode.inc");

/////////////////////////////////////
//                PAGE              //
/////////////////////////////////////
$page =	@$_POST['p'];
if($page == "")
{
    $page = @$_GET['p'];
}
if($page == "")
{
    $page = 1;
}
//print($page);


//$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$for =	@$_POST['hdnFor'];
$msg = '';


$ip  =	@$_POST['txtIP'];
if($ip == "")
    $ip  =	@$_POST['ip'];
if($ip == "")
    $ip  =	@$_GET['ip'];
//print($ip);







if($ip != '')
{
    $hostname = gethostbyaddr($ip);
}
else
{
    $hostname = "";
}

$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip)); 
$country = $ipdat->geoplugin_countryName; 
$city = $ipdat->geoplugin_city; 


//************************************************************************
//**                                     SAVE                                       **
//************************************************************************
if ($for == "SAVE")
{
    $query = "INSERT INTO hosts (IP, hostName) VALUES ('". $ip."', '". $hostname."');"; 
    //print($query);
    $result = $conn -> query($query);
    $err    = $conn->error;

    $msg = "SAVE! " . $err;
}
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../styles.css">
    <script language=javascript>
    function GetHost()
    {
        document.frmForm.hdnFor.value = "GET_HOST";
        document.frmForm.submit();
    }
    function Save()
    {
        document.frmForm.hdnFor.value = "SAVE";
        document.frmForm.submit();
    }
    </script>
</head>
    
<body>
<TABLE width="100%" cellpadding="3" cellspacing="0" class='clsFineBorder'>
	<tr>
            <td bgcolor="#e6f2ff" style="text-align:center">
                <?php print("&nbsp;".$msg) ?>
            </td>
	    <td bgcolor="#e6f2ff" style="text-align:right">
                <a href="./update_host.php">Bulk Update</a>
                &nbsp;&nbsp;&nbsp;
            </td>
        </tr>
</TABLE>
<br/>

<FORM ID=frmForm NAME=frmForm METHOD='post'>
    <input type="text" id='txtIP' name='txtIP' value='<?php print($ip) ?>'>
    <input type="text" id='txtHostName' name='txtHostName' style='width: 500px' value='<?php print($hostname) ?>'>
    <INPUT TYPE=button ID=btnGetHost ONCLICK='GetHost()' VALUE='&nbsp;&nbsp;&nbsp;GetHost&nbsp;&nbsp;&nbsp;' style="background-color:lightblue">&nbsp;&nbsp;&nbsp;
    <INPUT TYPE=button ID=btnSave ONCLICK='Save()' VALUE='&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;' style="background-color:lightblue">&nbsp;&nbsp;&nbsp;
    <INPUT TYPE=hidden ID=hdnFor NAME=hdnFor>
</form><br/>


Country: <?php print($country); ?><br/>
City: <?php print($city); ?><br/><br/>
<hr/>

Navigation history:<br/>
<?php
require_once("../xml_functions.inc");


$no_of_records_per_page = 15;
$offset = ($page - 1) * $no_of_records_per_page;



$total_pages_sql = "SELECT count(*) FROM counter WHERE counter.Ip = '" . $ip . "';";
$result = mysqli_query($conn, $total_pages_sql);
$total_rows = mysqli_fetch_array($result)[0];
$total_pages = ceil($total_rows / $no_of_records_per_page);


$query = "SELECT VisitTime, counter.ip, Page, Referrer, hosts.hostName
        FROM counter
        LEFT JOIN hosts ON counter.IP = hosts.IP
        WHERE counter.Ip = '" . $ip . "'
        ORDER BY VisitTime ASC
        LIMIT ".$offset.", ".$no_of_records_per_page.";";

PrintHTMLfromQuery($conn, $query);



if($page > 1)
{
?>
<a href='host.php?app=<?php print($appCode); ?>&amp;ip=<?php print($ip); ?>&p=1'>[ &lt;&lt; ]</a>
&nbsp;
<a href='host.php?app=<?php print($appCode); ?>&amp;ip=<?php print($ip); ?>&p=<?php print($page - 1); ?>'>[ &lt; ]</a>
<?php
}
else
{
?>
<font color="gray">[ &lt;&lt; ]</font>
&nbsp;
<font color="gray">[ &lt; ]</font>
<?php
}
?>

&nbsp;

<font color="gray">&nbsp; [ Page <?php print($page); ?> ] &nbsp;</font>

&nbsp;

<?php
if($page < $total_pages)
{
?>
<a href='host.php?app=<?php print($appCode); ?>&amp;ip=<?php print($ip); ?>&p=<?php print($page + 1); ?>'>[ &gt; ]</a>
&nbsp;
<a href='host.php?app=<?php print($appCode); ?>&amp;ip=<?php print($ip); ?>&p=<?php print($total_pages); ?>'>[ &gt;&gt; ]</a>
<?php
}
else
{
?>
<font color="gray">[ &gt; ]</font>
&nbsp;
<font color="gray">[ &gt;&gt; ]</font>
<?php
}
?>


</body>
</html>