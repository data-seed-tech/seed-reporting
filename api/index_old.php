<?php
// connection to mysql database:
require_once("../connection.inc");
/* CONNECTION SHOULD BE REPLACED WITH SOMETHING CUSTOMISED:
 * $conn = new mysqli("server", "user", "pasword", "database");
 *
 * BE CAREFUL TO KEEP THE NAME $conn!!!
 * 
 * PLEASE SEE INSTALLATION STEPS ONLINE: data-seed.tech!!!
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

/////////////////////////////////////
//              table              //
/////////////////////////////////////
$table  =	@$_POST['table'];
if($table == "")
{
    $table  = @$_GET['table'];
}
//print($table);


/////////////////////////////////////
//    API call  parametrers        //
/////////////////////////////////////
$whereAttr = @$_GET['whereAttr'];
if($whereAttr == "")
{
    $whereAttr = @$_POST['whereAttr'];
}

$whereValue = @$_GET['whereValue'];
if($whereValue == "")
{
    $whereValue = @$_POST['whereValue'];
}

$whereClause =  @$_GET['whereClause'];
if($whereClause == "")
{
    $whereClause = @$_POST['whereClause'];
}
if($whereClause == "like")
{
    $whereClause = "LIKE";
}
if($whereClause == "equal")
{
    $whereClause = "EQUAL";
}
if($whereClause == "=")
{
    $whereClause = "EQUAL";
}

$limit = @$_GET['limit'];
//print $limit;
if($limit == "")
{
    $limit = @$_POST['limit'];
}

$scope = @$_POST['scope'];
if($scope == "")
{
    $scope = @$_GET['scope'];
}

$format = @$_POST['format'];
if($format == "")
{
    $format = @$_GET['format'];
}
if($format == "json")
{
    $format = "JSON";
}


if($table == "")
{
    $output = "Service usage: \n"
            . "1. GET: equivalent with SELECT from entity\n\t"
            . " Example: GET https://{host}/seed/api/?table={entity name}&limit={number of records to be returned}\n\t"
            . " or:  GET https://{host}/seed/api/?table={entity name}&id={value of PK to be selected}\n\t"
            . " Parameters:\n\t"
            . "     table   = Entity name. To return all tables use * \n\t"
            . "     id      = The value of the PK to be returned\n\t"
            . "     whereAttr   = Where clause attribute\n\t"
            . "     whereValue  = Where clause value\n\t"
            . "     whereClause = Where clause: default is EQUAL, but it can be LIKE\n\t"
            . "     limit   = Number of records to be returned (no default)\n\t"
            . "     scope   = Scope: select (default), create (returns CREATE TABLE statement)\n\t"
            . "     TBD: format  = Output format: JSON (default), text (not fully implemented), XML (TBD), HTML (TBD)\n\n"
            . "2. POST: equivalent with INSERT, for one or more records\n\t"
            . " Example: POST https://{host}/seed/api/?table={entity name}\n\t"
            . " with a request body containig a JSON with the structure returned by GET method.\n\t"
            . " Parameters:\n\t"
            . "     table   = Entity name (MANDATORY)\n\t"
            . "     body    = JSON with the structure returned by GET method\n\n"
            . "3. PUT: equivalent with UPDATE, for one or more records\n\t"
            . " Same parameterization like POST\n\n"
            . "4. DELETE: \n\t"
            . " Example: DELETE https://{host}/seed/api/?table={entity name}&id={value of PK to be selected}\n\t"
            . " or it can be called by sending a JSON body containing the PK values that should be deleted."
            . " Parameters:\n\t"
            . "     table   = Entity name. To return all tables use * \n\t"
            . "     id      = The value of the PK to be returned\n\t";


    if($format == "")
    {
        print($output);
    }
    elseif($format == "JSON")
    {
        //echo json_encode(
        //    array("message" => $output)
        //);
        $rez=array();
        $rez["records"]=array();
        array_push($rez["records"], array("message" => $output));
        echo json_encode($rez);
    }

    return;
}



/////////////////////////////////////
//          current_schema         //
/////////////////////////////////////
$query = "SELECT DATABASE() as current_schema;";
$result = $conn -> query($query);
$row = $result -> fetch_object();
$current_schema  = $row -> current_schema;


/////////////////////////////////////
//              $pk_name           //
/////////////////////////////////////
$query = "SELECT COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_schema = '" . $current_schema . "'
            AND table_name = '" . $table . "'
            AND COLUMN_KEY = 'PRI';";
//print($query);
$result = $conn -> query($query);
if($row = $result -> fetch_object())
{
    $pk_name = $row->COLUMN_NAME;
    //print($pk_name);
}
elseif($table != "*")
{
    http_response_code(400);

    $rez=array();
    $rez["records"]=array();
    array_push($rez["records"], array("error" => "Requested table does not exist! Try using * to retrieve all tables!"));
    echo json_encode($rez);

    return;
}


/////////////////////////////////////
//              $pk_value           //
/////////////////////////////////////
$pk_value = @$_POST['id'];
if($pk_value == "")
{
    $pk_value = @$_GET['id'];
}
if($pk_value == "")
{
    $pk_value = @$_POST['Id'];
}
if($pk_value == "")
{
    $pk_value = @$_GET['Id'];
}
//print($pk_value);



/////////////////////////////////////
//              Input JSON         //
/////////////////////////////////////
//$input = (array) json_decode(file_get_contents('php://input'), TRUE);
$input = json_decode(file_get_contents("php://input"));
//print_r($input);
$input_arr = (array) $input;
//print_r($input_arr);



/////////////////////////////////////
//          request method         //
/////////////////////////////////////
$requestMethod = $_SERVER["REQUEST_METHOD"];
//print($requestMethod);

switch ($requestMethod) {
case 'GET':
    getEntity($limit);
    break;
case 'POST':
    if(array_key_exists("records", $input_arr))
    /* CONVENTION: INPUT JSON SHOULD HAVE A ROOT NAMED records[] LIKE THIS:
     *
    {
    "records": [
            {
                "CodValuta": "EUR",
                "Data": "2021-03-10",
                "CursValuta": "4.9"
            },
            {
                "CodValuta": "EUR",
                "Data": "2021-03-09",
                "CursValuta": "4.89"
            }
        ]
    }
     */
    {
        insertEntity();
    }
    else
    {
        http_response_code(400);
        $rez=array();
        $rez["records"]=array();
        array_push($rez["records"], array("error" => "You have to specify a request body!"));
        echo json_encode($rez);
    }
    break;
