<?php
function GetXMLfromQuery($conn, $query, $root_element_name = 'records', $element_name = 'record')
{
//print $query;

// parametrii de input:
//$query = "SELECT id_product, reference, name FROM table1";
//$root_element_name = "products";
//$element_name = "product";


// coloanele:
/*
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);

$columns = mysqli_stmt_result_metadata($stmt);

while ($field = mysqli_fetch_field($columns)) 
{
    printf("Fieldname: %s\n", $field->name);
}

mysqli_free_result($columns);
*/




// LOOP PRIN INREGISTRARI:
try 
{
    $result = $conn -> query($query);
}
catch(Exception $e)
{
    print ($e->getMessage());
} 
//print ($query);

try 
{
    $nr_coloane = $result->field_count;
    //print ($nr_coloane);
}
catch(Exception $e)
{
    print ($e->getMessage());
} 

$xml = "";
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n\r";
$xml = $xml . "<" . $root_element_name . ">\n\r";

//while ($row = $result ->fetch_object())
while ($row = $result ->fetch_row())
{
    $xml = $xml . "<" . $element_name . ">";
    $i = 0;
    while ($i < $nr_coloane)
    {
        $nume_coloana = htmlentities(mysqli_fetch_field_direct($result, $i)->name, ENT_XML1, 'UTF-8');
        
        if($nume_coloana == '')
        {
            $nume_coloana = htmlspecialchars(mysqli_fetch_field_direct($result, $i)->name);
            //return;
        }
        
        if($nume_coloana == 'count(*)')
        {
            print ("Eroare! Selectul nu poate fi count(*)! Trebuie sa aiba un alias!");
            return;
        }
        //$xml = $xml . "<" . $nume_coloana . ">" . htmlentities($row[$i], ENT_XML1, 'UTF-8') . "</" . $nume_coloana . ">";
        $xml = $xml . "<" . $nume_coloana . ">" . htmlspecialchars($row[$i]) . "</" . $nume_coloana . ">";
        //print(htmlspecialchars($row[$i]));
        $i++;
    }
    //$xml = $xml . "<reference>" . $row->reference . "</reference>";
    $xml = $xml . "</" . $element_name . ">\n\r";
}
$xml = $xml . "</" . $root_element_name . ">";

//print('xxxxx');
//print $xml;
//print('xxxxz');
$xml_object = new SimpleXMLElement($xml);
//print_r ($xml_object);
return $xml_object;
}



