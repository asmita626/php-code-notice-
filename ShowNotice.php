<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    session_destroy();
    header("location:Noticefrontend.php");
}
if ($_SESSION['user_type'] != "studentid") {
$user = $_SESSION['auth_user']['userid'];
    if (preg_match("/^da.*$/",$user)) {
        include('DeptAadminAfterlogin.php');
    }
    elseif (preg_match("/^a.*$/",$user)) {
        include('afterlogin.php');
    } else{
        echo "";
    }
}
//include('DeptAadminAfterlogin.php');
$link = mysqli_connect('localhost', 'root', '', 'Notice');
$sql = "";
$to = "1950-01-01";
$from = "2500-01-01";

if (!empty($_POST)){
    $to=htmlentities($_POST['toDate'],ENT_QUOTES);
    $from=htmlentities($_POST['fromDate'],ENT_QUOTES);

        $date = new DateTime($from);
        $date->modify('+1 day');
        $from = $date->format('Y-m-d');
}

if ($_SESSION['user_type'] == "deptUser") {
	$dept = $_SESSION['auth_user']['department'];
    $userid = $_SESSION['auth_user']['userid'];
	$sql = "SELECT newnotice.id as id, newnotice.adminId as adminId, category.category as category, noticesubject, noticedescription, date, refer_id FROM newnotice, category WHERE newnotice.category = category.id and newnotice.date BETWEEN '${to}' AND '${from}' and category.userType  IN ('1','0') and category.deptid IN ('${dept}','0') and newnotice.adminId != '$userid'";
}
elseif ($_SESSION['user_type'] == "teacherid") {
    $dept = $_SESSION['auth_user']['deptid'];
    $userid = $_SESSION['auth_user']['userid'];
    $sql = "SELECT newnotice.id as id, newnotice.adminId as adminId, category.category as category, noticesubject, noticedescription, date, refer_id FROM newnotice, category WHERE newnotice.category = category.id and newnotice.date BETWEEN '${to}' AND '${from}' and category.userType  IN ('3','0') and category.deptid IN ('${dept}','0')";
}
elseif ($_SESSION['user_type'] == "studentid") {

    $studentid = $_SESSION['auth_user']['studentid'];
    $catPrf = "SELECT * FROM preferences WHERE useerid = '$studentid'";
    $q=mysqli_query($link,$catPrf);
    $data=mysqli_fetch_all($q,MYSQLI_ASSOC);
   foreach ($data as $value) {
        $pref[] = $value['categoryId'];
    }
    if (empty($pref)){
        $comma_separated = "('')";
    }

    else{
     $comma_separated = "('" . implode("','", $pref) . "')";
    }
	$dept = $_SESSION['auth_user']['deptid'];
    $sql = "SELECT * FROM newnotice, category WHERE newnotice.category = category.id and newnotice.date BETWEEN '${to}' AND '${from}' and category.userType IN ('2','0') and category.deptid  IN ('${dept}','0') and category.fixedStatus = '0' and newnotice.category IN ". $comma_separated . "UNION SELECT * FROM newnotice, category WHERE newnotice.category = category.id and newnotice.date BETWEEN '${to}' AND '${from}' and category.userType IN ('2','0') and category.deptid IN ('${dept}','0') and category.fixedStatus = '1'";
}
else {
	$sql = "SELECT * FROM newnotice WHERE newnotice.date BETWEEN '${to}' AND '${from}'";
}
?>


<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Online Notice Board</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <scrip src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></scrip>

</head>
<body>
<div class="container">

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12 mt-5">
                <h3 class="text-center " style="color:#0ED104;background-color: #285C5C;padding: 5px;">Notice</h3>
                <hr>
                <div class="col-md-6">
                    <form method="post">
                        <div class="form-group">
                            <label style="color: #fff;font-size: 20px;">To</label>
                            <input type="date"  class="form-control" name="toDate"  >
                        </div>
                        <div class="form-group">
                            <label style="color: #fff;font-size: 20px;">From</label>
                            <input type="Date"  class="form-control" name="fromDate"  >
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 2%;margin-left: 40%;">Search</button>
                    </form>
                </div>
                
            </div>
        </div>

        
        <table class="table mt-5 bg-white">
            <thead>
            <tr>
                <th> Serial Number</th>
                <th>Category</th>
                <th>Notice Subject</th>
                <th>Notice Description</th>
                <th>Refer ID</th>
                <th>Date</th>
                <?php if ($_SESSION['user_type'] == "deptUser" || $_SESSION['user_type']== "userid") { ?>
                <th>Options</th>
                <?php } ?>
            </tr>
            </thead>
            <?php
                $query=mysqli_query($link,$sql);
                if (!empty($query)) {
                $data=mysqli_fetch_all($query,MYSQLI_ASSOC);

                $i = 1;

                    foreach ($data as $row) {
                    ?>

                    <tr>
                        <th scope="row"> <?php echo $i++ ?> </th>
                        <td><?= $row['category'] ?></td>
                        <td><?= $row['noticesubject'] ?></td>
                        <td><?= $row['noticedescription'] ?></td>
                        <td><?= $row['refer_id'] ?></td>
                        <td><?= $row['date'] ?></td>
                    <?php if ($_SESSION['user_type'] == "deptUser" || $_SESSION['user_type']== "userid") {
                         if ($_SESSION['user_type'] == "deptUser") {
                            if ($row['adminId'] == $userid) {
                                ?>
                                <th>
                                <a href="editNotice.php?id=<?=$row['id']?>"><button>Edit</button></a>
                                </th>
                                <th>
                                    <a href="deleteNotice.php?id=<?=$row['id']?>"><button>Delete</button></a>
                                </th>
                                <?php
                            }
                         } else{
                    ?>
                        <th>
                            <a href="editNotice.php?id=<?=$row['id']?>"><button>Edit</button></a>
                        </th>
                        <th>
                            <a href="deleteNotice.php?id=<?=$row['id']?>"><button>Delete</button></a>
                        </th>
                    <?php }} ?>
                    </tr>
                <?php } } ?>

        </table>
    </div>
</div>
</body>
</html>