case 'PUT':
    if(array_key_exists("records", $input_arr))
    {
        updateEntity();
    }
    else
    {
        http_response_code(400);
        $rez=array();
        $rez["records"]=array();
        array_push($rez["records"], array("error" => "You have to specify a request body!"));
        echo json_encode($rez);
    }
    break;
case 'DELETE':
    deleteEntity();
    break;
default:
    getEntity($limit);
    break;
}


function getEntity($limit)
{
    global $conn, $table, $scope, $current_schema, $pk_name, $pk_value, $whereAttr, $whereValue, $whereClause;
    $rez=array();
    $rez["records"]=array();

    if ($table === "*")
    {
        $query = "SELECT TABLE_NAME, TABLE_COMMENT "
                . "FROM information_schema.tables "
                . "WHERE table_schema = '".$current_schema."' "
                . "AND table_type = 'BASE TABLE'"
                . "ORDER BY TABLE_NAME;";
    }
    else
    {
        if($scope === "create")
        {
            $query = "SHOW CREATE TABLE ".$table."";
        }
        else
        {
            $query = "SELECT * FROM " . $table . "";
            // filter by primary key:
            if ($pk_value != "")
            {
                $query = $query . " WHERE ".$pk_name." = '" . $pk_value . "'";
            }

            // filter by WHERE clause
            if ($pk_value == "" && $whereAttr != "" && $whereValue != "")
            {
                if($whereClause == "")
                {
                    $query = $query . " WHERE ".$whereAttr." = '" . $whereValue . "'";
                }
                else
                {
                    $query = $query . " WHERE ".$whereAttr." LIKE '%" . $whereValue . "%'";
                }
            }

            if ($pk_value != "" && $whereAttr != "" && $whereValue != "")
            {
                http_response_code(400);
                array_push($rez["records"], array("error" => "You cannot filter by both Primary Key and WHERE clause!"));
                echo json_encode($rez);
                return;
            }

            if ($whereAttr != "" && $whereValue == "")
            {
                http_response_code(400);
                array_push($rez["records"], array("error" => "To use WHERE clause you should specify a whereValue!"));
                echo json_encode($rez);
                return;
            }

            if ($whereAttr == "" && $whereValue != "")
            {
                http_response_code(400);
                array_push($rez["records"], array("error" => "To use WHERE clause you should specify a whereAttr!"));
                echo json_encode($rez);
                return;
            }

            if ($whereAttr == "" && $whereValue == "" && $whereClause != "")
            {
                http_response_code(400);
                array_push($rez["records"], array("error" => "To use WHERE clause you should specify a whereAttr and a whereValue!"));
                echo json_encode($rez);
                return;
            }

            if ($whereClause != "" && $whereClause != "LIKE" && $whereClause != "EQUAL")
            {
                http_response_code(400);
                array_push($rez["records"], array("error" => "To use WHERE clause may be EQUAL or LIKE!"));
                echo json_encode($rez);
                return;
            }


            // limit clause
            if ($limit != "")
            {
                $query = $query . " LIMIT ".$limit.";";
            }
        }
    }
    //print($query);

    $result = $conn -> query($query);
    //print_r($result);

    $num = 0;
    if(!$result)
    {
        http_response_code(400);
        //array_push($rez["records"], array("error" => "Wrong clauses combination. No results returned!"));
        array_push($rez["records"], array("error" => mysqli_error($conn)));
        echo json_encode($rez);
        return;
    }
    else
    {
        $num = $result->num_rows;
    }

    if($num > 0)
    {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)){
            // extract row:
            // this will make $row['name'] to just $name only extract($row);
            //print_r($row);
            //print_r(array_keys($row));

            $item =$row;

            array_push($rez["records"], $item);
        }

        http_response_code(200);
    }
    else
    {
        http_response_code(404);

        $item = array("warning" => "No records found.");
        array_push($rez["records"], $item);
    }

    echo json_encode($rez);
}