function GetXSLTable($root_element_name = 'records', $element_name = 'record', $link_address = 'reportItem.php', $link_id = 'id', $parent = '_rapoarte', $link_target='_blank', $action_link = "")
{
//la $parent trebuie specificat uneori tableul in care editez curent
//treaba se face punand &amp;table=
//dupa modelul din pagina jobs.php
if($action_link != "")
{
    $action_xsl = "<xsl:element name=\"td\">
                        <xsl:value-of select=\"'&#127937; '\"/>
                        <xsl:element name=\"a\">
                            <xsl:attribute name=\"target\">".$link_target."</xsl:attribute>
                            <xsl:attribute name=\"title\">Execute [".$root_element_name."]</xsl:attribute>
                            <xsl:attribute name=\"href\">./".$action_link."?".$link_id."=<xsl:value-of select=\"./".$link_id."\" /></xsl:attribute>
                            
                            <xsl:value-of select=\"./".$link_id."\" />
                        </xsl:element>
                   </xsl:element >";
    $action_xsl_th = "<th>Action</th>";
}
else
{
    $action_xsl = "";
    $action_xsl_th = "";
}

//print($action_link);
    
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<xsl:stylesheet xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\">
   <xsl:output method=\"html\"/>
       <xsl:template match=\"/\">
            <table class=\"xml_table\">
                <tr>
                    <th>#</th>
                    <xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "[1]/*\">
                        <th><xsl:value-of select=\"name(.)\"/></th>
                    </xsl:for-each>
                    ".$action_xsl_th."
                </tr>
                <xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "\">
                <xsl:element name=\"tr\">
                    <xsl:attribute name=\"class\">
                        <xsl:choose>
                            <xsl:when test=\"position() mod 2 = 0\">
                              <xsl:text>nok</xsl:text>
                            </xsl:when>
                        </xsl:choose>
                    </xsl:attribute>
                    <td><xsl:value-of select=\"position()\"/></td>
                    
                    <xsl:for-each select=\"./*\">
                        <xsl:element name=\"td\">
                            <xsl:if test=\"number(.)\">
                                <xsl:if test=\"name(.) != '".$link_id."'\">
                                    <xsl:attribute name=\"class\">
                                        <xsl:text>numar</xsl:text>
                                    </xsl:attribute>
                                </xsl:if>
                            </xsl:if>

                            <xsl:choose>
                                <!-- INLOCUIRE id CU LINK DACA RAPORTUL PREVEDE-->
                                <xsl:when test=\"name(.) = '".$link_id."'\">
                                    <xsl:element name=\"a\">
                                        <xsl:attribute name=\"target\">".$link_target."</xsl:attribute>
                                        <xsl:attribute name=\"title\">Go to [".$root_element_name."]</xsl:attribute>
                                        <xsl:attribute name=\"href\">./".$link_address."?".$link_id."=<xsl:value-of select=\".\" />&amp;parent=".$parent."</xsl:attribute>
                                        <xsl:value-of select=\"'&#8618; '\"/>
                                        <xsl:value-of select=\".\" />
                                    </xsl:element>
                                </xsl:when>
                                <!-- INLOCUIRE id CU LINK DACA COLOANA SE NUMESTE link-->
                                <xsl:when test=\"name(.) = 'link'\">
                                    <xsl:element name=\"a\">
                                        <xsl:attribute name=\"target\">_blank</xsl:attribute>
                                        <xsl:attribute name=\"href\"><xsl:value-of select=\".\" /></xsl:attribute>
                                        <xsl:value-of select=\"'&#8618; '\"/>
                                        <xsl:value-of select=\".\" />
                                    </xsl:element>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:choose>
                                        <!-- HIGHLIGHT OK -->
                                        <xsl:when test=\". = 'OK'\">
                                            <xsl:value-of select=\"'&#9989; '\"/> OK
                                        </xsl:when>
                                        <xsl:when test=\". = 'NOK'\">
                                            <xsl:value-of select=\"'&#10060; '\"/> NOK
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select=\".\"/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:element >
                    </xsl:for-each>
                    ".$action_xsl."
                </xsl:element>
                </xsl:for-each>
            </table>
       </xsl:template>
   </xsl:stylesheet>";


//print $xml;                                                       // PENTRU DEBUG

$xsl_object = new SimpleXMLElement($xml);
//print_r($xsl_object);
return $xsl_object;
}






function GetXSLTableSUM($conn, $query, $root_element_name = 'records', $element_name = 'record', $link_address = 'reportItem.php', $link_id = 'id', $parent = '_rapoarte', $link_target='_blank')
{
$coloaneSUM = "";
$coloaneAVG = "";
$result = $conn -> query($query);
$nr_coloane = $result->field_count;
$i = 0;

while ($i < $nr_coloane)
{
    $nume_coloana = mysqli_fetch_field_direct($result, $i)->name;
    $coloaneSUM = $coloaneSUM . "<td><xsl:value-of select=\"sum(" . $root_element_name . "/" . $element_name . "/".$nume_coloana.")\"/></td>";
    //$coloaneAVG = $coloaneAVG . "<td><xsl:value-of select=\"sum(" . $root_element_name . "/" . $element_name . "/".$nume_coloana.") div count(" . $root_element_name . "/" . $element_name . "/".$nume_coloana.")\"/></td>";
    $i++;
}
    
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<xsl:stylesheet xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\">
   <xsl:output method=\"html\"/>
       <xsl:template match=\"/\">
            <table class=\"xml_tableSUM\">
                <tr>
                    <th>#</th>
                    <xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "[1]/*\">
                        <th><xsl:value-of select=\"name(.)\"/></th>
                    </xsl:for-each>
                </tr>
                <tr><th>SUM</th>". $coloaneSUM ."</tr>
            </table>
       </xsl:template>
   </xsl:stylesheet>";


//print $xml;                                                       // PENTRU DEBUG

$xsl_object = new SimpleXMLElement($xml);
//print_r($xsl_object);
return $xsl_object;
}









