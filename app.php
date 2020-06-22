<!DOCTYPE html>
<?php
  //  error reporting snippet
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  require_once "includes/connect.php";


  $achievement_accumulator = $category_quality_counter = $category_percent = $category_indexes_array = [];

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $permalink = $_GET['permalink'];
  }

  $job_info_query = "SELECT * FROM `job` WHERE `permalink` = '$permalink'";
  $query1 = mysqli_query($connection, $job_info_query);
  $row1 = mysqli_fetch_array($query1);

  //  job index gets categories for job description
  $job_index = $row1['job_index'];
  $job_name = $row1['name'];
  $job_link = $row1['link'];

  //  get categories from job index
  $category_list_query = "SELECT `category_index`, `category` FROM `job_qualifier_categories` WHERE `job_index` = '$job_index'";
  $query2 = mysqli_query($connection, $category_list_query);
  //  iterate through category list
  while ($row2 = mysqli_fetch_array($query2)) {
    //  general category list used throughout page.
    $category_array[$row2['category_index']] = $row2['category'];
    //  partial query to be concatenated into string with multiple category indexes
    $category_indexes_array[] = "Q.`category_index` = " . $row2['category_index'];
  }
  //  concatenate into string for multiple category indexes
  $category_indexes_string = implode(" OR ", $category_indexes_array);
  //  query for all qualities matching the categories related to this job
  $qa_query = "SELECT Q.`category_index`, Q.`quality`, A.`answer`, A.`achieved` FROM `job_qualifier` AS Q JOIN `job_answers` AS A WHERE Q.`qa_index` = A.`qa_index` AND Q.`category_index` = Q.`category_index` AND ($category_indexes_string) ORDER BY Q.`qa_index` ASC";
  $query3 = mysqli_query($connection, $qa_query);

  //  iterate through all qualities to sum total achievement values and count number of values per category
  while ($row3 = mysqli_fetch_array($query3)) {
    //  if the current category is not yet in the array
    if (!isset($achievement_accumulator[$row3['category_index']])) {
      //  initial value = first value
      $achievement_accumulator[$row3['category_index']] = $row3['achieved'];
      //  first item in count
      $category_quality_counter[$row3['category_index']] = 1;
    }
    else {
      //  add value to existing sum
      $achievement_accumulator[$row3['category_index']] = $achievement_accumulator[$row3['category_index']] + $row3['achieved'];
      //  +1 to count for category
      $category_quality_counter[$row3['category_index']]++;
    }
  }

  //  calculate percentage qualified for each category
  foreach ($achievement_accumulator as $key => $value) {
    $category_percent[$key] = $value / $category_quality_counter[$key];
  }
  //  reset array pointer to beginning
  mysqli_data_seek($query3, 0);
