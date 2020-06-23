<!DOCTYPE html>
<?php
  //  error reporting snippet
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  require_once('includes/connect.php');


  $achievement_accumulator = $category_quality_counter = $category_percent = $category_indexes_array = [];

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $permalink = $_GET['permalink'];
  }

  //  Get job data from permalink query
  $job_info_query = "SELECT * FROM `job` WHERE `permalink` = ?";
  $result = $db->prepare($job_info_query);
  $result->execute(array($_GET['permalink']));
  $row1 = $result->fetch(PDO::FETCH_ASSOC);

  //  get categories from job index
  $category_list_query = "SELECT `category_index`, `category` FROM `job_qualifier_categories` WHERE `job_index` = ?";
  $result = $db->prepare($category_list_query);
  $result->execute(array($row1['job_index']));
  //  iterate through category list
  while ($row2 = $result->fetch(PDO::FETCH_ASSOC)) {
    //  general category list used throughout page.
    $category_array[$row2['category_index']] = $row2['category'];
    //  partial query to be concatenated into string with multiple category indexes
    $category_indexes_array[] = $row2['category_index'];
  }

  $qa_query = "SELECT Q.`category_index`, Q.`quality`, A.`answer`, A.`achieved` FROM `job_qualifier` AS Q JOIN `job_answers` AS A WHERE Q.`qa_index` = A.`qa_index` AND Q.`category_index` = ? ORDER BY Q.`qa_index` ASC";
  $result = $db->prepare($qa_query);
  $row3 = [];

  //  iterate through each category to get qualities for each
  foreach ($category_indexes_array as $key => $value) {
    $result->execute(array($value));
    $row3[] = $result->fetchAll();
  }
  //  iterate through quality list to accumulate a total quality score for each category
  foreach ($row3 as $k => $v) {
    foreach ($v as $key => $value) {
      $cat_index = $row3[$k][$key]['category_index'];
      //  if the current category is not yet in the array
      if (!isset($achievement_accumulator[$cat_index])) {
        //  initial value = first value
        $achievement_accumulator[$cat_index] = $row3[$k][$key]['achieved'];
        //  first item in count
        $category_quality_counter[$cat_index] = 1;
      }
      else {
        //  add value to existing sum
        $achievement_accumulator[$cat_index] = $achievement_accumulator[$cat_index] + $row3[$k][$key]['achieved'];
        //  +1 to count for category
        $category_quality_counter[$cat_index]++;
      }
    }
  }

  //  calculate percentage qualified for each category
  foreach ($achievement_accumulator as $key => $value) {
    $category_percent[$key] = $value / $category_quality_counter[$key];
  }
