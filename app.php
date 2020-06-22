<!DOCTYPE html>
<?php
  //  error reporting snippet
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  require_once "includes/connect.php";

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $permalink = $_GET['permalink'];
  }
  $job_info_query = "SELECT * FROM `job` WHERE `permalink` = '$permalink'";
  $query1 = mysqli_query($connection, $job_info_query);
  $row1 = mysqli_fetch_array($query1);
  $job_index = $row1['job_index'];
  $job_name = $row1['name'];
  $job_link = $row1['link'];
  $category_list_query = "SELECT `category_index`, `category` FROM `job_qualifier_categories` WHERE `job_index` = '$job_index'";
  $category_indexes_array = [];
  $query2 = mysqli_query($connection, $category_list_query);
  while ($row2 = mysqli_fetch_array($query2)) {
    $category_array[$row2['category_index']] = $row2['category'];
    $category_indexes_array[] = "Q.`category_index` = " . $row2['category_index'];
  }
  $category_indexes_string = implode(" OR ", $category_indexes_array);
  $qa_query = "SELECT Q.`category_index`, Q.`quality`, A.`answer`, A.`achieved` FROM `job_qualifier` AS Q JOIN `job_answers` AS A WHERE Q.`qa_index` = A.`qa_index` AND Q.`category_index` = Q.`category_index` AND ($category_indexes_string) ORDER BY Q.`qa_index` ASC";
  $query3 = mysqli_query($connection, $qa_query);
  $query4 = mysqli_query($connection, $qa_query);
  $achievement_accumulator = $category_quality_counter = $category_percent = [];
  while ($row3 = mysqli_fetch_array($query3)) {
    if (!isset($achievement_accumulator[$row3['category_index']])) {
      $achievement_accumulator[$row3['category_index']] = $row3['achieved'];
      $category_quality_counter[$row3['category_index']] = 1;
    }
    else {
      $achievement_accumulator[$row3['category_index']] = $achievement_accumulator[$row3['category_index']] + $row3['achieved'];
      $category_quality_counter[$row3['category_index']]++;
    }
  }
  mysqli_data_seek($query3, 0);
  foreach ($achievement_accumulator as $key => $value) {
    $category_percent[$key] = $value / $category_quality_counter[$key];
  }
