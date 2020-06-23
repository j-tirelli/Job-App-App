<!DOCTYPE html>
<?php
  //  error reporting snippet
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  //  variable used to track succesful _____.
  $success = '';
  $pageTitle = "Job submitter";
  //  include mysqli connection info. Replace with PDO later.
  require_once "../includes/connect.php";
  require_once('includes/connect.php');
  require_once('check-login.php');
  include('includes/header.php');
  include('includes/navigation.php');


  function test_input($data) {
   global $connection;
   $data = trim($data);
   $data = mysqli_real_escape_string($connection, $data);
   return $data;
  }



  //  Get the last used quality and job index numbers.
  $qualifier_index_last_used_query = "SELECT `qa_index` FROM `job_qualifier` ORDER BY `job_qualifier`.`qa_index` DESC LIMIT 1";
  $job_index_last_used_query = "SELECT `job_index` FROM `job` ORDER BY `job`.`job_index` DESC LIMIT 1";
  $query1 = mysqli_query($connection, $qualifier_index_last_used_query);
  $query2 = mysqli_query($connection, $job_index_last_used_query);
  $row1 = mysqli_fetch_array($query1);
  $row2 = mysqli_fetch_array($query2);
  if (!empty($row1)) {
    $qa_index = $row1[0]+1;
  }
  else {
    $qa_index = 1;
  }
  if (!empty($row2)) {
    $job_index = $row2[0]+1;
  }
  else {
    $job_index = 1;
  }

  $list_of_objects = $answer_array = $achieved_array = array();

?>

<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Submitting Job Qualifications</title>
  </head>
  <body>
  <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
  ?>
    <div class="text-center">
      <h1>Please hold while values are submitted</h1>
      <p>(You will be redirected to a linkable page after submission is complete.)</p>
      <?php sleep(5); ?>
    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <?php
    require_once 'class.quals.php';
    //  iterate through every post value
    foreach($_POST as $key => $value){
        //  objects will be named by order recieved.
        if($key == 'job_name'){
          $job_name = test_input($value);
        }
        elseif ($key == 'permalink') {
          $permalink = test_input($value);
          $permalink_Used = "SELECT `permalink` FROM `job` WHERE `permalink` LIKE '$permalink%' ORDER BY `job`.`job_index` ASC";
          $query3 = mysqli_query($connection, $permalink_Used);
          $permalink_array = $permalink_numbers = [];
          while ($row3 = mysqli_fetch_array($query3)) {
            $permalink_array[] = $row3['permalink'];
          };
          $exists = false;
          foreach ($permalink_array as $key => $value) {
            $exists = true;
            $exploded = explode("-", $value);
            if (isset($exploded[1])) {
              $permalink_numbers[] = $exploded[1];
            }
          }
          $last_used_index = 0;
          foreach ($permalink_numbers as $key => $value) {
            $last_used_index = $value;
          }
          if ($last_used_index > 0 || $exists = true) {
            $last_used_index++;
            $permalink = $permalink . "-$last_used_index";
          }
        }
        elseif ($key == 'link') {
          $link = test_input($value);
        }
        elseif ($key == 'submit') {
        }
        else {
          //  seperate the type of post from the id number
          $exp_key = explode('-', $key);
          //  categories can be processed directly.
          if($exp_key[0] == 'category'){
              // next category index number
              $object_name = end($list_of_objects);
              $category_index = ${$object_name}->make_Category();

          }

          //  Turn qualities into objects, using the last value of the list of objects as a naming convention
          if($exp_key[0] == 'quality'){
              $list_of_objects[] = "a-" . $exp_key[1];
              $value = test_input($value);
              $object_name = end($list_of_objects);
              if (!isset($category_index)) {
                $category_index = $qa_index;
              }
              ${$object_name} = new quality($category_index, $qa_index, $value);
              $qa_index++;
          }

          //  Gather answers into an array to add to quality objects after all objects created
          if($exp_key[0] == 'answer'){
              $value = test_input($value);
              $array_index = end($list_of_objects);
              $answer_array[$array_index] = $value;
          }

          //  Gather achievements into an array to add to quality objects after all objects created
          if($exp_key[0] == 'achievement'){
              $value = test_input($value);
              $array_index = end($list_of_objects);
              $achieved_array[$array_index] = $value;
          }
        }
  }
  //  add answers and achieved status to quality objects
  foreach ($answer_array as $key => $value) {
    ${$key}->insert_Answer($value);
  }
  foreach ($achieved_array as $key => $value) {
    ${$key}->insert_Achieved($value);
  }

  //  Create array from query parts
  foreach ($list_of_objects as $key => $value) {
    if (${$value}->is_Category()) {
      $categories_query_Collector[] = ${$value}->category_query_constructor($job_index);
    }
    else {
      $quality_query_Collector[] = ${$value}->quality_query_constructor();
      $answer_query_Collector[] = ${$value}->answer_query_constructor();
    }
  }

//  SQL INSERTS --------------------------------------------------------------------------------------------------------------------------

  if ($_SESSION['id'] === 1) {
    if (!empty($job_name)) {
      $job_sql = "INSERT INTO job (job_index,name,permalink,link) VALUES ('$job_index','$job_name','$permalink','$link')";
      //  echo "$job_sql<br><br>";
      mysqli_query($connection, $job_sql);
    }
    else {
      exit;
    }
    //  multiple insert of all categories in a single preformatted query
    if (!empty($categories_query_Collector)) {
      $imploded_categories_queries = implode(',', $categories_query_Collector);
      $categories_sql = "INSERT INTO job_qualifier_categories (category_index,job_index,category) VALUES " . $imploded_categories_queries;
      //  echo "$categories_sql<br><br>";
      mysqli_query($connection, $categories_sql);
    }
    //  multiple insert of all qualities in a single preformatted query
    if (!empty($quality_query_Collector)) {
      $imploded_quality_queries = implode(',', $quality_query_Collector);
      $qualifier_sql = "INSERT INTO job_qualifier (category_index,qa_index,quality) VALUES " . $imploded_quality_queries;
      //  echo "$qualifier_sql<br><br>";
      mysqli_query($connection, $qualifier_sql);
    }
    //  multiple insert of all qualities in a single preformatted query
    if (!empty($answer_query_Collector)) {
      $imploded_answer_queries = implode(',', $answer_query_Collector);
      $answer_sql = "INSERT INTO job_answers (qa_index,answer,achieved) VALUES " . $imploded_answer_queries;
      //  echo "$answer_sql<br><br>";
      mysqli_query($connection, $answer_sql);
    }
    header("Location: app.php?permalink=$permalink");
  }
  else {
    header("Location: app.php?permalink=SRS");
  }



  //  END SQL INSERTS --------------------------------------------------------------------------------------------------------------------------

}
else {
?>
<h1>This page is not meant to be loaded directly</h1>
<p>You will be redirected to the start page.</p>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<?php

if ($_SESSION['id'] !== 1) {
  header("Location: app.php?permalink=SRS");

}
else {
  header("Location: insert_job.php");
}

}
include('includes/footer.php'); ?>
