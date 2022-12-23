<?php
require_once("../connection.inc");
//$ip = '114.119.154.82';
//$ip = '93.115.248.45';  //simpliq

//$ip = '66.249.66.198';     //crawl-66-249-66-198.googlebot.com
//$ip = '114.119.145.198';
//$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$for =	@$_POST['hdnFor'];
$msg = '';
$ip  =	@$_POST['txtIP'];
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
<TABLE width="100%" cellpadding="3" cellspacing="0" class='clsFineBorder' style="text-align: center">
	<tr>
            <TD bgcolor="#e6f2ff" style="text-align: center">
                <?php print("&nbsp;".$msg) ?>
            </TD>
	</TR>
</TABLE>
<br/>

<FORM ID=frmForm NAME=frmForm METHOD='post'>
    <input type="text" id="txtIP" name="txtIP" value="<?php print($ip) ?>">
    <input type="text" id="txtHostName" name="txtHostName" style="width: 500px" value=""<?php print($hostname) ?>">
    <input type="button" ID="btnGetHost" ONCLICK='GetHost()' VALUE='&nbsp;&nbsp;&nbsp;GetHost&nbsp;&nbsp;&nbsp;' style="background-color:lightblue">&nbsp;&nbsp;&nbsp;
    <input type="button" ID="btnSave" ONCLICK='Save()' VALUE='&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;' style="background-color:lightblue">&nbsp;&nbsp;&nbsp;
    <input type="hidden" ID="hdnFor" NAME="hdnFor">
</form>
</body>
</html>