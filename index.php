<!--index.html-->
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8" />
	<title>Stock Management</title>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"  crossorigin="anonymous"/>	
  <link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap4.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.min.css" rel="stylesheet"/>
  <link href="style.css" type="text/css" rel="stylesheet">

</head>
<body>

    <?php
    include "config.php";
    if (isset($_GET["sub"])) {
        $search_box = $_GET["search_box"];
        $datepicker1 = $_GET["datepicker"];
        $datepicker2 = $_GET["datepicker1"];

        $date1 = str_replace("/", "-", $datepicker1);
        $datepicker11 = date("Y-m-d", strtotime($date1));
        $date2 = str_replace("/", "-", $datepicker2);
        $datepicker12 = date("Y-m-d", strtotime($date2));

        if (!empty($search_box) && $datepicker11 == 0 && $datepicker12 == 0) {
            $query = "select * from stock where `stock_name`= '$search_box' ORDER BY`stock`.`stock_count` ASC";
        } elseif (
            empty($search_box) &&
            $datepicker11 != 0 &&
            $datepicker12 != 0
        ) {
            //echo("gehhh");
            $query = "select * from stock  where `date` BETWEEN '$datepicker11' AND '$datepicker12' ORDER BY `stock`.`stock_count` ASC ";
        } elseif (
            !empty($search_box) &&
            $datepicker11 != 0 &&
            $datepicker12 != 0
        ) {
            //echo("gehhh2");
            $query = "select * from stock  where `stock_name`= '$search_box' and `date` BETWEEN '$datepicker11' AND '$datepicker12' ORDER BY`stock`.`stock_count` ASC";
        } else {
            $query = "select * from stock ORDER BY`stock`.`stock_count` ASC";
        }
        //s echo($query);
    } else {
        $query = "select * from stock  ORDER BY`stock`.`stock_count` ASC";
    }

    if (isset($_POST["but_import"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["importfile"]["name"]);

        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

        $uploadOk = 1;
        if ($imageFileType != "csv") {
            $uploadOk = 0;
        }

        if ($uploadOk != 0) {
            if (
                move_uploaded_file(
                    $_FILES["importfile"]["tmp_name"],
                    $target_dir . "importfile.csv"
                )
            ) {
                // Checking file exists or not
                $target_file = $target_dir . "importfile.csv";
                $fileexists = 0;
                if (file_exists($target_file)) {
                    $fileexists = 1;
                }
                if ($fileexists == 1) {
                    // Reading file
                    $file = fopen($target_file, "r");
                    $i = 0;

                    $importData_arr = [];

                    while (($data = fgetcsv($file, 1000, ",")) !== false) {
                        $num = count($data);

                        for ($c = 0; $c < $num; $c++) {
                            $importData_arr[$i][] = mysqli_real_escape_string(
                                $con,
                                $data[$c]
                            );
                        }
                        $i++;
                    }
                    fclose($file);

                    $skip = 0;
                    // insert import data
                    foreach ($importData_arr as $data) {
                        if ($skip != 0) {
                            $date = $data[0];
                            $stock_name = $data[1];
                            $price = $data[2];
                            $stock_count = $data[3];
// check stock and price is numeric or not
                            if (is_numeric($stock_count) && (is_numeric($price)||empty($price))) 
							{
                                $sql =
                                    "select count(*) as allcount from stock where date='" .
                                    $date .
                                    "' and stock_name='" .
                                    $stock_name .
                                    "' and  price='" .
                                    $price .
                                    "' and stock_count='" .
                                    $stock_count .
                                    "' ";
                            } else {
                                echo "<script type=\"text/javascript\">
              alert(\"CSV File incorrect formate.\");
              window.location = \"index.php\"
              </script>";
                                exit();
                            }
// check stock and date formate
                            if (
                                preg_match(
                                    "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",
                                    $date
                                )
                            ) {
                                $sql =
                                    "select count(*) as allcount from stock where date='" .
                                    $date .
                                    "' and stock_name='" .
                                    $stock_name .
                                    "' and  price='" .
                                    $price .
                                    "' and stock_count='" .
                                    $stock_count .
                                    "' ";
                            } else {
                                echo "<script type=\"text/javascript\">
              alert(\"CSV File incorrect date formate make it (yyyy-mm-dd) and Uploaded .\");
              window.location = \"index.php\"
              </script>";
                                exit();
                            }

                            // Checking duplicate entry
                            $sql =
                                "select count(*) as allcount from stock where date='" .
                                $date .
                                "' and stock_name='" .
                                $stock_name .
                                "' and  price='" .
                                $price .
                                "' and stock_count='" .
                                $stock_count .
                                "' ";

                            $retrieve_data = mysqli_query($con, $sql);
                            $row = mysqli_fetch_array($retrieve_data);
                            $count = $row["allcount"];

                            if ($count == 0) {
                                // Insert record
                                $insert_query =
                                    "insert into stock(date,stock_name,price,stock_count) values('" .
                                    $date .
                                    "','" .
                                    $stock_name .
                                    "','" .
                                    $price .
                                    "','" .
                                    $stock_count .
                                    "')";
                                mysqli_query($con, $insert_query);
                            }
                        }
                        $skip++;
                    }
                    $newtargetfile = $target_file;
                    echo "<script type=\"text/javascript\">
              alert(\"CSV File Uploaded .\");
              window.location = \"index.php\"
              </script>";
                }
            }
        }
    }
    ?>
    </head>
    <body>
   <nav class="navbar navbar-dark bg-dark">
     <a class="navbar-brand" href="#">Stock <span class="text-danger">Management</span>
     </a>
     <form class="form-inline" method="get" action="" enctype="multipart/form-data" id="import_form">
       <div class="dropdown">
         <input type="text" name="search_box" class="form-control mr-2 border-0" placeholder="Type Here..." id="search_box" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onkeyup="javascript:load_data(this.value)" onfocus="javascript:load_search_history()" />
         <span id="search_result"></span>
       </div>
       <input data-date-format="dd/mm/yyyy" id="datepicker" name="datepicker" class="form-control mr-2 border-0">
       <input data-date-format="dd/mm/yyyy" id="datepicker1" name="datepicker1" class="form-control mr-2 border-0">
       <input type="submit" id="sub" name="sub" value="Submit" class="btn btn-success">
     </form>
   </nav>
   <div class="container pt-4">
     <div class="card">
       <div class="card-header">
         <div class="row">
           <div class="col-auto">Stock Table</div>
           <div class="col-auto ml-auto">
             <form method="post" action="" enctype="multipart/form-data" id="import_form" class="d-flex align-items-center">
               <div class="input-group">
                 <div class="custom-file">
                   <input type="file" class="custom-file-input" id="importfile" name="importfile" aria-describedby="but_import">
                   <label class="custom-file-label" for="importfile">Choose file</label>
                 </div>
                 <div class="input-group-append">
                   <button class="btn btn-dark" type="submit" id="but_import" name="but_import" value="Import">Import</button>
                 </div>
               </div>
               <div class="dropdown">
                 <a class="btn btn-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                   <i class="fa fa-info-circle"></i> Instruction </a>
                 <div class="dropdown-menu">
                   <ul class="list-group list-group-flush">
                     <li class="list-group-item">Enclose text field in quotes (' , " ) if text contains comma (,) is used.</li>
                     <li class="list-group-item">Enclose text field in single quotes (') if text contains double quotes (")</li>
                     <li class="list-group-item">Enclose text field in double quotes (") if text contains single quotes (')</li>
                   </ul>
                 </div>
               </div>
             </form>
           </div>
         </div>
       </div>
       <div class="card-body">
         <table id="userTable" class="table table-bordered table-sm">
           <thead>
             <tr>
               <th>S.no</th>
               <th> Date</th>
               <th>Stock Name</th>
               <th>Price</th>
			     <th>Balance Stock</th>
			     <th>Next Purchase Date</th>
             </tr>
           </thead>
           <tbody> <?php
    $result = mysqli_query($con, $query);

    $sno = 1;

    while ($row = mysqli_fetch_array($result)) {
        // your code here

        
		//  If the price of stock is not available at that date, it should take the price of the stock on the previous date
		if($row["price"]!='')
		{
		$Newprice=$row["price"];	
		}
		else
		{
		$querynew = ("SELECT price FROM stock WHERE stock_name='" .$row["stock_name"] . "' and `price`!='' ORDER BY `stock`.`date` DESC ");
		
        $resultnew = mysqli_query($con, $querynew);
		$row1 = mysqli_fetch_array($resultnew);
		$Newprice=$row1["price"];
		
		}
		if($row["stock_count"]<10 && $row["stock_count"] >5)
		{
			$NewDate=Date('Y-m-d', strtotime('+3 days'));
		}
		else
		{
			$NewDate='';
		}
		//  If the price of stock count > 50 success , <= 10 danger
		if($row["stock_count"] > 50)
		{
			$class="table-success";
		}
		else if ($row["stock_count"] <= 10)
		{
		
			$class="table-danger";
		}
		else
		{
			$class="table-light";
		}
		
        echo "<tr class=" .$class .">
			<td>" .$sno ."</td>
			<td>" .$row["date"] . "</td>
			<td>" . $row["stock_name"] ."</td>
			<td>" .$Newprice ."</td>
			<td>" .$row["stock_count"] ."</td>
			<td>" .$NewDate ."</td>
			</tr>";
        $sno++;
    }
    ?> 


</tbody>
         </table>
       </div>
     </div>
   </div>
 </body>
 </html>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

<script>
	$(document).ready(function () {
    $('#userTable').DataTable();

    bsCustomFileInput.init();

});
</script>

<script type="text/javascript">
    $('#datepicker').datepicker({
        weekStart: 1,
        daysOfWeekHighlighted: "6,0",
        autoclose: true,
        todayHighlight: true,
    });
    $('#datepicker').datepicker("setDate", new Date());
	
	 $('#datepicker1').datepicker({
        weekStart: 1,
        daysOfWeekHighlighted: "6,0",
        autoclose: true,
        todayHighlight: true,
    });
    $('#datepicker1').datepicker("setDate", new Date());
</script>
<script>

var coll = document.getElementsByClassName("collapsible");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.display === "block") {
      content.style.display = "none";
    } else {
      content.style.display = "block";
    }
  });
}


function delete_search_history(id)
{
	fetch("process_data.php", {

		method: "POST",

		body: JSON.stringify({
			action:'delete',
			id:id
		}),

		headers:{
			'Content-type' : 'application/json; charset=UTF-8'
		}

	}).then(function(response){

		return response.json();

	}).then(function(responseData){
		load_search_history();
	});
}

function load_search_history()
{
	var search_query = document.getElementsByName('search_box')[0].value;

	if(search_query == '')
	{

		fetch("process_data.php", {

			method: "POST",

			body: JSON.stringify({
				action:'fetch'
			}),

			headers:{
				'Content-type' : 'application/json; charset=UTF-8'
			}

		}).then(function(response){

			return response.json();

		}).then(function(responseData){

			if(responseData.length > 0)
			{

				var html = '<ul class="list-group">';

				html += '<li class="list-group-item d-flex justify-content-between align-items-center"><b class="text-primary"><i>Your Recent Searches</i></b></li>';

				for(var count = 0; count < responseData.length; count++)
				{

					html += '<li class="list-group-item text-muted" style="cursor:pointer"><i class="fas fa-history mr-3"></i><span onclick="get_text(this)">'+responseData[count].search_query+'</span> <i class="far fa-trash-alt float-right mt-1" onclick="delete_search_history('+responseData[count].id+')"></i></li>';

				}

				html += '</ul>';

				document.getElementById('search_result').innerHTML = html;

			}

		});

	}
}

function get_text(event)
{
	var string = event.textContent;

	//fetch api

	fetch("process_data.php", {

		method:"POST",

		body: JSON.stringify({
			search_query : string
		}),

		headers : {
			"Content-type" : "application/json; charset=UTF-8"
		}
	}).then(function(response){

		return response.json();

	}).then(function(responseData){

		document.getElementsByName('search_box')[0].value = string;
	
		document.getElementById('search_result').innerHTML = '';

	});

	

}

function load_data(query)
{
	if(query.length > 2)
	{
		var form_data = new FormData();

		form_data.append('query', query);

		var ajax_request = new XMLHttpRequest();

		ajax_request.open('POST', 'process_data.php');

		ajax_request.send(form_data);

		ajax_request.onreadystatechange = function()
		{
			if(ajax_request.readyState == 4 && ajax_request.status == 200)
			{
				var response = JSON.parse(ajax_request.responseText);

				var html = '<div class="list-group">';

				if(response.length > 0)
				{
					for(var count = 0; count < response.length; count++)
					{
						html += '<a href="#" class="list-group-item list-group-item-action" onclick="get_text(this)">'+response[count].post_title+'</a>';
					}
				}
				else
				{
					html += '<a href="#" class="list-group-item list-group-item-action disabled">No Data Found</a>';
				}

				html += '</div>';

				document.getElementById('search_result').innerHTML = html;
			}
		}
	}
	else
	{
		document.getElementById('search_result').innerHTML = '';
	}
}
</script>