function GetXSL_CSV($root_element_name = 'records', $element_name = 'record')
{
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<xsl:stylesheet xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\">
<xsl:output method=\"text\"/>
<xsl:template match=\"/\"><xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "[1]/*\"><xsl:value-of select=\"name(.)\"/>,</xsl:for-each>;
<xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "\"><xsl:for-each select=\"./*\"><xsl:value-of select=\".\"/>,</xsl:for-each>;
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>";


//print $xml;

$xsl_object = new SimpleXMLElement($xml);
//print_r($xsl_object);
return $xsl_object;
}




function GetXSL_IMPORT($root_element_name = 'records', $element_name = 'record')
{
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<xsl:stylesheet xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\">
<xsl:output method=\"text\"/>
<xsl:template match=\"/\">INSERT INTO " . $root_element_name . " (<xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "[1]/*\"><xsl:value-of select=\"name(.)\"/><xsl:choose><xsl:when test=\"position() != last()\">, </xsl:when></xsl:choose></xsl:for-each>)
VALUES <xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "\">(<xsl:for-each select=\"./*\">'<xsl:value-of select=\".\"/>'<xsl:choose><xsl:when test=\"position() != last()\">, </xsl:when></xsl:choose></xsl:for-each>)<xsl:choose><xsl:when test=\"position() != last()\">,
</xsl:when><xsl:otherwise>;
</xsl:otherwise></xsl:choose>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>";



//cealalta varianta de insert:
/*
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<xsl:stylesheet xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\">
<xsl:output method=\"text\"/>
<xsl:template match=\"/\">
<xsl:for-each select=\"/" . $root_element_name . "/" . $element_name . "\">INSERT INTO " . $root_element_name . " VALUES (<xsl:for-each select=\"./*\">'<xsl:value-of select=\".\"/>'<xsl:choose><xsl:when test=\"position() != last()\">, </xsl:when></xsl:choose></xsl:for-each>);
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>";
*/


//print $xml;

$xsl_object = new SimpleXMLElement($xml);
//print_r($xsl_object);
return $xsl_object;
}



function PrintXMLfromQuery($conn, $query, $root_element_name = 'records', $element_name = 'record')
{
// LOOP PRIN INREGISTRARI:
//print $query;
$result = $conn -> query($query);

$nr_coloane = $result->field_count;
//print $nr_coloane;
//print(mysqli_fetch_field_direct($result, 0)->name);
$xml = "";
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
$xml = $xml . "<" . $root_element_name . ">";
//while ($row = $result ->fetch_object())
while ($row = $result -> fetch_row())
{
    //print($row[0]);
    $xml = $xml . "<" . $element_name . ">";
    $i = 0;
    while ($i < $nr_coloane)
    {
        //$nume_coloana = htmlentities(mysqli_fetch_field_direct($result, $i)->name, ENT_XML1, 'UTF-8');
        $nume_coloana = mysqli_fetch_field_direct($result, $i)->name;
        //print($nume_coloana);
        if($nume_coloana == 'count(*)')
        {
            print ("Eroare! Selectul nu poate fi count(*)! Trebuie sa aiba un alias!");
            return;
        }
        //$xml = $xml . "<" . $nume_coloana . ">" . htmlentities($row[$i], ENT_XML1, 'UTF-8') . "</" . $nume_coloana . ">";
        $xml = $xml . "<" . $nume_coloana . ">" . $row[$i] . "</" . $nume_coloana . ">";
        $i++;
    }
    //$xml = $xml . "<reference>" . $row->reference . "</reference>";
    $xml = $xml . "</" . $element_name . ">";
}
$xml = $xml . "</" . $root_element_name . ">";

header("Content-Type: text/xml; charset=UTF-8");

print $xml;
}



