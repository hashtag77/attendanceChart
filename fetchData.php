<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<body>
<?php
  ini_set('default_socket_timeout', 5000);
  ini_set("display_errors", 1);

  $handle = fopen('data.csv','r');
  while(!feof($handle))
  {
    $csvData[] = fgetcsv($handle);
  }
  $dataArr = array_chunk($csvData, 23);

  function explodeValue($data) {
    $result = explode(':', $data);
    return trim($result[1], ' ');
  }

  $mainArr = [];
  $dateFrameArr = [];
  foreach ($dataArr as $key => $value) {
    $idExp = explodeValue($value[0][0]);
    $nameExp = explodeValue($value[0][3]);
    $deptExp = explodeValue($value[0][6]);
    $dateFrameExp = explodeValue($value[0][12]);

    $count = 2;
    $counter = 2;
    $dataStore = [];
    for ($i=0; $i <= 31 ; $i++) { 
      ++$count;
      if($count >= 3 && $count <= 18 && $value[$count][0] != '') {
        $summaryDate = str_replace(".", "-", $value[$count][0]);
        $months = [
          "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
        $summaryDateExp = explode("-", $summaryDate);
        $freshDate = $months[$summaryDateExp[0] - 1].' '.$summaryDateExp[1];

        $summaryData = [
          'day'         => $value[$count][1],
          'date'        => $freshDate,
          'logs'     => [
            '1' => [
              'check-in'  => $value[$count][2],
              'check-out' => $value[$count][3]
            ],
            '2' => [
              'check-in'  => $value[$count][4],
              'check-out' => $value[$count][5]
            ],
            '3' => [
              'check-in'  => $value[$count][6],
              'check-out' => $value[$count][7]
            ]
          ],
        ];
        array_push($dataStore, $summaryData);
      } else {
        ++$counter;
        if($counter >= 3 && $counter <= 18 && $value[$counter][8] != '') {
          $summaryDate = str_replace(".", "-", $value[$counter][8]);
          $months = [
            "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
          ];
          $summaryDateExp = explode("-", $summaryDate);
          $freshDate = $months[$summaryDateExp[0] - 1].' '.$summaryDateExp[1];

          $summaryData = [
            'day'         => $value[$counter][9],
            'date'        => $freshDate,
            'logs'     => [
              '1' => [
                'check-in'  => $value[$counter][10],
                'check-out' => $value[$counter][11]
              ],
              '2' => [
                'check-in'  => $value[$counter][12],
                'check-out' => $value[$counter][13]
              ],
              '3' => [
                'check-in'  => $value[$counter][14],
                'check-out' => $value[$counter][15]
              ]
            ],
          ];
          array_push($dataStore, $summaryData);
        }
      }
    }
    $newDate = str_replace("/", "-", $dateFrameExp);
    $dateExp = explode("~", $newDate);
    $date1 = date_create($dateExp[0]);
    $dateData1 = date_format($date1, "M y, d");
    $date2 = date_create($dateExp[1]);
    $dateData2 = date_format($date2, "M y, d");
    $dateFrame = $dateData1." - ".$dateData2;

    if(!in_array($dateFrame, $dateFrameArr)) {
      array_push($dateFrameArr, $dateFrame);
      array_push($dateFrameArr, "Aug 21, 2019 - Aug 23, 2019");
    }

    $data = [
      'id'          => $idExp,
      'name'        => $nameExp,
      'department'  => $deptExp,
      'data-set'     => [
        'date-frame'  => $dateFrame,
        'week'        => $dataStore
      ],
    ];
    array_push($mainArr, $data);
  }
  $json_string = json_encode($mainArr, JSON_PRETTY_PRINT);
  $fp = fopen('json-file.json', 'w');
  fwrite($fp, $json_string);
  fclose($fp);
?>
<input type="hidden" id="mainArr" value="<?php echo $mainArr; ?>">
  <div class="container-fluid" style="padding-top:15px">
    <div class="row">
      <div class="col-md-2">
        <div class="form-group">
          <a href="json-file.json" class="btn btn-info col-sm-12" class="form-control" target="_blank">View JSON</a>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <select id="user" class="form-control col-sm-10">
            <option value="">Select User</option>
            <?php foreach ($mainArr as $mainData) { ?>
              <option value="<?php echo $mainData['id']; ?>"><?php echo $mainData['name']; ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <select id="timeFrame"class="form-control col-sm-10">
            <option value="">Select Time Frame</option>
            <?php foreach ($dateFrameArr as $dateFrame) { ?>
              <option value="<?php echo $dateFrame ?>"><?php echo $dateFrame; ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <button type="submit" id="fetchChart" class="btn btn-success col-sm-10" class="form-control">Submit</button>
        </div>
        <span class="error-msg col-sm-8" style="color: red; display: none">Sorry! No data available for selected time frame.</span>
        <span class="error-message col-sm-8" style="color: red; display: none">All options are mandatory to select.</span>
      </div>
      <div class="col-md-4">
        <table class="table table-striped" style="display:none">
          <tbody>
            <tr>
              <td>Name</td>
              <td id="user_name"></td>
            </tr>
            <tr>
              <td>Department</td>
              <td id="user_department"></td>
            </tr>
            <tr>
              <td>Time Frame</td>
              <td id="user_timeframe"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div id="canvasChart" style="display: none">
      <canvas id="myChart"></canvas>
    </div>
  </div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
<script>
  $("#fetchChart").on('click', function() {
    if($('#user option:selected').val() == "" || $('#timeFrame option:selected').val() == "") {
      $(".error-message").css('display', 'block');
    }
    else {
      $(".error-message").css('display', 'none');
      userId = $('#user option:selected').val();
      dates = [];
      logData = [];
      logsData = [];
      logS1CheckIn = []; logS1CheckOut = []; logS2CheckIn = []; logS2CheckOut = [];
      logS3CheckIn = []; logS3CheckOut = [];
      minS1LogIn = []; minS1LogOut = []; minS2LogIn = []; minS2LogOut = []; minS3LogIn = [];
      minS3LogOut = [];
      checkInS1TimeLapse = []; checkOutS1TimeLapse = []; checkInS2TimeLapse = [];checkOutS2TimeLapse = []; checkInS3TimeLapse = []; checkOutS3TimeLapse = [];
      am = "a.m.";
      pm = "p.m.";

      maindata = <?php echo json_encode($mainArr); ?>;
      $.each(maindata, function(key, value) {
        if($('#user option:selected').val() == value['id']) {
          $.each(value, function(index, dataSet) {
            if(dataSet['date-frame'] == $('#timeFrame option:selected').val()) {
              $("#user_name").html("<strong>" + value['name'] + "</strong>");
              $("#user_department").html("<strong>" + value['department'] + "</strong>");
              $("#user_timeframe").html("<strong>" + $('#timeFrame option:selected').val() + "</strong>");
              $("table").css('display', 'block');
              $(".error-msg").css('display', 'none');
              $("#canvasChart").css('display', 'block');
              $.each(dataSet['week'], function(k, week) {
                dates.push(week['date']);
                $.each(week, function(w, log) {
                  if(w == 'logs') {
                    logsS1Checkin = [log[1]['check-in']]; logsS1Checkout = [log[1]['check-out']];
                    logsS2Checkin = [log[2]['check-in']]; logsS2Checkout = [log[2]['check-out']];
                    logsS3Checkin = [log[3]['check-in']]; logsS3Checkout = [log[3]['check-out']];

                    logS1CheckIn.push(logsS1Checkin); logS1CheckOut.push(logsS1Checkout);
                    logS2CheckIn.push(logsS2Checkin); logS2CheckOut.push(logsS2Checkout);
                    logS3CheckIn.push(logsS3Checkin); logS3CheckOut.push(logsS3Checkout);
                  }
                });
              });
            } else {
              $(".error-msg").css('display', 'block');
              $("#canvasChart").css('display', 'none');
            }
          });
        }
      });

      $.each(logS1CheckIn, function(li, data) {
        for(i=0; i<data.length; i++) {
          dataValue = dataCode(data);
          if(dataValue[i] != "0") {
            preString = dataValue[i].substr(0, 2);
            sufString = dataValue[i].substr(2, 3);
            if(preString > 12) {
              time = (preString - 12).toString();
              if(time.length == 1) {
                time = "0" + time;
              }
              dataValue[i] = time + "" + sufString;
              checkInS1TimeLapse.push(pm);
            } else {
              checkInS1TimeLapse.push(am);
            }
          } else {
            checkInS1TimeLapse.push("");
          }
        }
        $.merge(minS1LogIn, dataValue);
      });
      $.each(logS1CheckOut, function(lo, data) {
        for(i=0; i<data.length; i++) {
          dataValue = dataCode(data);
          if(dataValue[i] != "0") {
            preString = dataValue[i].substr(0, 2);
            sufString = dataValue[i].substr(2, 3);
            if(preString > 12) {
              time = (preString - 12).toString();
              if(time.length == 1) {
                time = "0" + time;
              }
              dataValue[i] = time + "" + sufString;
              checkOutS1TimeLapse.push(pm);
            } else {
              checkOutS1TimeLapse.push(am);
            }
          } else {
            checkOutS1TimeLapse.push("");
          }
        }
        $.merge(minS1LogOut, dataValue);
      });
      $.each(logS2CheckIn, function(li, data) {
        for(i=0; i<data.length; i++) {
          dataValue = dataCode(data);
          if(dataValue[i] != "0") {
            preString = dataValue[i].substr(0, 2);
            sufString = dataValue[i].substr(2, 3);
            if(preString > 12) {
              time = (preString - 12).toString();
              if(time.length == 1) {
                time = "0" + time;
              }
              dataValue[i] = time + "" + sufString;
              checkInS2TimeLapse.push(pm);
            } else {
              checkInS2TimeLapse.push(am);
            }
          } else {
            checkInS2TimeLapse.push("");
          }
        }
        $.merge(minS2LogIn, dataValue);
      });
      $.each(logS2CheckOut, function(lo, data) {
        for(i=0; i<data.length; i++) {
          dataValue = dataCode(data);
          if(dataValue[i] != "0") {
            preString = dataValue[i].substr(0, 2);
            sufString = dataValue[i].substr(2, 3);
            if(preString > 12) {
              time = (preString - 12).toString();
              if(time.length == 1) {
                time = "0" + time;
              }
              dataValue[i] = time + "" + sufString;
              checkOutS2TimeLapse.push(pm);
            } else {
              checkOutS2TimeLapse.push(am);
            }
          } else {
            checkOutS2TimeLapse.push("");
          }
        }
        $.merge(minS2LogOut, dataValue);
      });
      $.each(logS3CheckIn, function(li, data) {
        for(i=0; i<data.length; i++) {
          dataValue = dataCode(data);
          if(dataValue[i] != "0") {
            preString = dataValue[i].substr(0, 2);
            sufString = dataValue[i].substr(2, 3);
            if(preString > 12) {
              time = (preString - 12).toString();
              if(time.length == 1) {
                time = "0" + time;
              }
              dataValue[i] = time + "" + sufString;
              checkInS3TimeLapse.push(pm);
            } else {
              checkInS3TimeLapse.push(am);
            }
          } else {
            checkInS3TimeLapse.push("");
          }
        }
        $.merge(minS3LogIn, dataValue);
      });
      $.each(logS3CheckOut, function(lo, data) {
        for(i=0; i<data.length; i++) {
          dataValue = dataCode(data);
          if(dataValue[i] != "0") {
            preString = dataValue[i].substr(0, 2);
            sufString = dataValue[i].substr(2, 3);
            if(preString > 12) {
              time = (preString - 12).toString();
              if(time.length == 1) {
                time = "0" + time;
              }
              dataValue[i] = time + "" + sufString;
              checkOutS3TimeLapse.push(pm);
            } else {
              checkOutS3TimeLapse.push(am);
            }
          } else {
            checkOutS3TimeLapse.push("");
          }
        }
        $.merge(minS3LogOut, dataValue);
      });
      logsData.push(minS1LogIn); logsData.push(minS1LogOut);
      logsData.push(minS2LogIn); logsData.push(minS2LogOut);
      logsData.push(minS3LogIn); logsData.push(minS3LogOut);
      logsData.push(checkInS1TimeLapse); logsData.push(checkOutS1TimeLapse);
      logsData.push(checkInS2TimeLapse); logsData.push(checkOutS2TimeLapse);
      logsData.push(checkInS3TimeLapse); logsData.push(checkOutS3TimeLapse);
      lineChart(dates, logsData);
    }
  });

  /* Line Chart */
  function lineChart(xAxis, logsData) {
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
    plugins: [{ 
      beforeInit: function(chart, options) {
        chart.legend.afterFit = function() {
          this.height = this.height + 20;
        };
      }
    }],
    responsive: true,
    type: 'line',
      data: {
        labels: xAxis,
        datasets: [{
          label: 'Section 1 - Check In',
          data: logsData[0],
          fill: false,
          borderRadius: 1,
          borderColor: '#00bfff',
          set: logsData[6]
        },
        {
          label: 'Section 1 - Check Out',
          data: logsData[1],
          fill: false,
          borderRadius: 1,
          borderColor: '#006080',
          set: logsData[7]
        }, {
          label: 'Section 2 - Check In',
          data: logsData[2],
          fill: false,
          borderRadius: 1,
          borderColor: '#ff0055',
          set: logsData[8]
        },
        {
          label: 'Section 2 - Check Out',
          data: logsData[3],
          fill: false,
          borderRadius: 1,
          borderColor: '#80002a',
          set: logsData[9]
        }, {
          label: 'Section 3 - Check In',
          data: logsData[4],
          fill: false,
          borderRadius: 1,
          borderColor: '#00ff55',
          set: logsData[10]
        },
        {
          label: 'Section 3 - Check Out',
          data: logsData[5],
          fill: false,
          borderRadius: 1,
          borderColor: '#00802b',
          set: logsData[11]
        }]
      },
      options: {
        tooltips: {
          mode: 'point',
          callbacks: {
            label: function(tooltipItem, data) {
              if(tooltipItem.datasetIndex == 0) {
                var value = data.datasets[0].data[tooltipItem.index];
                timeLapse = data.datasets[0].set[tooltipItem.index];
                value = value.toString();
                value = value.split(/(?=(?:..)*$)/);
                value = value.join(':');
                result = data.datasets[0].label + ": " + value + "" + timeLapse;
              } else if(tooltipItem.datasetIndex == 1) {
                var value = data.datasets[1].data[tooltipItem.index];
                timeLapse = data.datasets[1].set[tooltipItem.index];
                value = value.toString();
                value = value.split(/(?=(?:..)*$)/);
                value = value.join(':');
                result = data.datasets[1].label + ": " + value + "" + timeLapse;
              } else if(tooltipItem.datasetIndex == 2) {
                var value = data.datasets[2].data[tooltipItem.index];
                timeLapse = data.datasets[2].set[tooltipItem.index];
                value = value.toString();
                value = value.split(/(?=(?:..)*$)/);
                value = value.join(':');
                result = data.datasets[2].label + ": " + value + "" + timeLapse;
              } else if(tooltipItem.datasetIndex == 3) {
                var value = data.datasets[3].data[tooltipItem.index];
                timeLapse = data.datasets[3].set[tooltipItem.index];
                value = value.toString();
                value = value.split(/(?=(?:..)*$)/);
                value = value.join(':');
                result = data.datasets[3].label + ": " + value + "" + timeLapse;
              } else if(tooltipItem.datasetIndex == 4) {
                var value = data.datasets[4].data[tooltipItem.index];
                timeLapse = data.datasets[4].set[tooltipItem.index];
                value = value.toString();
                value = value.split(/(?=(?:..)*$)/);
                value = value.join(':');
                result = data.datasets[4].label + ": " + value + "" + timeLapse;
              } else if(tooltipItem.datasetIndex == 5) {
                var value = data.datasets[5].data[tooltipItem.index];
                timeLapse = data.datasets[5].set[tooltipItem.index];
                value = value.toString();
                value = value.split(/(?=(?:..)*$)/);
                value = value.join(':');
                result = data.datasets[5].label + ": " + value + "" + timeLapse;
              }
              return result;
            }
          },
          titleFontSize: 16,
          bodyFontSize: 16,
          titleFontFamily: "Arial",
          bodyFontFamily: "Arial"
        },
        title: {
          display: true,
          text: 'Attendance Log',
          fontSize: 18,
        },
        scales: {
          yAxes: [{
            ticks: {
              fontSize: 12,
              beginAtZero: true,
              userCallback: function(value, index, values) {
                value = value.toString();
                value = value.split(/(?=(?:..)*$)/);
                value = value.join(':');
                return value;
              }  
            },
            scaleLabel: {
              display: true,
              labelString: 'Time',
              fontSize: 12
            },
            gridLines: {
              color: "#f5f5f0",
            } 
          }],
          xAxes: [{
            ticks: {
              callback: function(e) {
                var xAxisLabel = e.split(" ");
                if(xAxisLabel[1].length == 1) {
                  result = e + "0";
                } else {
                  result = e;
                }
                return result;  
              },
              fontSize: 12,
            },
            scaleLabel: {
              display: true,
              labelString: 'Date',
              fontSize: 12
            },
            gridLines: {
              color: "#f5f5f0",
            }   
          }]
        },
        legend: {
          labels: {
            fontSize: 12
          }
        }
      }
    });
  }

  function dataCode(data) {
    data[i] = data[i].replace("Rest ", "0");
    data[i] = data[i].replace(" ", "0");
    data[i] = data[i].replace("", "0");
    data[i] = data[i].replace("00", "0");
    data[i] = data[i].replace("01", "1");
    data[i] = data[i].replace("02", "2");
    data[i] = data[i].replace(":", "");

    return data;
  }
</script>
</body>
</html>