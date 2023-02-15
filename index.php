<?php
require_once("./connection.inc");
require_once("./counter.inc");

$appCode  = "";
$table = "";

?>

<html>
<head>
    <title>DATA-SEED for MySQL - no code / low code development</title>
    <link rel="stylesheet" type="text/css" href="./styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
</head>
<body>
<div class="header">
    <h1><a href="./" class="header">&#127968; <?php print($Site); ?></a>&nbsp;&nbsp;>&nbsp; DATA-SEED Apps</h1>
</div>

<div class="container">
    <?php require_once("./menu.inc"); ?>
    <?php require_once("./menu_left.inc"); ?>
    
    
    
    <div class="right">
    
    <b>DATA-SEED Apps</b>
    <br/><br/>
        
    <?php
    $query_menu = "SELECT seed_apps.appCode, seed_apps.appName, seed_apps.appDescription, seed_apps.icon
                    FROM seed_apps;";
    $result_menu = $conn -> query($query_menu);
    while($row_menu = $result_menu -> fetch_object())
    {
        $appCode = $row_menu->appCode;
        $appName = $row_menu->appName;
        $appDescription = $row_menu->appDescription;
        $icon = $row_menu->icon;
        ?>
        <div class="card" onclick="javascript:location.href='indexApp.php?app=<?php print ($appCode); ?>';" title="<?php print ($appDescription); ?>">
            <div class="valoare_buna"><?php print ($icon); ?> <?php print ($appName); ?></div><br/>
            
            <?php print(substr($appDescription, 0, 30) . "..."); ?>
        </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php
    }
    ?>&nbsp;
        
    
    <br/><br/>
    <?php
    ///////////////////////////////////////////////
    // APP REPORTS:
    ///////////////////////////////////////////////
    //require_once("reports.inc");
    ?>
</div>
    
</body>
</html>