function PrintHTMLfromQuery($conn, $query)
{
    $xml = GetXMLfromQuery($conn, $query);
    //print_r ($xml);

    $proc = new XSLTProcessor();

    //$xslTable = GetXSLTable($table_parent, $table_parent, 'entityEdit.php', $parentIdColumnFK, "&amp;app=".$appCode."&amp;table=".$table_parent, "_self");
    $xslTable = GetXSLTable();
    //print ($xslTable);

    $proc->importStyleSheet($xslTable);
    $xmlTable = $proc->transformToXML($xml);

    print ($xmlTable);
    print ("<br /><br />");
}


function GetXSLEditForm($table, $coloane, $valori, $parentIdColumn = '', $parentIdValue = '', $conn = null, $appCode = '')
{
//print_r($valori); 
//print($parentIdColumn);
if(sizeof($valori) === 0)
{
    foreach($coloane as $coloana)
    {
        $valori[$coloana['COLUMN_NAME']] = '';
    }
}

$element = "";
$i = 1;
foreach($coloane as $coloana)
{
    //print_r($coloana);
    $element = $element . "<tr>";
    $element = $element . "<td>".$i."</td>";
    if($coloana['COLUMN_KEY'] === 'PRI')
    {
        $element = $element . "<td style=\"font-weight:bold\">".$coloana['COLUMN_NAME']." &#128273; </td>";
    }
    else
    {
        if($coloana['IS_NULLABLE'] === 'NO')
        {
            $element = $element . "<td>".$coloana['COLUMN_NAME']." *</td>";
        }
        else
            {
            $element = $element . "<td>".$coloana['COLUMN_NAME']."</td>";
        }
    }
    //print($coloana['COLUMN_NAME']."=".$valori[$coloana['COLUMN_NAME']].";");
    
    $element = $element . "<td>";
    switch ($coloana['DATA_TYPE']) 
    {
        case "varchar":
            if($coloana['CHARACTER_MAXIMUM_LENGTH'] < 100)
            {
                //print("aaa");
                if($coloana['REFERENCED_TABLE_NAME'] != "")
                {
                    //print("aaa");
                    if($valori[$coloana['COLUMN_NAME']] === "")
                    {
                        //print("aaa");
                        if($coloana['COLUMN_NAME'] === $parentIdColumn)
                        {
                            //print("aaa");
                            $element = $element . "<xsl:element name=\"select\">";
                            $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                            $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $parentIdValue, true);
                            //$s = "";
                            $element = $element . $s;
                            //$element = $element . "</xsl:element>";
                        }
                        else
                        {
                            //print("aaa");
                            $element = $element . "<xsl:element name=\"select\">";
                            $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                            $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], "", false);
                            //$s = "";
                            $element = $element . $s;
                            //$element = $element . "</xsl:element>";
                        }
                    }
                    else
                    {
                        //print("zzzzzzzzzzzzz");
                        $element = $element . "<xsl:element name=\"select\">";
                        $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                        //$s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $valori[$coloana['COLUMN_NAME']], true);
                        $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], htmlspecialchars($valori[$coloana['COLUMN_NAME']]), false);
                        $element = $element . $s;
                        //$element = $element . "<xsl:element name=\"option\">aaa";
                        //$element = $element . "    <xsl:attribute name=\"value\">aaa</xsl:attribute>";
                        //$element = $element . "     <xsl:attribute name=\"id\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                        //$element = $element . "</xsl:element>";
                    }

                    //$element = $element . $s;
                    
                }
                else
                {
                    $element = $element . "<xsl:element name=\"input\">";
                    $element = $element . "    <xsl:attribute name=\"type\">text</xsl:attribute>
                                               <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                    $element = $element . "    <xsl:attribute name=\"value\">".htmlspecialchars($valori[$coloana['COLUMN_NAME']])."</xsl:attribute>";
                    $element = $element . "    <xsl:attribute name=\"maxlength\">".$coloana['CHARACTER_MAXIMUM_LENGTH']."</xsl:attribute>";
                    //$element = $element . "</xsl:element>";
                    //print("qqqqqqqq");
                }
            }
            else
            {
                $element = $element . "<xsl:element name=\"textarea\">";
                $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                $element = $element . "     <xsl:attribute name=\"rows\">8</xsl:attribute>";
                $element = $element . "     <xsl:attribute name=\"cols\">100</xsl:attribute>";
                $element = $element . htmlspecialchars($valori[$coloana['COLUMN_NAME']]);
                //$element = $element . "</xsl:element>";
            }
            break;
        case "char":
            if($coloana['REFERENCED_TABLE_NAME'] != "")
            {
                //print("aaa");
                if($valori[$coloana['COLUMN_NAME']] === "")
                {
                    //print("aaa");
                    if($coloana['COLUMN_NAME'] === $parentIdColumn)
                    {
                        //print("aaa");
                        $element = $element . "<xsl:element name=\"select\">";
                        $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                        $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $parentIdValue, true);
                        //$s = "";
                        $element = $element . $s;
                        //$element = $element . "</xsl:element>";
                    }
                    else
                    {
                        //print("aaa");
                        $element = $element . "<xsl:element name=\"select\">";
                        $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                        $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], "", false);
                        //$s = "";
                        $element = $element . $s;
                        //$element = $element . "</xsl:element>";
                    }
                }
                else
                {
                    //print("zzzzzzzzzzzzz");
                    $element = $element . "<xsl:element name=\"select\">";
                    $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                    $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $valori[$coloana['COLUMN_NAME']], false);
                    //$s = "<option>aaa</option>";
                    $element = $element . $s;
                    //$element = $element . "<xsl:element name=\"option\">aaa";
                    //$element = $element . "    <xsl:attribute name=\"value\">aaa</xsl:attribute>";
                    //$element = $element . "     <xsl:attribute name=\"id\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                    //$element = $element . "</xsl:element>";
                }

                //$element = $element . $s;
            }
            else
            {
                $element = $element . "<xsl:element name=\"input\">";
                $element = $element . "    <xsl:attribute name=\"type\">text</xsl:attribute>
                                           <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                $element = $element . "    <xsl:attribute name=\"value\">".htmlspecialchars($valori[$coloana['COLUMN_NAME']])."</xsl:attribute>";
                $element = $element . "    <xsl:attribute name=\"maxlength\">".$coloana['CHARACTER_MAXIMUM_LENGTH']."</xsl:attribute>";
                //$element = $element . "</xsl:element>";
            }
            break;
        case "date":
            $element = $element . "<xsl:element name=\"input\">";
            $element = $element . "    <xsl:attribute name=\"type\">date</xsl:attribute>
                                       <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
            $element = $element . "    <xsl:attribute name=\"value\">".$valori[$coloana['COLUMN_NAME']]."</xsl:attribute>";
            //$element = $element . "</xsl:element>";
            break;
        case "int" or "smallint" or "tinyint":
            //print("aaa");
            if($coloana['REFERENCED_TABLE_NAME'] != "")
            {
                //print("aaa");
                if($valori[$coloana['COLUMN_NAME']] === "")
                {
                    //print("aaa");
                    if($coloana['COLUMN_NAME'] === $parentIdColumn)
                    {
                        //print("aaa");
                        $element = $element . "<xsl:element name=\"select\">";
                        $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                        $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $parentIdValue, true);
                        //$s = "";
                        $element = $element . $s;
                        //$element = $element . "</xsl:element>";
                    }
                    else
                    {
                        //print("aaa");
                        $element = $element . "<xsl:element name=\"select\">";
                        $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                        $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], "", false);
                        //$s = "";
                        $element = $element . $s;
                        //$element = $element . "</xsl:element>";
                    }
                }
                else
                {
                    //print("zzzzzzzzzzzzz");
                    $element = $element . "<xsl:element name=\"select\">";
                    $element = $element . "     <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                    $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $valori[$coloana['COLUMN_NAME']], true);
                    //$s = "<option>aaa</option>";
                    $element = $element . $s;
                    //$element = $element . "<xsl:element name=\"option\">aaa";
                    //$element = $element . "    <xsl:attribute name=\"value\">aaa</xsl:attribute>";
                    //$element = $element . "     <xsl:attribute name=\"id\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                    //$element = $element . "</xsl:element>";
                }

                //$element = $element . $s;
            }
            else
            {
                $element = $element . "<xsl:element name=\"input\">";
                $element = $element . "    <xsl:attribute name=\"type\">number</xsl:attribute>
                                           <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
                $element = $element . "    <xsl:attribute name=\"value\">".$valori[$coloana['COLUMN_NAME']]."</xsl:attribute>";
                //$element = $element . "</xsl:element>";
            }
            break;
        case "decimal":
            $element = $element . "<xsl:element name=\"input\">";
            $element = $element . "    <xsl:attribute name=\"type\">number</xsl:attribute>
                                       <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
            $element = $element . "    <xsl:attribute name=\"value\">".$valori[$coloana['COLUMN_NAME']]."</xsl:attribute>";
            //$element = $element . "</xsl:element>";
            break;
        case "boolean":
            $element = $element . "<xsl:element name=\"input\">";
            $element = $element . "    <xsl:attribute name=\"type\">checkbox</xsl:attribute>
                                       <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
            $element = $element . "    <xsl:attribute name=\"value\">".$valori[$coloana['COLUMN_NAME']]."</xsl:attribute>";
            //$element = $element . "</xsl:element>";
            break;
        default:
            $element = $element . "<xsl:element name=\"input\">";
            $element = $element . "    <xsl:attribute name=\"type\">text</xsl:attribute>
                                       <xsl:attribute name=\"name\">".$coloana['COLUMN_NAME']."_".$coloana['DATA_TYPE'].$coloana['ORDINAL_POSITION']."</xsl:attribute>";
            $element = $element . "    <xsl:attribute name=\"value\">".htmlspecialchars($valori[$coloana['COLUMN_NAME']])."</xsl:attribute>";
            //$element = $element . "</xsl:element>";
    }
    $sfat = "";
    $sfat_text = "";
    $readonly = false;
    if($coloana['EXTRA'] === 'auto_increment')
    {
        $element = $element . "    <xsl:attribute name=\"readonly\" />";
        $element = $element . "    <xsl:attribute name=\"class\">read_only</xsl:attribute>";
        $sfat       = "&#127920; ";
        $sfat_text  = "Auto-completed field";
        //$readonly = true;
    }
    if($coloana['COLUMN_NAME'] === $parentIdColumn)
    {
        //$element = $element . "    <xsl:attribute name=\"readonly\" />";
        //$element = $element . "    <xsl:attribute name=\"value\">".$parentIdValue."</xsl:attribute>";
        $sfat       = "&#127920; ";
        $sfat_text  = "Auto-completed field";
        $readonly = true;
    }
    $element = $element . "</xsl:element>";
    $element = $element . "</td>";
    $element = $element . "<td>".$coloana['COLUMN_COMMENT']."</td>";
    
    if($coloana['CHARACTER_MAXIMUM_LENGTH'] != "")
    {
        $sfat       = "&#128129; ";
        $sfat_text  = "Maximum length: " . $coloana['CHARACTER_MAXIMUM_LENGTH'];
    }
    $element = $element . "<td title=\"".$sfat_text."\"> ".$sfat."</td>";
    
    
    
    
    if($coloana['REFERENCED_TABLE_NAME'] != "")
    {
        $element = $element . "<td>";
        $element = $element . "[<a target=\"_blank\" href=\"./entityView.php?app=".$appCode."&amp;table=".$coloana['REFERENCED_TABLE_NAME']."\">".$coloana['REFERENCED_TABLE_NAME']."</a>]";
        $element = $element . "</td>";
        
    }
    else
    {
        $element = $element . "<td></td>";
    }
    
    /*
    if($coloana['REFERENCED_TABLE_NAME'] != "")
    {
        if($valori[$coloana['COLUMN_NAME']] === "")
        {
            if($coloana['COLUMN_NAME'] === $parentIdColumn)
                $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $parentIdValue, $readonly);
            else
                $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], "", $readonly);
        }
        else
        {
            $s = GetHTMLSelect($conn, $coloana['REFERENCED_TABLE_NAME'], $valori[$coloana['COLUMN_NAME']], $readonly);
        }
        
        $element = $element . "<td>";
        $element = $element . $s;
        $element = $element . "</td>";
    }
    else
    {
        $element = $element . "<td></td>";
    }
    */
    
    
    
        
    $element = $element . "</tr>";

    $i = $i + 1;
}
    
    
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<xsl:stylesheet xmlns:xsl=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\">
   <xsl:output method=\"html\"/>
       <xsl:template match=\"/\">
            <table class=\"form_table\">
                <tr>
                    <th>#</th>
                    <th colspan=\"3\">" . $table . "</th>
                    <th>&#32; [ &#8505; ]</th>
                    <th>link</th>
                </tr>
                " . $element . "
            </table>
       </xsl:template>
   </xsl:stylesheet>";


