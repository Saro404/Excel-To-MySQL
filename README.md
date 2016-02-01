# Excel-To-MySQL


## Convert An Excel File To MySQL 

### There are two tools:
  - The first tool converts an excel sheet (Excel DB) to (MySQL DB).<br>
  - The second tool migrates the data from an excel file to an existed MySQL database.

# Constraints

## Excel Format
The user have to follow a certain format to make the converting functionality work properly.<br>
Each table in each excel sheet should follow the following constraints<br>
  The First row: table name.<br>
  The Second row: column name, datatype, size, PK. Note: The PK works only for (int, varchar, char, decimal, text).

##Supported Data types
Int, decimal, varchar, char, text, data, and bit.


##Libraries
1.	Main class for spreadsheet reading (Spreadsheet_reader.php).
2.	A class for reading Microsoft Excel (97/2003) Spreadsheets (excel_reader.php).
3.	A Class for parsing XLS files (SpreadsheetReader_XLS.php).
4.	A Class for parsing XLSX files (SpreadsheetReader_XLSX.php).


# Note
<b> This code is an enhanced version from https://github.com/alfhh/XLStoSQL </b>