?>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?= $job_name ?> Job Qualifications</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="includes/css/style.min.css">

    <style media="screen">
      h1,h2,h3,h4 {
        color: #000;
      }

      body {
        text-align: left;
      }

      p {
      }

      .accordian, .accordian p {
        margin-bottom: .5rem;
      }

      .positive {
        cursor: pointer;
        color:green;
        transition: all .3s linear;
      }

      .positive:hover, .accordian.active.positive {
        color: black;
        background-color: rgb(240,255,240);
        box-shadow: 4px 4px 2px grey;
      }

      .medium {
        cursor: pointer;
        color:orange;
        transition: all .3s linear;
      }

      .medium:hover, .accordian.active.medium {
        color: black;
        background-color: rgb(255,240,225);
        box-shadow: 4px 4px 2px grey;
      }

      .negative {
        cursor: pointer;
        color:red;
        transition: all .3s linear;
      }

      .negative:hover, .accordian.active.negative {
        color: black;
        background-color: rgb(255,240,240);
        box-shadow: 4px 4px 2px grey;
      }

      .progress{
          width: 150px;
          height: 150px;
          line-height: 150px;
          background: none;
          margin: 0 auto;
          box-shadow: none;
          position: relative;
      }
      .progress:after{
          content: "";
          width: 100%;
          height: 100%;
          border-radius: 50%;
          border: 2px solid #fff;
          position: absolute;
          top: 0;
          left: 0;
      }
      .progress > span{
          width: 50%;
          height: 100%;
          overflow: hidden;
          position: absolute;
          top: 0;
          z-index: 1;
      }
      .progress .progress-left{
          left: 0;
      }
      .progress .progress-bar{
          width: 100%;
          height: 100%;
          background: none;
          border-width: 5px;
          border-style: solid;
          position: absolute;
          top: 0;
          border-color: #0dff00;
      }

      .progress .progress-left .progress-bar{
          left: 100%;
          border-top-right-radius: 80px;
          border-bottom-right-radius: 80px;
          border-left: 0;
          -webkit-transform-origin: center left;
          transform-origin: center left;
      }
      .progress .progress-right{
          right: 0;
      }
      .progress .progress-right .progress-bar{
          left: -100%;
          border-top-left-radius: 80px;
          border-bottom-left-radius: 80px;
          border-right: 0;
          -webkit-transform-origin: center right;
          transform-origin: center right;
      }

      .progress .progress-value{
          width: 85%;
          height: 85%;
          border-radius: 50%;
          border: 2px solid #ebebeb;
          font-size: 32px;
          line-height: 125px;
          text-align: center;
          position: absolute;
          top: 7.5%;
          left: 7.5%;
          color: black;
      }

      .slide-in-right {
          animation: slideInRight 1s ease both; }

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
           <?php } ?>

      @keyframes slideInLeft {
          0% {
              opacity: 0;
              transform: translateX(-90vw);
          }
          100% {
              opacity: 1;
              transform: translateX(0);
          }
      }

      @media only screen and (max-width: 990px){
          .progress{ margin-bottom: 20px; }
      }
      @media only screen and (max-width: 1032px){
          .progress{ margin-bottom: 20px; }
          h4 {
            font-size: 1rem;
          }
      }
      .progress-header {
        text-align:center;
      }


      .tab {
        margin-left:2.5rem;
      }

      .wrapper {
        width:95%;
        margin:auto;
        padding:2rem;
        margin-bottom:2rem;
        margin-top:2rem;
        border-style: groove;
      }

      .progress-wrapper {
        background-color: rgb(246,246,246);
        border: 1px solid black;
      }

      .wrapper:hover {
        background-color: rgb(250,250,250)
      }

      .panel {
        padding: 0 18px;
        display: block;
        overflow: hidden;
        transition: all .3s linear;
        transform: scale(1, 1);
        max-height: 10rem;
      }

      .hidden_panel {
        transition: all .3s linear;
        max-height: 0;
      }

    </style>

  </head>
  <body onload="Onload()">
    <h1 class="text-center mt-5"><?= $job_name ?> Job Qualifications</h1>

    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.11/jquery.mixitup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.0/jquery.waypoints.min.js"></script>
    <!-- Include the above in your HEAD tag ---------->
    <div class="wrapper progress-wrapper">
      <div class="container">
          <div class="row">
            <?php
              $counted_categories = count($category_array);
              $columns_per = 12/$counted_categories;
              $i = 1;
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
      foreach ($category_array as $key => $value) {
        ?>
      <div class="wrapper slide-in-right">
        <h2><?= $value . " " . round($category_percent[$key]) . "%"; ?></h2>
        <div class="tab">
          <?php
          while ($row3 = mysqli_fetch_array($query3)) {
            if ($row3['category_index'] == $key) {
              if ($row3['achieved'] > 66) {
                $endTag = 'positive';
                $symbol = '++  ';
              }
              elseif ($row3['achieved'] > 33) {
                $endTag = 'medium';
                $symbol = '+-  ';
              }
              else {
                $endTag = 'negative';
                $symbol = '--  ';
              }
              ?>
              <div class="accordian <?= $endTag; ?>">
                <p class=""><?= $symbol . $row3['quality']; ?></p>
                <div class="hidden_panel panel tab">
                  <p class="tab m-1 p-1"><?= $row3['answer']; ?></p>
                </div>
              </div>
              <?php
            }
          }
          mysqli_data_seek($query3, 0); ?>
          </div>
        </div>
       <?php
      }
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
    </script>
  </body>
</html>

<script>
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

<script>
  // SCROLL ANIMATIONS
  function onScrollInit( items, elemTrigger ) {
    var offset = $(window).height() / 1.6
    items.each( function() {
      var elem = $(this),
          animationClass = elem.attr('data-animation'),
          animationDelay = elem.attr('data-delay');

          elem.css({
            '-webkit-animation-delay':  animationDelay,
            '-moz-animation-delay':     animationDelay,
            'animation-delay':          animationDelay
          });

          var trigger = (elemTrigger) ? trigger : elem;

          trigger.waypoint(function() {
            elem.addClass('animated').addClass(animationClass);
            if (elem.get(0).id === 'gallery') mixClear(); //OPTIONAL
            },{
                triggerOnce: true,
                offset: offset
          });
    });
  }

  setTimeout(function() { onScrollInit($('.waypoint')) }, 10);

</script>