//print $xml;

$xsl_object = new SimpleXMLElement($xml);
//print_r($xsl_object);
return $xsl_object;
}


function GetHTMLSelect($conn, $table, $id_value, $readonly = false)
{
    //print('a='.$id_value);
    //$readonly = $readonly ? 'true' : 'false';
    //print('a='.$readonly);
    /////////////////////////////////////
    //          current_schema         //
    /////////////////////////////////////
    $query = "SELECT DATABASE() as current_schema;";
    $result = $conn -> query($query);
    $row = $result -> fetch_object();
    $current_schema  = $row -> current_schema;

    /////////////////////////////////////
    //              id_name           //
    /////////////////////////////////////
    $query = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_schema = '" . $current_schema . "'
                AND table_name = '" . $table . "'
                AND COLUMN_KEY = 'PRI';";
    //print($query);
    $result = $conn -> query($query);
    $row = $result -> fetch_object();
    $id_name = $row->COLUMN_NAME;
    
    /////////////////////////////////////
    //          displayable_name       //
    /////////////////////////////////////
    $query = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_schema = '" . $current_schema . "'
                AND table_name = '" . $table . "'
                AND (DATA_TYPE = 'varchar' OR DATA_TYPE = 'char');";
    //print($query);
    $result = $conn -> query($query);
    if(mysqli_num_rows ($result) > 0)
    {
        $row = $result -> fetch_object();
        $displayable_name = $row->COLUMN_NAME;
    }
    else
    {
        $query = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_schema = '" . $current_schema . "'
                AND table_name = '" . $table . "'
                AND COLUMN_KEY = 'PRI';";
        //print($query);
        $result = $conn -> query($query);
        $row = $result -> fetch_object();
        $displayable_name = $row->COLUMN_NAME;
    }
    
    /////////////////////////////////////
    //              MAIN SELECT        //
    /////////////////////////////////////
    //$sel = "<select name=\"".$table."\" id=\"".$table."\">";
    //$sel = "<select id=\"".$table."\" name=\"".$row->COLUMN_NAME."_".$row->DATA_TYPE.$row->ORDINAL_POSITION."\">";
    $sel = "";
    
    if($readonly === true && $id_value != "")
    {
        if(is_numeric($id_value))
        {
            $query = "SELECT ".$id_name. ", ".$displayable_name." FROM " . $table . " WHERE ".$id_name." = ".$id_value.";";
        }
        else
        {
            $query = "SELECT ".$id_name. ", ".$displayable_name." FROM " . $table . " WHERE ".$id_name." = '".$id_value."';";
        }
    }
    else
    {
        $query = "SELECT ".$id_name. ", ".$displayable_name." FROM " . $table . ";";
        $sel = $sel . "<option value=\"\"> --- </option>";
    }
    //print($query);
    $result = $conn -> query($query);

    while($row = $result -> fetch_array(MYSQLI_ASSOC))
    {
        $coloane[] = $row;
    }
    

    
    foreach($coloane as $coloana)
    {
        //print($coloana[$id_name]);
        if($coloana[$id_name] === $id_value)
        {
            $sel = $sel . "<option value=\"".$coloana[$id_name]."\" selected=\"true\">".htmlspecialchars($coloana[$displayable_name])."</option>";
        }
        else
        {
            $sel = $sel . "<option value=\"".$coloana[$id_name]."\">".htmlspecialchars($coloana[$displayable_name])."</option>";
        }
    }
    //$sel = $sel . "</select>";

    //print($sel);
    return $sel;
}


