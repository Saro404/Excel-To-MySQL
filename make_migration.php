<?php
header('Content-Type: text/plain');
/**
 *
 * Main File for migrating data from excel file to MySQL file
 * 1- Read excel file 
 * 2- Read tables, columns names, datatypes and values 
 * 3- Read given database name and read its tables, columns names, datatypes
 * 4- Map between the columns names, datatypes
 * 5- Migrate the data
**/
    /* include the main class for spreadsheet reading */
    include 'spreadsheet_reader.php';
    /* include the main class for reading old microsoft excel (97/2003) spreadsheets */
    include 'excel_reader.php';

    /* directory name to save the xls file */
    $xls_dir_name = "xls/";
    /* excel file name */
    $xls_file_name = $_FILES['file']['name'];
    /* database name */
    $sql_database_name = $_POST['sql_file'];

    /* database connection */
    $servername = "localhost";
    $username = "root";
    $password = "root";
    // given database name
    $database = $sql_database_name;
    // table list [names] 
    $tableList = array();
    // table info table name + colunm name + datatypes
    $tableList_cn = array();
    $conn = new mysqli($servername, $username, $password, $database);
    /* if connection faild */
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    // query to get all tables 
    $listdbtables = $conn->query('SHOW TABLES') or die('Invalid query: ' . mysqli_error($conn));
    // save all database tables to table list array $tableList_cn[]
    while($cRow = mysqli_fetch_array($listdbtables)){
            $tableList[] = $cRow[0];
            // for each table show all colunms
            $table_info = $conn->query('SHOW FULL COLUMNS FROM '.$cRow[0].';') or die('Invalid query: ' . mysqli_error($conn));
            $table_info_string= "";
            while($iRow = mysqli_fetch_array($table_info)){
                // sotre the colunms names and datatypes (without numbers)
                if(strpos($iRow['Type'],'(') !== false){
                      $table_info_string.= $iRow['Field'].' '.substr($iRow['Type'], 0, strpos($iRow['Type'], '(')).', ';
                }
                else {$table_info_string.= $iRow['Field'].' '.$iRow['Type'].', ';
                }
              
            }
            $table_info_string = $string = substr($table_info_string, 0, -2);
            array_push($tableList_cn, array( "tableName" => $cRow[0], "tableInfo" => $table_info_string));
          //  print_r($tableList_cn);
    }

    /* excel file */
    /* if the file is already exist */
    if (file_exists($xls_dir_name.$xls_file_name)){
        /* to get a unique value */
        $unique_id = time();
        /* rename file: unique value - file name */
        $xls_file_name = $unique_id . "-" . $xls_file_name;
    }
    else if ($_FILES['file']['name'] == ""){
        $xls_file_name = '';
    }
        // upload the xsl file to this path xls/ 
        move_uploaded_file($_FILES['file']['tmp_name'], $xls_dir_name . $xls_file_name);
        $file_path = $xls_dir_name . $xls_file_name;

    try {
        $spreadsheet = new SpreadsheetReader($file_path);
        $sheets = $spreadsheet -> Sheets();/* object contains all the excel sheet */
        $data_type = array(); /* store the data type of the columns */
        $size = sizeof($sheets); /* sheet numbers */
        $string = ""; /* store sql statements */
        $table_columns_names_data_types=""; /* store tables columns names */
        $table_name = ""; /* store tables names */
        $m_result = ""; /* migration results */
        /* for each sheet */ 
        foreach ($sheets as $index => $name) {
            /* move to another sheet */
            $spreadsheet -> ChangeSheet($index);
            /* count to count the rows 0 is first row, 1 is second row ..etc */
            $count = 0;
            $aux = 1;
            foreach ($spreadsheet as $Key => $row) { // Get the name of the table
                if($count == 0){
                    $table_name = $row[0];
                }
                else if ($count == 1){
                    foreach ($row as $Key => $row1){ // Get the names of the columns
                        $datatype_array = explode(',', $row1);
                        $table_columns_names_data_types.= $datatype_array[0]." ";
                        if($datatype_array[1]=="varchar"){
                            $table_columns_names_data_types.= "varchar";
                            array_push($data_type, "varchar");
                        }
                        else if($datatype_array[1]=="int"){
                            $table_columns_names_data_types.= "int";
                            array_push($data_type, "int");
                        }
                        else if($datatype_array[1]=="char"){
                            $table_columns_names_data_types.= "char";
                            array_push($data_type, "char");
                        }
                        else if($datatype_array[1]=="date"){
                            $table_columns_names_data_types.= "date";
                            array_push($data_type, "date");
                        }
                        else if($datatype_array[1]=="decimal"){
                            $table_columns_names_data_types.= "decimal";
                            array_push($data_type, "decimal");
                        }
                        else if($datatype_array[1]=="bit"){
                            $table_columns_names_data_types.= "bit";
                            array_push($data_type, "bit");
                        }
                        else if($datatype_array[1]=="text"){
                            $table_columns_names_data_types.= "text";
                            array_push($data_type, "text");
                        }
                        $aux++;
                        if($aux <= sizeof($row)){
                            $table_columns_names_data_types.=", ";
                        }
                        else{
                            $string.="REPLACE INTO ".$table_name." VALUES ";
                        }
                    }
                }
                else{
                    $i = 0;
                    $string.= "(";
                    foreach ($row as $Key => $row1) { /* get table's values */
                            if($data_type[$i]=="varchar"){
                                    $string.= "'$row1', ";
                            }
                            else if($data_type[$i]=="int"){
                                    $string.= "$row1, ";
                            }
                            else if($data_type[$i]=="char"){
                                    $string.= "'$row1', ";
                            }
                            else if($data_type[$i]=="date"){
                                    $string.= "'$row1', ";
                            }
                            else if($data_type[$i]=="decimal"){
                                    $string.= "$row1, ";
                            } 
                            else if($data_type[$i]=="bit"){          
                                    $string.= "$row1, ";
                            }
                            else if($data_type[$i]=="text"){ 
                                    $string.= "'$row1', ";
                            }
                        $i++;  
                    }
                    // delete , for the last value
                    $string = substr($string, 0, -2);
                    // close the insert brackets
                    $string = $string."),";
                }
                $count++;
            }

                $string = substr($string, 0, -2).");";

                foreach ($tableList_cn as $table_info){
                    // if same table name
                    if($table_info['tableName'] == $table_name){
                        // if same columns & same datatype do the migration
                       // echo $table_columns_names_data_types;
                       // echo "<br>";
                       // echo $table_info['tableInfo'];
                        if($table_info['tableInfo'] == $table_columns_names_data_types){
                            $replace = $conn->query($string) or die('Invalid query: ' . mysqli_error($conn));
                                if($replace){
                                    // include the tables name
                                    $m_result.= "-".$table_name;
                                }
                        }
                    }
                }
                $string = "";
                $table_columns_names_data_types = "";
        }


            echo "<br>";
            echo "<br>";
            echo "<p style='color:#35ad6d;'>The Data Successfully Migrated. <br> Database Name: ".$sql_database_name.". <br> Tables: ". $m_result ."</p>";
    }// end try
    // exception error
    catch (Exception $error){
        echo $error -> getMessage();
    }
?>
