<!DOCTYPE html>
<!-- to convert from xls to mysql -->
<html>
   <head>
      <link rel="stylesheet" href="css/style.css" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta charset="UTF-8">
      <title>Migrate Data from XLS to MySQL</title>
   </head>
   <body>
    <main>
        <img src="imgs/logo.png">
        <h2>Migrate Data from XLS to MySQL</h2>
        <h4>Upload Your Excel File</h4>
        <input id="xls-file" type="file" name="file" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
        <span style="float: none;">Allowed excel formats: .xls, .xlsx</span>
        <div><br></div>
        <div><br></div>
        <input id="sql-file" type="text" name="sql_file" placeholder="Data Base Name"/>
        <div class="clr"></div>
        <div><br></div>
        <div><br></div>
        <button id="upload" onclick="migrate()">migrate</button>
        <div class="result"><!-- add sql file here --></div>
    </main>
    <!-- JQuery & AJAX script code -->
    <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-2.1.1.min.js"></script>
    <script>
        function migrate(){
          if($('#xls-file').val() != ""){
            var xls_data = $('#xls-file').prop('files')[0];
            var sql_data = $('#sql-file').val();
            var form_data = new FormData();
            form_data.append('file', xls_data);
            form_data.append('sql_file', sql_data);
            $.ajax({
                url: 'make_migration.php', // point to server-side PHP script
                dataType: 'text',  // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(SQLtext){
                    // return SQL 
                    $(".result").html( "<p>"+SQLtext+"</p>");
                }
            });
         }// end if empty
         else {
          alert("Empty Input File");
         }
        }
    </script>
    <!-- / JQuery & AJAX code -->
   </body>
</html>

