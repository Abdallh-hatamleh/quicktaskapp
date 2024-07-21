<!-- 
 create database taskapp;
 create table projects (id int primary KEY AUTO_INCREMENT, title varchar(255) not null, description varchar(255) null, create_date date DEFAULT(CURRENT_DATE())); 
create table tasks (id int AUTO_INCREMENT PRIMARY KEY, project_id int not null ,FOREIGN KEY (project_id) REFERENCES projects(id), title varchar(255) not null, status varchar(255) not null, created_date date DEFAULT(CURRENT_DATE()), created_time time DEFAULT(CURRENT_TIME())); 
-->


<?php 
try {
  $conn = new pdo("mysql:host=localhost;dbname=taskapp","root","");
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e) {
  echo "". $e->getMessage();
    if (str_contains($e->getMessage(),"Unknown database")) {
        $conn = new pdo ("mysql:host=localhost","root","");
        $conn->query("create database taskapp");
        $conn = new pdo ("mysql:host=localhost;dbname=taskapp","root","");
        $conn->query("create table projects (id int primary KEY AUTO_INCREMENT, title varchar(255) not null, description varchar(255) null, create_date date DEFAULT(CURRENT_DATE())); ");
        $conn->query("create table tasks (id int AUTO_INCREMENT PRIMARY KEY, project_id int not null ,FOREIGN KEY (project_id) REFERENCES projects(id), title varchar(255) not null, status varchar(255) not null, created_date date DEFAULT(CURRENT_DATE()), created_time time DEFAULT(CURRENT_TIME()));");
    }
    else echo "ERROR : ". $e->getMessage();
}
$conn = new pdo("mysql:host=localhost;dbname=taskapp","root","");
if ($_SERVER['REQUEST_METHOD' ] == 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $query = $conn->prepare("delete from projects where id = $id");
        $query->execute();
    }
    if (isset($_POST["add_task"])) {
        // echo "<script> alert('add')</script>";
        $proj_id = $_POST["proj_id"];
        $title = $_POST["title"];
        $status = $_POST['status'];
        $query = $conn->query("insert into tasks (project_id, title, status) values ($proj_id,'$title','$status')");
        $_POST['view'] = 'any';
        $_POST['id'] = $proj_id;
    }
    if (isset($_POST['applyEdit'])) {
        $title = $_POST['title'];
        $status = $_POST['status']; 
        $task_id = $_POST['task_id'];
        $query = $conn->query("update tasks set title='$title', status='$status' where id=$task_id");
        $_POST['view'] = 'any';
        $_POST['id'] = $_POST['proj_id'];
    }
    if (isset($_POST['delete_task'])) {
        $id = $_POST['task_id'];
        $query = $conn->query("delete from tasks where id=$id");
        $_POST['view'] = 'any';
        $_POST['id'] = $_POST['proj_id'];
    }
    if (isset($_POST['addProj'])) {
      $title = $_POST['title'];
      $query = $conn->query("insert into projects (title) values ('$title')");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
* {

    margin: 0;
    padding: 0;
}
body {
    background-color: black;
    display: flex;
}
.projrow , .taskrow{
  display: flex;
  column-gap: 3rem;
}
.project , .task, .editTask {
  padding: 1rem;
  background-color: white;
  border-radius: 1rem;
  margin: 1rem;
  max-width: fit-content;
}
.tasks {
    /* background-color: grey; */
    padding: 1rem;
}
.editTask {
    height: fit-content;
}
    </style>
</head>
<body>
  <div class="projects">
    <form action="" method="post">
      <h2 style="color:white;">Add a project :</h2>
      <input type="text" name="title">
      <input type="submit" name="addProj">
    </form>
    <h2 style="color: white;">Projects : </h2>
 <?php 
 $query = $conn->prepare("select * from projects");
 $query->execute();
 while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $cur_id = $row['id'];
    echo "<div class='project'>Project Title: ". $row["title"] ."<br><br><div class='projrow'>". $row["create_date"] . 
    "<div class='buttons'><form method='post' style='display:inline'><input type='hidden' value='$cur_id' name='id'><input type='submit' value='View' name='view'><input type='submit' value='DELETE' name='delete'></form></div></div>".
    "</div>";
}
?>
  </div>
  <div class="tasks">

    <?php
    if (isset($_POST["view"])) {
        $id = $_POST["id"];
        echo '<form action="" method="post">';
        echo '<label for="title" style="color: white;">Title: </label><br>';
        echo '<input type="text" name="title"><br>';
        // echo '<label for="description">Description: </label><br>';
        // echo '<textarea name="description"></textarea><br>';
        echo '<label for="status" style="color: white;">Status: </label><br>';
        echo '<input type="text" name="status"><br>';
        echo "<input type='hidden' value='$id' name='proj_id'>";
        echo '<input type="submit" name="add_task" value="ADD">';
        echo '</form>';
        $query = $conn->prepare("select * from tasks where project_id =$id");
        $query->execute();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $proj_id = $id;
            $task_id = $row["id"];
            $title = $row["title"];
            $status = $row["status"];
            echo "<div class='task'><h2>". $row["title"] ."</h2><div class='taskrow'>". $row["status"] . "<div>" . $row['created_date'] . " ," .  $row['created_time'] . "</div></div>
            <form method='post'><input type='hidden' value='$status' name='task_status'><input type='hidden' value='$title' name='task_title'>
            <input type='hidden' value='$proj_id' name='proj_id'><input type='hidden' value='$task_id' name='task_id'><input type='submit' name='delete_task' value='Delete'>
            <input type='submit' name='edit_task' value='Edit'></form></div>";
        }
    }
    ?>
  </div>
  <div class="editTask" style="display: <?php
  if (isset($_POST["edit_task"])) {
echo "block";
  }
else echo "none";
  ?>;">
    <h2>Edit Task:</h2>
    <form action="" method="post">
        <?php
        if (isset($_POST["edit_task"])) {
            $id = $_POST['task_id'];
            $proj_id = $_POST['proj_id'];
            $title = $_POST['task_title'];
            $status = $_POST['task_status'];
                echo "<input type='hidden' name='task_id' value='$id'>";
                echo "<input type='hidden' name='proj_id' value='$proj_id'>";
                echo '<label for="title">Title: </label>';                
                echo "<br><input type='text' name='title' value='$title'><br>";
                echo '<label for="status">Status: </label>';
                echo "<br><input type='text' name='status' value='$status'><br>";
                echo '<input type="submit" name="applyEdit">';
        }
?>
    </form>
  </div>
</body>
</html>