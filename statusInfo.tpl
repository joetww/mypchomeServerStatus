<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <!-- <script type="text/javascript" src="http://ecweb.ec1.mypchome.com.tw/common/js/jquery-1.7.2.min.js"></script> -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <title>伺服器狀態</title>
    <style>
        table, th, td 
        {
            margin: 10px 0;
            border: solid 1px #333;
            padding: 2px 4px;
            font: 15px Verdana;
        }
        table {
            border-collapse: collapse;
        }
        th {
            font-weight: bold;
        }
        td {
            word-wrap: break-word;
        }
        tr:hover {background-color: #f5f5f5}
    </style>
</head>
<body>
    <input type="button" onclick="CreateTableFromJSON()" value="Create Table From JSON" />
    <div id="showData"></div>
</body>
<script>
    var myServer;
    
    function CreateTableFromJSON() {

        // 檢查分類數量
        var group_name;
        var group = {};
        for (var i = 0; i < myServers.length; i++){
            group_name = myServers[i][Object.keys(myServers[i])[0]]['group'];
            if(!([group_name] in group)) group[group_name] = 0;
            group[group_name] = group[group_name] + 1;
        }

        // EXTRACT VALUE FOR HTML HEADER.
        // ('Book ID', 'Book Name', 'Category' and 'Price')
        var col = [];
        var picesOfServer = myServers[0][Object.keys(myServers[0])[0]];
        col = Object.keys(picesOfServer);

        $('#showData').html('');
        for(var i = 0; i < Object.keys(group).length; i++){
            var table = $('<table></table>').attr('id','group_'+Object.keys(group)[i]);
            table.append($('<tbody />')).append('<tr />');
            $('#showData').append(table);
            $('#group_'+Object.keys(group)[i]+' tbody tr:first').append('<th>server name</th>');
            for(var j = 0; j < col.length; j++){
                $('#group_'+Object.keys(group)[i]+' tbody tr:first').append('<th>'+col[j]+'</th>');
            }
            $('#group_'+Object.keys(group)[i]).append('<caption> Group: '+Object.keys(group)[i]+'</caption>');
        }

        for(var i = 0; i < myServers.length; i++){
            var data = myServers[i];
            Object.keys(data).map(function(key, index){
                groupKey = data[key]['group'];
                $('#group_'+groupKey+' tbody').append('<tr />');
                $('#group_'+groupKey+' tbody tr:last').append('<td>'+key+'</td>'); //補上伺服器name
                Object.keys(data[key]).map(function(k, i){
                    $('#group_'+groupKey+' tbody tr:last').append('<td>'+data[key][k]+'</td>');
                });
            });
        }

    }

    // A $( document ).ready() block.
    $( document ).ready(function() {
        $.ajax({
            url: $(location)[0].origin+$(location)[0].pathname+"?ACT=getJsonData",
            cache: false
        })
        .done(function(e) {
            eval(e);
        });

        setInterval(function(){
            $.ajax({
                url: $(location)[0].origin+$(location)[0].pathname+"?ACT=getJsonData",
                cache: false
            })
            .done(function(e) {
                eval(e);
            });
        }, 60000);
    });
</script>
</html>
