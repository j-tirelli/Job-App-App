<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  $success = '';
  $pageTitle = "Insert Job";
  include_once "../includes/connect.php";
  require_once('includes/connect.php');
  require_once('check-login.php');
  include('includes/header.php');
  include('includes/navigation.php');

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["name"])) {
    } else {
      $job_name = test_input($_POST["name"]);
    }
    if (empty($_POST["permalink"])) {
    } else {
      $permalink = test_input($_POST["permalink"]);
    }
    if (empty($_POST["link"])) {
    } else {
      $link = test_input($_POST["link"]);
    }

    if (trim($job_name) != "" AND trim($link) != "" AND trim($permalink) != "") {
      $query="INSERT INTO qualifier (name, permalink, link) VALUES ('$job_name','$permalink','$link')";
      $result = mysqli_query($connection, $query);
    }
 }

  function test_input($data) {
    global $connection;
    $data = trim($data);
    $data = mysqli_real_escape_string($connection, $data);
    return $data;
}

?>
      <div class="row my-5">
          <div class="col my-auto text-right">
            <form class="" action="responses.php" method="post">
            <div class="">
              <label for="name">Name of Company: </label>
              <input type="text" name="name" value="" required >
            </div>
            <div class="">
              <label for="permalink">Permalink: </label>
              <input type="text" name="permalink" value="" required >
            </div>
            <div class="">
              <label for="link">Link to the job posting:</label>
              <input type="url" name="link" value="" required >
            </div>
          </div>
          <div class="col" style>
            <label for="job_qualifications">Insert the job qualifications (new line for each item): </label>
            <textarea name="job_qualifications" rows="24" cols="80" required ></textarea>
            <input type="submit" name="submit" value="SUBMIT">
          </div>
        </form>
      </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
 <?php include('includes/footer.php'); ?>