?>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?= $job_name ?> Job Qualifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="includes/css/style.min.css">
    <link rel="stylesheet" href="includes/css/app.css">

    <style media="screen">
    <?php
      $index = 1;
      foreach ($category_percent as $key => $value) {
        $progress_Class = ".progress-$index";
        $total_deg = ($value)*3.6;
        if ($total_deg >= 180) {
          $degA = 180;
          $degB = $total_deg - 180;
          $aTime = 2/$total_deg*180;
          $bTime = 2 - $aTime;
        }
        else {
          $degA = $total_deg;
          $degB = 0;
          $aTime = 2;
          $bTime = 0;

        } ?>
        <?= $progress_Class; ?> .progress-right .progress-bar{
            animation: loading-<?= $index; ?>A <?= $aTime; ?>s linear forwards;
        }

        <?= $progress_Class; ?> .progress-left .progress-bar{
            animation: loading-<?= $index; ?>B <?= $bTime; ?>s linear forwards <?= $aTime; ?>s;
        }

        @keyframes loading-<?= $index; ?>A {
            0%{
                -webkit-transform: rotate(0deg);
                transform: rotate(0deg);
                border-color: red;
            }
            100%{
                -webkit-transform: rotate(<?= $degA; ?>deg);
                transform: rotate(<?= $degA; ?>deg);
                border-color:rgb(<?= round((100-$value)*2.55);?>,<?= round($value*2.55); ?>, 0);
            }
        }
        @keyframes loading-<?= $index++; ?>B {
            0%{
                -webkit-transform: rotate(0deg);
                transform: rotate(0deg);
                border-color:rgb(<?= round((100-$value)*2.55);?>,<?= round($value*2.55); ?>, 0);
            }
            100%{
                -webkit-transform: rotate(<?= $degB; ?>deg);
                transform: rotate(<?= $degB; ?>deg);
                border-color:rgb(<?= round((100-$value)*2.55);?>,<?= round($value*2.55); ?>, 0);
            }
        }
        <?php
      }
    ?>
    </style>

  </head>
  <body onload="Onload()">
    <h1 class="text-center mt-5"><?= $job_name ?> Job Qualifications</h1>

    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.11/jquery.mixitup.min.js"></script>
    <!-- Include the above in your HEAD tag ---------->
    <div class="wrapper progress-wrapper">
      <div class="container">
          <div class="row">
            <?php
              $counted_categories = count($category_array);
              //  calculate width of columns based on number of categories
              $columns_per = ceil(12/$counted_categories);
              $i = 1;
              foreach ($category_array as $key => $value) {
              ?>
                <div class="col-md-<?= $columns_per; ?> col-sm-6">
                  <h4 class="progress-header"><?= $value; ?></h4>
                    <!--- Parent target of progress bars --->
                    <div class="progress-<?= $i; ?> progress">
                        <span class="progress-left">
                            <span class="progress-bar"></span>
                        </span>
                        <span class="progress-right">
                            <span class="progress-bar"></span>
                        </span>
                        <div class="progress-value">
                            <!--- Target for Javascript to change innerHTML --->
                            <span name="<?= round($category_percent[$key]); ?>" class="number-spot category-<?= $key ?>"  id="counter-<?= $i++; ?>">0</span>%
                        </div>
                    </div>
                </div>
              <?php
            } //  END forEach category
            ?>
          </div>
      </div>
    </div>
    <?php
      //  Begin Categorical breakdown of desired qualities
      foreach ($category_array as $key => $value) {
        ?>
        <div class="wrapper slide-in-right">
        <!--- category name and percentage --->
        <h2><?= $value . " " . round($category_percent[$key]) . "%"; ?></h2>
        <div class="tab">
          <?php
          //  reset array pointer to beginning
          mysqli_data_seek($query3, 0);
          //  Begin listing qualities within category
          while ($row3 = mysqli_fetch_array($query3)) {
            //  if this quality belongs in this category
            if ($row3['category_index'] == $key) {
              //  if feeling possitive about my qualifications (top 1/3 of 100%)
              if ($row3['achieved'] > 66) {
                $class_Tag = 'positive';
                $symbol = '++  ';
              }
              //  if feeling ok about my qualifications (middle 1/3 of 100%)
              elseif ($row3['achieved'] > 33) {
                $class_Tag = 'medium';
                $symbol = '+-  ';
              }
              //  if feeling negative about my qualifications (bottom 1/3 of 100%)
              else {
                $class_Tag = 'negative';
                $symbol = '--  ';
              }
              ?>
              <!--- class depends on qualification score  --->
              <div class="accordian <?= $class_Tag; ?>">
                <!--- Append symbol to front of qualification (git style) --->
                <p class=""><?= $symbol . $row3['quality']; ?></p>
                <div class="hidden_panel panel tab">
                  <!--- insert answer as well --->
                  <p class="tab m-1 p-1"><?= $row3['answer']; ?></p>
                </div>
              </div>
              <?php
            } //  END of If quality matches category
          } //  END of listing qualities

          //  reset array pointer to beginning
          mysqli_data_seek($query3, 0); ?>
          </div>
        </div>
       <?php
     } // END of Categorical breakdown of qualities
    ?>
    <script>
      var acc = document.getElementsByClassName("accordian");
      var i;

      for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
          this.classList.toggle("active");
          var panel = this.children[1];

          if (panel.style.maxHeight != "0") {
            panel.classList.toggle("hidden_panel");
          } else {
            panel.classList.toggle("hidden_panel");
          }
        });
      }
      var counttx = 0, countup = true, countmax = 0;

      function Onload() {
        var started = Date.now();

        // make it loop every 100 milliseconds
        var interval = setInterval(function(){
          if (Date.now() - started > 5000) {
            clearInterval(interval);
          } else {
            timerr();
          }
        }, 50); // every 100 milliseconds
      }

      function timerr() {

        var numberSpots = document.getElementsByClassName('number-spot');
        var id = 0;
        for (var i = 0; i < numberSpots.length; i++) {
          countup = true
          var percent = numberSpots[i].getAttribute('name');
          if (countup) {
            ++counttx;
            if (counttx <= percent) {
              numberSpots[i].innerHTML = counttx;
              countup = false;
            }
            if (counttx >= 100) {
              countup = false;
            }
          }
        }
      }
    </script>
  </body>
</html>
