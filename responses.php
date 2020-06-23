<!DOCTYPE html>
<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  $success = '';
  $pageTitle = "Job Qualification Responses";
  include_once "../includes/connect.php";
  require_once('includes/connect.php');
  require_once('check-login.php');
  include('includes/header.php');
  include('includes/navigation.php');
  require_once('includes/connect.php');


  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!empty($_POST["name"])) {
      $job_name = test_input($_POST["name"]);
    }
    if (!empty($_POST["permalink"])) {
      $permalink = test_input($_POST["permalink"]);
    }
    if (!empty($_POST["link"])) {
      $link = test_input($_POST["link"]);
    }
    if (!empty($_POST["job_qualifications"])) {

      $job_qualifications = explode("\n", trim(str_replace("•","", trim($_POST["job_qualifications"]))));
      $delete_Keys = [];
      foreach ($job_qualifications as $key => $value) {
        if (trim($value) == '') {
          $delete_Keys[] = $key;
        }
      }
      foreach ($delete_Keys as $key => $value) {
        unset($job_qualifications[$value]);
      }
      $job_qualifications = array_values($job_qualifications);
      $quality_Count = count($job_qualifications);
    }
/*
    if (trim($job_name) != "" AND trim($link) != "" AND trim($permalink) != "") {
      $query="INSERT INTO qualifier (name, permalink, link) VALUES ('$job_name','$permalink','$link')";
      $result = mysqli_query($connection, $query);
    }
*/
 }

 function test_input($data) {
   global $connection;
   $data = trim($data);
   $data = mysqli_real_escape_string($connection, $data);
   return $data;
}

function endsWith($haystack, $needle)
{
    if (substr($haystack,-1) == $needle) {
        return true;
    }
    else {
      return false;
    }
}

?>

<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Edit Job Qualifications</title>
    <style media="screen">
      .insert_job_wrapper {

      }
    </style>
  </head>
  <body onload="category_Onload(<?= $quality_Count; ?>)">

    <div class="container my-5">
      <div class="row">
          <div class="col">
            <h1><?= $job_name; ?></h1>
            <label for="permalink">Permalink: </label>
            <input type="text" name="permalink" value="<?= $permalink; ?>" disabled style="width:100%; margin-bottom:1rem;">
            <label for="link">Link to the job posting:</label>
            <input type="url" name="link" value="<?= $link; ?>" disabled style="width:100%; margin-bottom:1rem;">
          </div>
        </div>

        <form class="" action="submitter.php" method="post">
          <div class="row mb-5">
              <input type="hidden" name="job_name" value="<?= $job_name; ?>">
              <input type="hidden" name="permalink" value="<?= $permalink; ?>">
              <input type="hidden" name="link" value="<?= $link; ?>">
              <?php foreach ($job_qualifications as $key => $value) {
                  $value = trim($value);?>
                  <div id="wrapper-<?= $key; ?>" class="mx-0 p-2" style="width:100%;transition: all .5s linear;<?= ($key % 2 == 0) ? 'background-color:lightgrey' : '' ?>">
                    <div class="" style="margin-top:2rem;display:inline-block; width:85%;text-align:right;">
                      <label id="label-<?= $key; ?>" for="quality-<?= $key; ?>">quality <?= $key+1; ?></label>
                      <input type="text" name="quality-<?= $key; ?>" value="<?= $value; ?>" style="width:91%; margin-bottom:1rem;">
                    </div>
                    <div class="" style="text-align:right;display:inline-block; width:14%;">
                      <label for="category-<?= $key; ?>">Is this a category?
                        <?php $likely_Category = endsWith($value,':');?>
                        <input type="checkbox" name="category-<?= $key; ?>" id="category-<?= $key; ?>" value="yes" onclick="category(this, <?=$key;?>);" <?php echo ($likely_Category) ? 'checked' : '' ?>>
                      </label>
                    </div>

                    <div id="answer_row-<?= $key; ?>">
                      <div id="" style="display: inline-block;width:85%;text-align:right;">
                        <label for="answer-<?= $key; ?>">Answer <?= $key+1; ?></label>
                        <textarea name="answer-<?= $key; ?>" rows="2" style="width:91%;"></textarea>
                      </div>
                      <div style="text-align:center;margin-bottom:2rem;display:inline-block; width:13%;">
                        <div class="form-group ml-3 mr-2">
                          <label for="formControlRange">Qualification achieved?</label>
                          <input type="range" class="form-control-range" name="achievement-<?= $key; ?>" id="formControlRange-<?= $key; ?>" min="1" max="100" value="50">
                        </div>
                      </div>
                    </div>

                  </div> <?php
              }
              ?>
              </div>
            <input type="submit" name="submit" value="submit">
        </form>
    </div>

    <script>
      function category(cb, key) {
        var element = document.getElementById("answer_row-"+key);
        var label = document.getElementById("label-"+key);
        var wrapper = document.getElementById("wrapper-"+key);

        if (cb.checked) {
          element.style.display = "none";
          label.style.visibility = "hidden";
          wrapper.style.backgroundColor = "#009ADA";
        }
        else {
          if (key % 2 == 0) {
            wrapper.style.backgroundColor = "lightgrey";
          }
          else {
            wrapper.style.backgroundColor = "unset";
          }
          label.style.visibility = "visible"
          element.style.display = "block";
        }
      }

        function category_Onload(element_Count) {
          for (var key = 0; key < element_Count; key++) {
            console.log('test ' + key);
            var cb = document.getElementById("category-"+key);
            if (cb.checked) {
              var element = document.getElementById("answer_row-"+key);
              var label = document.getElementById("label-"+key);
              var wrapper = document.getElementById("wrapper-"+key);
              element.style.display = "none";
              label.style.visibility = "hidden";
              wrapper.style.backgroundColor = "#009ADA";
            }
          }
        }


    </script>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
 <?php include('includes/footer.php'); ?>