?>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?= $row1['name'] ?> Job Qualifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../includes/css/style.min.css">
    <link rel="stylesheet" href="../includes/css/app.css">

    <style media="screen">
    <?php
      //  CSS in PHP to dynamically create rules as needed

      //  index to label rulesets
      $index = 1;
      //  Calculate degrees of rotation and timing of animations based on percentages
      foreach ($category_percent as $key => $value) {
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

        /* Create dynamically labelled and timed rules */
        <?= ".progress-".$index; ?> .progress-right .progress-bar{
            animation: loading-<?= $index; ?>A <?= $aTime; ?>s linear forwards;
        }

        <?= ".progress-".$index; ?> .progress-left .progress-bar{
            animation: loading-<?= $index; ?>B <?= $bTime; ?>s linear forwards <?= $aTime; ?>s;
        }

        /* Create dynamically labelled and rotated rules Right side*/
        @keyframes loading-<?= $index; ?>A {
            0%{
                -webkit-transform: rotate(0deg);
                transform: rotate(0deg);
                border-color: red;
            }
            100%{
                -webkit-transform: rotate(<?= $degA; ?>deg);
                transform: rotate(<?= $degA; ?>deg);
                /* Color the end result of ring based on percentage complete converted to red-green gradiant */
                border-color:rgb(<?= round((100-$value)*2.55);?>,<?= round($value*2.55); ?>, 0);
            }
        }
        /* Create dynamically labelled and rotated rules Left side*/
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
    <h1 class="text-center mt-5"><?= $row1['name'] ?> Job Qualifications</h1>
    <div class="text-center">
      <a class="text-center"style="color:green;" href="<?= $row1['link'] ?>" target="_blank"><?= $row1['link'] ?></a>
    </div>

    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.11/jquery.mixitup.min.js"></script>
    <!-- Include the above in your HEAD tag ---------->
    <div class="wrapper progress-wrapper">
      <div class="container">
          <div class="row">
            <?php
              //  set column width dynamically based on number of categories
              $columns_per = 12/count($category_array);
              $i = 1;

              //  Create columns for animated rings
              foreach ($category_array as $key => $value) {
              ?>
                <div class="col-md-<?= $columns_per; ?> col-sm-6">
                  <h4 class="progress-header"><?= $value; ?></h4>
                    <div class="progress-<?= $i; ?> progress">
                        <span class="progress-left">
                            <span class="progress-bar"></span>
                        </span>
                        <span class="progress-right">
                            <span class="progress-bar"></span>
                        </span>
                        <!--- Target for JS number counter --->
                        <div class="progress-value"><span name="<?= round($category_percent[$key]); ?>" class="number-spot category-<?= $key ?>"  id="counter-<?= $i++; ?>"></span>%</div>
                    </div>
                </div>
              <?php
              }
            ?>
          </div>
      </div>
    </div>
    <?php
      //  Create a section for each category
      foreach ($category_array as $key => $value) {
        ?>
      <div class="wrapper slide-in-right">
        <!--- Label and percentage --->
        <h2><?= $value . " " . round($category_percent[$key]) . "%"; ?></h2>
        <div class="tab">
          <?php

          //  Create accordian tabs for each item
          foreach ($row3 as $k => $v) {
            foreach ($v as $keys => $value) {
              $this_row = $row3[$k][$keys];

              //  Assign an quality class based on quality percentage (33/33/34)
              if ($this_row['category_index'] == $key) {
                if ($this_row['achieved'] > 66) {
                  $Qclass = 'positive';
                  $symbol = '++  ';
                }
                elseif ($this_row['achieved'] > 33) {
                  $Qclass = 'medium';
                  $symbol = '+-  ';
                }
                else {
                  $Qclass = 'negative';
                  $symbol = '--  ';
                }
                ?>
                <div class="accordian <?= $Qclass; ?>">
                  <p class=""><?= $symbol . $this_row['quality']; ?></p>
                  <div class="hidden_panel panel tab">
                    <!--- Insert response desired quality  --->
                    <p class="tab m-1 p-1"><?= $this_row['answer']; ?></p>
                  </div>
                </div>
                <?php
              }
            }
          }
          ?>
          </div>
        </div>
       <?php
      }
    ?>
    <script>

      //  accordian functionality
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

      //  Run when body loads, starts the timerr function and runs it for ~5 seconds
      function Onload() {
        var started = Date.now();

        // make it loop every 50 milliseconds
        var interval = setInterval(function(){
          if (Date.now() - started > 5500) {
            clearInterval(interval);
          } else {
            timerr();
          }
        }, 50); // every 50 milliseconds
      }

      var counttx = 0, countup = true, countmax = 0;

      //  Increase percentage until it reaches desired percentage
      function timerr() {
        //  target the proper elements
        var numberSpots = document.getElementsByClassName('number-spot');
        //  for each element with class name number-spot
        for (var i = 0; i < numberSpots.length; i++) {
          countup = true;
          //  percentage stored in name attribute
          var percent = numberSpots[i].getAttribute('name');
          //  if function should be active
          if (countup) {
            ++counttx;
            //  update the percentage as long as it is below the threshhold (percent)
            if (counttx <= percent) {
              numberSpots[i].innerHTML = counttx;
            }
            //  else stop
            else {
              countup = false;
            }
          }
        }
      }
    </script>
  </body>
</html>
