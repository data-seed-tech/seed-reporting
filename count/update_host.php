<?php
require_once("../connection.inc");
require_once("../counter.inc");

$limit = @$_GET['txtLimit'];
if($limit == '')
{
    $limit = 100;
}
?>

<html>
<head>
    <title>Update iterativ pe campul hostName din tabela visitors</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<?php //require_once("header.inc"); ?>
<div class="container">
    <?php //require_once("meniu_stg.inc"); ?>
    <?php //require_once("meniu_dr.inc"); ?>
    <?php //require_once("../menu.inc"); ?>
    
    <h1>Update iterativ pe campul hostName din tabela visitors</h1>

<?php
$azi = date('20y-m-d');
$query = "SELECT DISTINCT visitors.IP
            FROM visitors 
            WHERE visitors.IP NOT IN (SELECT IP FROM hosts)
            LIMIT " . $limit . ";";
//print $query;

$result = $conn -> query($query);
//$err =   $conn->error;
//print $err;
?>

<table>
<tr>
    <th colspan="12" style="text-align:right">
        <form id="frmForm" name="frmForm">
        Numar de inregistrari: &nbsp;<input type="text" name="txtLimit" value="<?php print ($limit); ?>" />
        <input type="submit" name="btnSubmit" value="Go" />
        </form>
    </th>
</tr>
 <tr>
    <th>#</th>
    <th>IP</th>
    <th>Host-ul IP-ului</th>
 </tr>

<?php
$i = 1;
/*
$nume_precedent = "";
$atribut_precedent = "";
$atribut_precedent_valoare = "";
 * 
 */
while ($row = $result ->fetch_object())
{
    $IP     = $row->IP;
    
    $culoare_data_de_valoare = "#ffffff";
    

    $hostname = gethostbyaddr($IP);
    
    $query1 = "INSERT INTO hosts (IP, hostName) VALUES ('". $IP."', '". $hostname."');"; 
    //print($query1);
    $result1 = $conn -> query($query1);
    
    if(preg_match('(googlebot|msnbot|ahrefs.com|spider.yandex)', $hostname) === 1)
    {
        $este_bot = 1;
        //print $este_bot;
        $culoare_data_de_valoare = "#ffe6e6";
    }
   
    
    ?>
    <tr>
        <td style='text-align:right;background-color:<?php print ($culoare_data_de_valoare); ?>;' title="#"><?php print($i); ?></td>
        <td style='background-color:<?php print ($culoare_data_de_valoare); ?>;'>
            <a target="_blank" href="host.php?ip=<?php print($IP); ?>" title="HOST"><?php print($IP); ?></a>
        </td>
        <td style='background-color:<?php print ($culoare_data_de_valoare); ?>;' title="Unde este gazduit IP-ul">
            <?php
                //$hostname = gethostbyaddr($IP);
                // $pos = strpos($hostname, "ahrefs.com");
                //$pos = strpos($hostname, "googlebot");
                //print $hostname;
               
                print $hostname;
            ?>
        </td>
        
    </tr>
    <?php
    $i++;
}
?>
</table>

<br/><br/>
&#9432; Raportul afiseaza un numar limitat de visitors pentru ca daca punem multe se incarca greu (de la functia <code>gethostbyaddr</code>)
     
<br><br><br><br><br>
<?php //require_once("footer.inc"); ?>
</div>
</body>
</html>
