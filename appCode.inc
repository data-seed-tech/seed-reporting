<?php
/////////////////////////////////////
//            appCode              //
/////////////////////////////////////
$appCode  =	@$_POST['app'];
if($appCode == "")
    $appCode  = @$_GET['app'];
//print($appCode);

if($appCode != "")
{
    $query_app = "SELECT seed_apps.appCode, seed_apps.appName, seed_apps.appDescription, seed_apps.icon
                FROM seed_apps WHERE seed_apps.appCode = '".$appCode."';";
    $result_app = $conn -> query($query_app);
    $row_app = $result_app -> fetch_object();

    //$appCode = $row_app->appCode;
    $appName = $row_app->appName;
    $appDescription = $row_app->appDescription;
    $appIcon = $row_app->icon;
}
else
{
    $appName = "";
    $appDescription = "";
    $appIcon = "";
}

?>