<!DOCTYPE html>
<!-- to convert from xls to mysql -->
<html>
   <head>
      <link rel="stylesheet" href="css/style.css" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta charset="UTF-8">
      <title>Convert XLS to MySQL</title>
   </head>
   <body>
    <main>
        <img src="imgs/logo.png">
        <h2>Convert XLS to MySQL</h2>
        <h4>Upload Your Excel File</h4>
        <input id="xls-file" type="file" name="files" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
        <button id="upload" onclick="convert()">Upload</button>
        <span>Allowed excel formats: .xls, .xlsx</span>
        <div><!-- add sql file here & Database name --></div>
    </main>
    <!-- JQuery & AJAX script code -->
    <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-2.1.1.min.js"></script>
    <script>
        function convert(){
          if($('#xls-file').val() != ""){
            var xls_data = $('#xls-file').prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', xls_data);
            $.ajax({
                url: 'converter.php', // point to server-side PHP script
                dataType: 'text',  // what to expect back from the PHP script.
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(SQLtext){
                    // return SQL 
                    $("div").append(SQLtext);
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