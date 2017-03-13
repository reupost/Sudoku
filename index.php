<?php

include_once('engine.php');

function WhatToDo() {
  $puzzle = array();
  
   if (isset($_POST['puzzle_data'])) {
     $puzzle = GetPuzzleFromData($_POST['puzzle_data']);
     Solve($puzzle);
     DisplaySolution();
   } else {
     DisplayEntryForm();
   }
   return;
}

function GetPuzzleFromData($puzzle_data) {
  $puz = array();
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      $puz[$y][$x] = ($puzzle_data[$y][$x] != ''? intval($puzzle_data[$y][$x]) : 0);
      
    }
  }
  return $puz;
}

function GetCellStyleHTML($y, $x, $ply = 0) {
  $block_style = '2px solid black;';
  $cell_style = '1px solid #CCCCCC;';
  $color = '#FFFFFF';
  if ($ply != 0) {
    $color = dechex(255 - $ply*10);  
    $color = (strlen($color) < 2? "0" : "") . $color;
    $color = '#' . $color . $color . $color;
  }
  $html = '';
  $border_top = false;
  $border_bottom = false;
  $border_left = false;
  $border_right = false;
  if (!($y % 3)) { //top row in block
      $border_top = true;
  } elseif ($y == 8) {
     $border_bottom = true;
  }
  if (!($x % 3)) { //left column in block
      $border_left = true;
  } elseif ($x == 8) {
     $border_right = true;
  }

  $html = "<td style='";
  $html .= "border-left:" . ($border_left? $block_style : $cell_style);
  $html .= "border-right:" . ($border_right? $block_style : $cell_style);
  $html .= "border-top:" . ($border_top? $block_style : $cell_style);
  $html .= "border-bottom:" . ($border_bottom? $block_style : $cell_style);
  
  $html .= "background-color:" . $color;
  $html .= "'>";  
  return $html;
}

function DisplayEntryForm() {  
  echo "<form action='index.php' method='post'>";
  echo "<table>";
  for ($y = 0; $y < 9; $y++) {    
    echo "<tr>";
    for ($x = 0; $x < 9; $x++) {
      echo GetCellStyleHTML($y, $x); 
      echo "<input type='textbox' name='puzzle_data[" . $y . "][] value=''/> </td>";
    }
    echo "</tr>";
  }
  echo "</table>";
  echo "<div id='go'><input type='submit' value='Solve'></div>";
  echo "</form>";
}

function DisplaySolution() {
  global $board;
  global $init_board;
  global $ply_set;
  global $ply;
  global $instructions;
  global $start_time;
  global $end_time;
  
  $unsolved = 0;
  
  echo "<table>";
  for ($y = 0; $y < 9; $y++) {
    echo "<tr>";
    for ($x = 0; $x < 9; $x++) {
      if (!IsKnown($x,$y)) $unsolved++;
      echo GetCellStyleHTML($y, $x, $ply_set[$y][$x]*10);
      if ($init_board[$y][$x] > 0) {
        echo "<div class='init_cell'>" . $init_board[$y][$x] . "</div>";
      } else {
        if($board[$y][$x]) { echo $board[$y][$x]; } else { echo "&nbsp;"; }
      }
      echo "</td>";
    }
    echo "</tr>";
  }
  echo "</table>";
  echo "<div id='results'>Iterations required: <b>" . $ply . "</b> (" . $instructions . " instructions in "  . number_format($end_time - $start_time,3,'.',''). "s)<br/>";
  echo ($unsolved? "Unsolved: " . $unsolved . " cells remain." : "OK") . "</div>";
  return;
}

?>
<html>
   <head>
      <link rel="stylesheet" type="text/css" href="sudoku.css">
   </head>
   <body>
      <?php WhatToDo() ?>
   </body>
</html>