function updateEntity()
{
    global $conn, $table, $input, $pk_name;
    $rez=array();
    $rez["records"]=array();
    $errors = 0;

    foreach ($input->records as $record)
    {
        $row = (array) $record;
        //print_r(array_keys($row));

        $valori = "";
        $i = 1;
        foreach ($row as $key => $value)
        {
            if($key === $pk_name)
            {
                $where_clause = " WHERE " . $pk_name . " = '" . $value . "'";
            }
            else
            {
                if($i === 1)
                {
                    $valori = $key . " = '" . $value . "'";
                }
                else
                {
                    $valori = $valori . ", " . $key . " = '" . $value . "'";
                }
                $i++;
            }

        }

        $query = "UPDATE " . $table . " SET " . $valori . $where_clause . ";";

        //print($query . "\n");
        $result = $conn -> query($query);


        if($conn->errno === 0)
        {
            array_push($rez["records"], array("ok" => $query));
            //TO NOT SHOW THE SQL, REPLACE WITH:
            //array_push($rez["records"], "Insert succesfully.");
        }
        else
        {
            // BE CAREFUL WITH SHOWING THE ERROR, IT MAY REPRESENT A SECURITY ISSUE
            // IF YOU DON'T PROTECT THE RUNNING SERVER WITH A PASSWORD THEN REPLACE mysqli_error WITH A GENERIC MESSAGE
            array_push($rez["records"], array("error" => mysqli_error($conn)));
            $errors = 1;
        }
    }


    if($errors == 0)
    {
        http_response_code(200);
    }
    else
    {
        http_response_code(206);
    }
    echo json_encode($rez);
}


