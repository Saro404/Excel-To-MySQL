<?php
/**
 *
 * Main File for converting excel file to MySQL file
 * 1- Read excel file 
 * 2- Read tables, columns names, datatypes and values 
 * 3- Create .sql file contains these values
 * 4- Create a new DB and then insert the .sql file 
**/
    /* include the main class for spreadsheet reading */
    include 'spreadsheet_reader.php';
    /* include the main class for reading old microsoft excel (97/2003) spreadsheets */
    include 'excel_reader.php';

    /* directory name to save the xls file */
    $xls_dir_name = "xls/";
    /* directory name to save the sql file */
    $sql_dir_name = "sql/";
    /* excel file name */
    $xls_file_name = $_FILES['file']['name'];
    /* sql file name */
    $sql_file_name = "";

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
        $sheets = $spreadsheet -> Sheets(); /* object contains all the excel sheet */
        $data_type = array(); /* store the data type of the columns */
        $size = sizeof($sheets); /* sheet numbers */
        $string = ""; /* store sql statements */
        $table_name = ""; /* store tables names */
        /* for each sheet */ 
        foreach ($sheets as $index => $name) {
            /* move to another sheet */
            $spreadsheet -> ChangeSheet($index);
            /* count to count the rows 0 is first row, 1 is second row ..etc */
            $count = 0;
            $aux = 1;
            foreach ($spreadsheet as $Key => $row) { // Get the name of the table
                // first row
                if($count == 0){
                    $string.= "CREATE TABLE ".$row[0]."(";
                    // first cell
                    $table_name = $row[0];
                }
                // second row
                else if ($count == 1){
                    // store the primary key
                    $primary_key = "";
                    foreach ($row as $Key => $row1){
                        // store the names of the columns and datatypes 
                        $datatype_array = explode(',', $row1);
                        $string.= $datatype_array[0]." ";
                        if($datatype_array[1]=="varchar"){
                            $string.= "varchar(".$datatype_array[2].")";
                            array_push($data_type, "varchar");
                            if($datatype_array[3]=="PK"){
                                    $primary_key.=", PRIMARY KEY (".$datatype_array[0].")";
                            }
                        }
                        else if($datatype_array[1]=="int"){
                            $string.= "int";
                            array_push($data_type, "int");
                            if($datatype_array[2]=="PK"){
                                    $primary_key.=", PRIMARY KEY (".$datatype_array[0].")";
                            }
                        }
                        else if($datatype_array[1]=="char"){
                            $string.= "char";
                            array_push($data_type, "char");
                            if($datatype_array[2]=="PK"){
                                    $primary_key.=", PRIMARY KEY (".$datatype_array[0].")";
                            }
                        }
                        else if($datatype_array[1]=="date"){
                            $string.= "date";
                            array_push($data_type, "date");
                            if($datatype_array[2]=="PK"){
                                    $primary_key.=", PRIMARY KEY (".$datatype_array[0].")";
                            }
                        }
                        else if($datatype_array[1]=="decimal"){
                            $string.= "decimal(".$datatype_array[2].",".$datatype_array[3].")";
                            array_push($data_type, "decimal");
                            if($datatype_array[4]=="PK"){
                                    $primary_key.=", PRIMARY KEY (".$datatype_array[0].")";
                            }
                        }
                        else if($datatype_array[1]=="bit"){
                            $string.= "bit(".$datatype_array[2].")";
                            array_push($data_type, "bit");
                        }
                        else if($datatype_array[1]=="text"){
                            $string.= "text";
                            array_push($data_type, "text");
                            if($datatype_array[2]=="PK"){
                                    $primary_key.=", PRIMARY KEY (".$datatype_array[0].")";
                            }
                        }
                        $aux++;
                        if($aux <= sizeof($row)){
                            $string.=", ";
                        }
                        else{
                            if($primary_key != ""){
                               $string.=$primary_key;
                            }
                            $string.=");INSERT INTO ".$table_name." VALUES ";
                        }
                    }
                }
                // other rows that contain the columns values/ data values 
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
        }

            // $srting now is containing all the 'formatted' sql statements         
            /* sql file name = xls file name */
            $sql_file_name = substr($xls_file_name,  0, strpos($xls_file_name, "."));
            /* create new sql file */
            $sql_file = fopen("sql/".$sql_file_name.".sql", "w") or die("Unable to open file!");
            /* write the sql statements */
            fwrite($sql_file, $string);
            /* close file */
            fclose($sql_file);
            // download button - allow the user to download the new sql file  
            echo "<a class='btn' href='sql/".$sql_file_name.".sql' download>Download The MySQL File</a><br><br>";
        
                /* Create a new database */
                /* database connection */
                $servername = "localhost";
                $username = "root"; 
                $password = "root";
                $conn = new mysqli($servername, $username, $password);
                /* if connection faild */
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $conn->set_charset("utf8");

                // create database with a given name = the same name as the new sql file 
                $creat_db = "CREATE DATABASE `$sql_file_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
                if ($conn->query($creat_db) === TRUE) {
                    
                    mysqli_select_db($conn, $sql_file_name);
                    //  create and insert tables & values into the new database
                    if ($conn->multi_query($string) === TRUE) {
                        echo "<span style='color:#35ad6d; text-align: left;'>A new database has been generated with this name: ".$sql_file_name."</span>";
                    }
                    else {
                        echo 'Error: '. $conn->error;
                    }
                 }
                else {
                 echo 'Error: '. $conn->error;
                }
    }// end try
    // exception error
    catch (Exception $error){
        echo $error -> getMessage();
    }
?>