function deleteEntity()
{
    global $conn, $table, $input, $input_arr, $pk_name, $pk_value;
    $errors = 0;
    $rez=array();
    $rez["records"]=array();

    if($pk_value != "")
    {
        $query = "DELETE FROM " . $table . " WHERE " . $pk_name . " = '" . $pk_value . "'";

        //print($query . "\n");
        $result = $conn -> query($query);


        if($conn->errno === 0)
        {
            array_push($rez["records"], array("ok" => $query));
            //TO NOT SHOW THE SQL, REPLACE WITH:
            //array_push($rez["records"], "Insert succesfully.");
        }
        else
        {
            // BE CAREFUL WITH SHOWING THE ERROR, IT MAY REPRESENT A SECURITY ISSUE
            // IF YOU DON'T PROTECT THE RUNNING SERVER WITH A PASSWORD THEN REPLACE mysqli_error WITH A GENERIC MESSAGE
            array_push($rez["records"], array("error" => mysqli_error($conn)));
            $errors = 1;
        }
    }
    elseif(array_key_exists("records", $input_arr))
    {
        foreach ($input->records as $record)
        {
            $row = (array) $record;
            //print_r(array_keys($row));

            foreach ($row as $key => $value)
            {
                if($key === $pk_name)
                {
                    $where_clause = " WHERE " . $pk_name . " = '" . $value . "'";
                }
            }

            $query = "DELETE FROM " . $table . $where_clause . ";";

            //print($query . "\n");
            $result = $conn -> query($query);


            if($conn->errno === 0)
            {
                array_push($rez["records"], array("ok" => $query));
                //TO NOT SHOW THE SQL, REPLACE WITH:
                //array_push($rez["records"], "Insert succesfully.");
            }
            else
            {
                // BE CAREFUL WITH SHOWING THE ERROR, IT MAY REPRESENT A SECURITY ISSUE
                // IF YOU DON'T PROTECT THE RUNNING SERVER WITH A PASSWORD THEN REPLACE mysqli_error WITH A GENERIC MESSAGE
                array_push($rez["records"], array("error" => mysqli_error($conn)));
                $errors = 1;
            }
        }
    }
    else
    {
        http_response_code(400);
        array_push($rez["records"], array("error" => "You have to specify either an id or a request body!"));
        echo json_encode($rez);
        return;
    }


    if($errors == 0)
    {
        http_response_code(200);
    }
    else
    {
        http_response_code(206);
    }
    echo json_encode($rez);
}



function insertEntity()
{
    global $conn, $table, $input;
    $rez=array();
    $rez["records"]=array();
    $errors = 0;

    foreach ($input->records as $record)
    {
        $row = (array) $record;
        //print_r(array_keys($row));

        $valori = "";
        $i = 1;
        foreach ($row as $key => $value)
        {
            if($i === 1)
            {
                $valori = $key . " = '" . $value . "'";
            }
            else
            {
                $valori = $valori . ", " . $key . " = '" . $value . "'";
            }
            $i++;
        }

        $query = "INSERT INTO " . $table . " SET " . $valori . ";";

        //print($query . "\n");
        $result = $conn -> query($query);


        if($conn->errno === 0)
        {
            array_push($rez["records"], array("ok" => $query));
            //TO NOT SHOW THE SQL, REPLACE WITH:
            //array_push($rez["records"], "Insert succesfully.");
        }
        else
        {
            // BE CAREFUL WITH SHOWING THE ERROR, IT MAY REPRESENT A SECURITY ISSUE
            // IF YOU DON'T PROTECT THE RUNNING SERVER WITH A PASSWORD THEN REPLACE mysqli_error WITH A GENERIC MESSAGE
            array_push($rez["records"], array("error" => mysqli_error($conn)));
            $errors = 1;
        }
    }

    if($errors == 0)
    {
        http_response_code(200);
    }
    else
    {
        http_response_code(206);
    }
    echo json_encode($rez);
}
