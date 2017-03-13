<?php

$board = array();
$init_board = array();
$possible = array();
$ply_set = array();
$instructions = 0;
$start_time = 0.0;
$end_time = 0.0;

$ply = 0;

function arrayCopy( array $array ) {
  $result = array();
  foreach( $array as $key => $val ) {
      if( is_array( $val ) ) {
          $result[$key] = arrayCopy( $val );
      } elseif ( is_object( $val ) ) {
          $result[$key] = clone $val;
      } else {
          $result[$key] = $val;
      }
  }
  return $result;
}

function IsValidInRow($row, $val) {
  global $board;
  $valid = true;
  for ($i = 0; $i < 9; $i++) {
    if ($board[$row][$i] == $val) $valid = false;
  }
  return $valid;
}

function IsValidInCol($col, $val) {
  global $board;
  $valid = true;
  for ($i = 0; $i < 9; $i++) {
    if ($board[$i][$col] == $val) $valid = false;
  }
  return $valid;
}

function IsValidInBlock($block_x, $block_y, $val) {
  global $board;
  $valid = true;
  for ($y = $block_y*3; $y < $block_y*3+3; $y++) {
    for ($x = $block_x*3; $x < $block_x*3+3; $x++) {
      if ($board[$y][$x] == $val) $valid = false;
    }
  }
  return $valid;
}

function OptionsForValInCol($col, $val) {
  global $possible;
  $cells = array();
  
  for ($y = 0; $y < 9; $y++) {
    if (in_array($val, $possible[$y][$col])) $cells[] = array($y,$col);
  }
  return $cells;
}

function OptionsForValInRow($row, $val) {
  global $possible;
  $cells = array();
  
  for ($x = 0; $x < 9; $x++) {
    if (in_array($val, $possible[$row][$x])) $cells[] = array($row,$x);
  }
  return $cells;
}

function OptionsForValInBlock($block_x, $block_y, $val) {
  global $possible;
  $cells = array();
  
  for ($y = $block_y*3; $y < $block_y*3+3; $y++) {
    for ($x = $block_x*3; $x < $block_x*3+3; $x++) {
      if (in_array($val, $possible[$y][$x])) $cells[] = array($y,$x);
    }
  }
  return $cells;
}

function IsSetInRow($row, $val) {
  global $board;
  
  for ($y = 0; $y < 9; $y++) {
    if ($board[$y][$row] == $val) return true;
  }
  return false;
}

function IsSetInCol($col, $val) {
  global $board;
 
  for ($x = 0; $x < 9; $x++) {
    if ($board[$col][$x] == $val) return true;
  }
  return false;
}

function IsSetInBlock($block_x, $block_y, $val) {
  global $board;
  
  for ($y = $block_y*3; $y < $block_y*3+3; $y++) {
    for ($x = $block_x*3; $x < $block_x*3+3; $x++) {
      if ($board[$y][$x] == $val) return true;
    }
  }
  return false;
}

function IsKnown($x, $y) {
  global $board;
  if ($board[$y][$x] > 0) return true;
  return false;
}

function IsSolved() {
  global $board;
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      if (!$board[$y][$x]) return false;
    }
  }
  return true;
}

function Init($puzzle) {
  global $board;
  global $init_board;
  global $ply_set;
  
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      $board[$y][$x] = 0;
      $init_board[$y][$x] = 0;
      $ply_set[$y][$x] = 0;
    }
  }
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      $init_board[$y][$x] = $puzzle[$y][$x];
    }
  }
  //add any specific setup here
  //Guardian Difficult = 15 ply (now 1 with rule of K etc.)
  //iterations: 9364
  /* $init_board = array(
    array(0,0,0,0,0,0,1,3,0),
    array(0,0,0,4,0,2,0,0,6),
    array(0,0,0,0,8,0,0,0,0),
    array(0,2,0,0,0,0,0,1,9),
    array(0,0,7,0,0,0,2,0,0),
    array(0,9,0,0,0,0,6,8,0),
    array(6,0,0,0,9,1,0,0,3),
    array(4,0,0,6,0,7,0,0,2),
    array(0,3,0,8,0,0,5,6,0)
    ); */
  
  // http://www.fiendishsudoku.com/sudoku.html - does not solve - 40 cells remain - solved in 1 now
  //instructions: 13517
  /* $init_board = array(
    array(0,6,0,0,2,0,0,1,0),
    array(5,0,0,0,1,9,0,0,8),
    array(0,0,7,0,0,0,5,0,0),
    array(0,9,0,0,0,0,0,0,0),
    array(8,2,0,0,3,0,0,4,5),
    array(0,0,0,0,0,0,0,6,0),
    array(0,0,1,0,0,0,3,0,0),
    array(6,0,0,5,4,0,0,0,1),
    array(0,7,0,0,6,0,0,8,0)
    ); */
  
  // http://www.fiendishsudoku.com/sudoku.html - does not solve - 42 cells remain - now solves in 1
  /* $init_board = array(
    array(6,0,0,0,0,0,8,3,4),
    array(0,2,0,0,0,1,0,0,9),
    array(0,0,4,0,6,0,0,0,2),
    array(0,0,0,5,0,0,0,9,0),
    array(0,0,7,0,8,0,3,0,0),
    array(0,8,0,0,0,6,0,0,0),
    array(4,0,0,0,3,0,7,0,0),
    array(9,0,0,2,0,0,0,4,0),
    array(5,3,8,0,0,0,0,0,1)
    ); */
  // from fiendish sudoku - solves in 1 
  /* $init_board = array(
    array(0,6,0,0,0,0,0,8,0),
    array(5,0,9,0,0,0,2,0,6),
    array(0,2,0,9,0,6,0,4,0),
    array(0,0,4,0,8,0,6,0,0),
    array(0,0,0,4,0,7,0,0,0),
    array(0,0,3,0,5,0,8,0,0),
    array(0,4,0,3,0,2,0,9,0),
    array(9,0,2,0,0,0,7,0,3),
    array(0,8,0,0,0,0,0,6,0)
    ); */
  // from fiendish sudoku (only hard though) - solves in 2
  // instructions: 8883
  /*
   $init_board = array(
    array(0,5,0,0,0,0,0,2,0),
    array(4,0,7,0,0,0,6,0,8),
    array(0,2,0,3,0,1,0,7,0),
    array(0,0,2,0,1,0,9,0,0),
    array(0,0,0,8,0,7,0,0,0),
    array(0,0,1,0,3,0,8,0,0),
    array(0,1,0,2,0,9,0,5,0),
    array(2,0,4,0,0,0,1,0,6),
    array(0,7,0,0,0,0,0,8,0)
    ); */
  //19 feb - solves in 1
  /* $init_board = array(
    array(0,0,6,0,8,0,3,0,0),
    array(0,7,0,2,0,0,0,9,0),
    array(3,0,0,0,0,1,0,0,5),
    array(0,0,9,0,0,0,0,4,0),
    array(1,0,0,0,3,0,0,0,8),
    array(0,8,0,0,0,0,7,0,0),
    array(4,0,0,8,0,0,0,0,9),
    array(0,1,0,0,0,2,0,7,0),
    array(0,0,7,0,9,0,2,0,0)
    ); */
  //cannot be solved :(
  /* $init_board = array(
    array(6,0,0,0,0,8,1,0,0),
    array(0,8,0,1,0,9,0,0,0),
    array(0,0,7,0,6,0,0,0,0),
    array(0,1,0,0,9,0,6,0,0),
    array(0,0,2,0,0,0,0,0,3),
    array(0,0,0,3,0,0,0,5,0),
    array(0,7,0,0,1,0,8,0,0),
    array(5,0,0,0,0,4,0,2,0),
    array(0,0,0,0,0,0,0,0,4)
    ); */
   // 29 Mar solves in 1
    /* $init_board = array(
    array(0,0,6,0,1,0,0,4,0),
    array(5,0,4,0,0,7,0,0,0),
    array(0,0,0,0,0,0,0,7,5),
    array(0,7,0,0,3,0,0,0,0),
    array(3,0,0,2,0,9,0,0,8),
    array(0,0,0,0,8,0,0,5,0),
    array(4,2,0,0,0,0,0,0,0),
    array(0,0,0,4,0,0,3,0,7),
    array(0,6,0,0,2,0,9,0,0)
    ); */
  //28 Mar - solves in 1
  /* $init_board = array(
    array(0,0,6,0,5,0,4,0,0),
    array(0,0,0,7,0,9,0,0,0),
    array(7,0,0,0,1,0,0,0,8),
    array(0,5,0,0,3,0,0,8,0),
    array(9,0,4,5,0,1,6,0,2),
    array(0,6,0,0,2,0,0,9,0),
    array(6,0,0,0,9,0,0,0,5),
    array(0,0,0,2,0,7,0,0,0),
    array(0,0,3,0,4,0,1,0,0)
    ); */
   /* $init_board = array(
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0),
    array(0,0,0,0,0,0,0,0,0)
    );  */
  
  
  $board = arrayCopy($init_board);
  
  return;
}

function GetBlock($i) {
  if ($i < 3) return 0;
  if ($i < 6) return 1;
  if ($i < 9) return 2;
  return -1;
}

function RemoveValFromArray($arr, $val) {
  $newarr = array();
  for ($i = 0; $i < sizeof($arr); $i++) {
    if ($arr[$i] != $val) {
      $newarr[] = $arr[$i];
    }
  }
  return $newarr;
}

function RemoveValsFromArray($arr, $arr_vals) {
  $arrnew = $arr;
  foreach( $arr_vals as $val) {
    $arrnew = RemoveValFromArray($arrnew, $val);
  }
  return $arrnew;
}

function SetPossible() {
  global $possible;
  global $instructions;
  
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      $instructions++; $possible[$y][$x] = array();
      if (!IsKnown($x, $y)) {
        $instructions++; $possible_xy = array();
        for ($val = 1; $val < 10; $val++) {
          $ok = true;
          $instructions++; if (!IsValidInRow($y, $val)) $ok = false;
          $instructions++; if (!IsValidInCol($x, $val)) $ok = false;
          $instructions++; if (!IsValidInBlock(GetBlock($x), GetBlock($y), $val)) $ok = false;
          $instructions++; if ($ok) $possible_xy[] = $val;
        }
        $possible[$y][$x] = $possible_xy;
      }
    }
  }
  return;
}

function CountSamePossibleAs_Row($x_item, $y) {
  global $possible;
  
  $matching = 0;
  for ($x = 0; $x < 9; $x++) {
    if (!IsKnown($x,$y)) {
      if ($possible[$y][$x] == $possible[$y][$x_item]) $matching++;
    }
  }
  return $matching;
}

function RemoveSamePossibleAs_Row($x_item, $y) {
  global $possible;
  $pruned = 0;
  
  for ($x = 0; $x < 9; $x++) {
    if (!IsKnown($x,$y)) {
      if ($possible[$y][$x] != $possible[$y][$x_item]) {        
        //sieve out any of the elements that are in $possible[$row][$col]
        $newarr = array();
        for ($i = 0; $i < sizeof($possible[$y][$x]); $i++) {
          if (!in_array($possible[$y][$x][$i], $possible[$y][$x_item])) {
            $newarr[] = $possible[$y][$x][$i];
          } else {
            $pruned++;
          }
        }
        $possible[$y][$x] = $newarr;
      }
    }
  }
  return $pruned;
}

function CountSamePossibleAs_Col($x, $y_item) {
  global $possible;
  
  $matching = 0;
  for ($y = 0; $y < 9; $y++) {
    if (!IsKnown($x,$y)) {
      if ($possible[$y][$x] == $possible[$y_item][$x]) $matching++;
    }
  }
  return $matching;
}

function RemoveSamePossibleAs_Col($x, $y_item) {
  global $possible;
  $pruned = 0;
  
  for ($y = 0; $y < 9; $y++) {
    if (!IsKnown($x,$y)) {
      if ($possible[$y][$x] != $possible[$y_item][$x]) {      
        //sieve out any of the elements that are in $possible[$row][$col]
        $newarr = array();
        for ($i = 0; $i < sizeof($possible[$y][$x]); $i++) {
          if (!in_array($possible[$y][$x][$i], $possible[$y_item][$x])) {
            $newarr[] = $possible[$y][$x][$i];
          } else {
            $pruned++;
          }
        }
        $possible[$y][$x] = $newarr;
      }
    }
  }
  return $pruned;
}

function CountSamePossibleAs_Block($x_item, $y_item) {
  global $possible;
  
  $matching = 0;
  $block_y = GetBlock($y_item);
  $block_x = GetBlock($x_item);
  
  for ($y = $block_y*3; $y < $block_y*3+3; $y++) {
    for ($x = $block_x*3; $x < $block_x*3+3; $x++) {
      if (!IsKnown($x,$y)) {
        if ($possible[$y][$x] == $possible[$y_item][$x_item]) $matching++;
      }
    }
  }
  return $matching;
}

function RemoveSamePossibleAs_Block($x_item, $y_item) {
  global $possible;
  $pruned = 0;
  
  $block_y = GetBlock($y_item);
  $block_x = GetBlock($x_item);
  
  for ($y = $block_y*3; $y < $block_y*3+3; $y++) {
    for ($x = $block_x*3; $x < $block_x*3+3; $x++) {
      if (!IsKnown($x,$y)) {    
        if ($possible[$y][$x] != $possible[$y_item][$x_item]) {        
          //sieve out any of the elements that are in $possible[$row][$col]
          $newarr = array();
          for ($i = 0; $i < sizeof($possible[$y][$x]); $i++) {
            if (!in_array($possible[$y][$x][$i], $possible[$y_item][$x_item])) {
              $newarr[] = $possible[$y][$x][$i];
            } else {
              $pruned++;
            }
          }
          $possible[$y][$x] = $newarr;
        }
      }
    }
  }
  return $pruned;
}

function IsArraySubsetOf($arr_to_check, $arr_of) {
  $is_subset = true;
  for( $i = 0; $i < sizeof($arr_to_check); $i++ ) {
     if( ! in_array($arr_to_check[$i], $arr_of ) ) $is_subset = false;
  }
  return $is_subset;
}

function GetCellsWithSubsetOfPossibilities_Row($row, $arr_possibilities) {
  global $possible;
  $cells = array();
  for( $x = 0; $x < 9; $x++ ) {
    if( ! IsKnown( $x, $row ) ) {
      $is_subset = IsArraySubsetOf( $possible[$row][$x], $arr_possibilities );
      if( $is_subset ) {
        $cells[] = array($row, $x);
      }
    }
  }
  return $cells;
}
function GetCellsWithSubsetOfPossibilities_Col($col, $arr_possibilities) {
  global $possible;
  $cells = array();
  for( $y = 0; $y < 9; $y++ ) {
    if( ! IsKnown( $col, $y ) ) {
      $is_subset = IsArraySubsetOf( $possible[$y][$col], $arr_possibilities );
      if( $is_subset ) {
        $cells[] = array($y, $col);
      }
    }
  }
  return $cells;
}
function GetCellsWithSubsetOfPossibilities_Block($block_x, $block_y, $arr_possibilities) {
  global $possible;
  $cells = array();
  
  for ($y = $block_y*3; $y < $block_y*3+3; $y++) {
    for ($x = $block_x*3; $x < $block_x*3+3; $x++) {
      if (!IsKnown($x,$y)) {  
        $is_subset = IsArraySubsetOf( $possible[$y][$x], $arr_possibilities );
        if( $is_subset ) {
          $cells[] = array($y, $x);
        }
      }
    }
  }
  return $cells;
}

function PrunePossibleRuleOfK() {
  //e.g. if n cells in row/col/block have the same n possibilities then can remove those n possibilities from any other cells in row/col/block
  global $instructions;
  
  global $possible;
  $numopts = 0;
  $matching = 0;
  $pruned = 0;
  
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      if (!IsKnown($x,$y)) {
        $numopts = sizeof($possible[$y][$x]); 
        $instructions++; $matching = CountSamePossibleAs_Row($x, $y);          
        if ($matching == $numopts) {
          //if ($y == 7 && $x == 6) { echo "<pre>"; print_r($possible); echo "</pre>"; }
          $instructions++; $pruned += RemoveSamePossibleAs_Row($x, $y);
          //if ($y == 7 && $x == 6) { echo "<pre>"; print_r($possible); echo "</pre>"; }
          //if ($y == 7 && $x == 6) exit;
        }
        $instructions++; $matching = CountSamePossibleAs_Col($x, $y);
        if ($matching == $numopts) { $instructions++; $pruned += RemoveSamePossibleAs_Col($x, $y); }
        $instructions++; $matching = CountSamePossibleAs_Block($x, $y);
        if ($matching == $numopts) { $instructions++; $pruned += RemoveSamePossibleAs_Block($x, $y); }
      }
    }
  }
  return $pruned;
}

//TODO: add naked quads function as well
function PrunePossibleNakedTriples() {
  global $instructions;
  
  global $possible;
  $numopts = 0;
  $matching = 0;
  $pruned = 0;
  
  //e.g. if row/col/block cells have possibilities as follows (a,b) (b,c) (a,b,c) then a,b,c must be in those 3 cells and no others
  //find cell with 3 opts
  //look at other cells to find any which have a subset of those 3 opts and no other opts
  //if can find 2 others, then we have a naked triple
  for( $y = 0; $y < 9; $y++ ) {
    for( $x = 0; $x < 9; $x++ ) {
      if( !IsKnown($x,$y) ) {
        $numopts = sizeof( $possible[$y][$x] ); 
        if( $numopts == 3 ) {
          $arr_possibilities = $possible[$y][$x];
          $instructions++; $subset_cells = GetCellsWithSubsetOfPossibilities_Row($y, $arr_possibilities);
          if( sizeof( $subset_cells ) == 3 ) {
            //will include original cell, so need 3 as 'subset'
            //remove options from any other cells in row
            for( $xx = 0; $xx < 9; $xx++ ) {
              if( !IsKnown($xx,$y) ) {
                $should_prune = true;
                foreach( $subset_cells as $cell ) {
                  if( $cell[1] == $xx ) $should_prune = false; //one of our matching subset cells, so leave it
                }
                if( $should_prune ) {
                  $opts = sizeof($possible[$y][$xx]);
                  $instructions++; $possible[$y][$xx] = RemoveValsFromArray( $possible[$y][$xx], $arr_possibilities );
                  $opts2 = sizeof($possible[$y][$xx]);
                  $pruned += ($opts - $opts2);
                }
              }
            }
          }
          $subset_cells = GetCellsWithSubsetOfPossibilities_Col($x, $arr_possibilities);
          if( sizeof( $subset_cells ) == 3 ) {
            //will include original cell, so need 3 as 'subset'
            //remove options from any other cells in col
            for( $yy = 0; $yy < 9; $yy++ ) {
              if( ! IsKnown( $x, $yy ) ) {
                $should_prune = true;
                foreach( $subset_cells as $cell ) {
                  if( $cell[0] == $yy ) $should_prune = false; //one of our matching subset cells, so leave it
                }
                if( $should_prune ) {
                  $opts = sizeof($possible[$yy][$x]);
                  $instructions++; $possible[$yy][$x] = RemoveValsFromArray( $possible[$yy][$x], $arr_possibilities );
                  $opts2 = sizeof($possible[$yy][$x]);
                  $pruned += ($opts - $opts2);
                }
              }
            }
          }
          $block_y = GetBlock($y);
          $block_x = GetBlock($x);
          $subset_cells = GetCellsWithSubsetOfPossibilities_Block($block_x, $block_y, $arr_possibilities);
          if( sizeof( $subset_cells ) == 3 ) {
            //will include original cell, so need 3 as 'subset'
            //remove options from any other cells in block
            for ($yy = $block_y*3; $yy < $block_y*3+3; $yy++) {
              for ($xx = $block_x*3; $xx < $block_x*3+3; $xx++) {   
                if( ! IsKnown( $xx, $yy ) ) {
                  $should_prune = true;
                  foreach( $subset_cells as $cell ) {
                    if( $cell[0] == $yy && $cell[1] == $xx ) $should_prune = false; //one of our matching subset cells, so leave it
                  }
                  if( $should_prune ) {
                    $opts = sizeof($possible[$yy][$xx]);
                    $instructions++; $possible[$yy][$xx] = RemoveValsFromArray( $possible[$yy][$xx], $arr_possibilities );
                    $opts2 = sizeof($possible[$yy][$xx]);
                    $pruned += ($opts - $opts2);
                  }
                }
              }
            }
          }
        }
      }
    }
  }
  return $pruned;
}

function PrunePossibleExcluder() {
  //if the only valid cells for a particular value [x] in a row/col are all found in a single block, then
  //can exclude that value [x] from the possibilities for other cells in the block (since a value can only occur once in a block)
  //if the only valide cells for a particular value [x] in a block are all found in a single row/col, then
  //can exclude that value [x] from the possibilities for the other cells in the row/col (since value can only occur once in a row/col)
  global $instructions;
  global $possible;
  //global $board;
  
  $pruned = 0;
  
  //rows
  for( $y = 0; $y < 9; $y++ ) { //for each row
    for( $val = 1; $val < 10; $val++ ) { //for options 1..9
      if( IsValidInRow( $y, $val) ) { //i.e. val not already set in row
        $instructions++; $where_possible = OptionsForValInRow($y, $val);
        $block_row = GetBlock($y);
        $block_col = -1;
        //smallest and biggest x will be first and last array elements respectively
        if( $where_possible[sizeof($where_possible)-1][1] < 3 ) { 
          //all in left-most block
          $block_col = 0;
        } elseif( $where_possible[0][1] > 5 ) { 
          // all in right-most block
          $block_col = 2;
        } elseif( $where_possible[0][1] > 2 && $where_possible[sizeof($where_possible)-1][1] < 6 ) {
          //all in middle block
          $block_col = 1;
        }
        if( $block_row >= 0 && $block_col >= 0 ) {
          //remove other options for this value from the block
          for ($yy = $block_row*3; $yy < $block_row*3+3; $yy++) {
            for ($xx = $block_col*3; $xx < $block_col*3+3; $xx++) {
              if (!IsKnown($xx,$yy)) { 
                if ($yy != $y ) {
                  $instructions++; $newpos = RemoveValFromArray($possible[$yy][$xx],$val);
                  $pruned += (sizeof($possible[$yy][$xx]) - sizeof($newpos));
                  $possible[$yy][$xx] = $newpos;
                }
              }
            }
          }
        }
      }
    }
  }
  
  //cols
  for( $x = 0; $x < 9; $x++ ) { //for each row
    for( $val = 1; $val < 10; $val++ ) { //for options 1..9
      if( IsValidInCol( $x, $val) ) { //i.e. val not already set in row
        $instructions++; $where_possible = OptionsForValInCol($x, $val);
        $block_col = GetBlock($x);
        $block_row = -1;
        //smallest and biggest y will be first and last array elements respectively
        if( $where_possible[sizeof($where_possible)-1][0] < 3 ) { 
          //all in top block
          $block_row = 0;
        } elseif( $where_possible[0][0] > 5 ) { 
          // all in bottom block
          $block_row = 2;
        } elseif( $where_possible[0][0] > 2 && $where_possible[sizeof($where_possible)-1][0] < 6 ) {
          //all in middle block
          $block_row = 1;
        }
        if( $block_row >= 0 && $block_col >= 0 ) {
          //remove other options for this value from the block
          for ($yy = $block_row*3; $yy < $block_row*3+3; $yy++) {
            for ($xx = $block_col*3; $xx < $block_col*3+3; $xx++) {
              if (!IsKnown($xx,$yy)) { 
                if ($xx != $x ) {
                  $instructions++; $newpos = RemoveValFromArray($possible[$yy][$xx],$val);
                  $pruned += (sizeof($possible[$yy][$xx]) - sizeof($newpos));
                  $possible[$yy][$xx] = $newpos;
                }
              }
            }
          }
        }
      }
    }
  }
  
  //blocks
  for( $by = 0; $by < 3; $by++ ) { //for each row
    for( $bx = 0; $bx < 3; $bx++ ) { //for each col
      for( $val = 1; $val < 10; $val++ ) { //for options 1..9
        if( IsValidInBlock( $bx, $by, $val) ) { //i.e. val not already set in block
          $instructions++; $where_possible = OptionsForValInBlock($bx, $by, $val);
          $x1 = 10; $x2 = -1;
          $y1 = 10; $y2 = -1;
          //get bounds of where_possible min/max in each dimension
          for( $i = 0; $i < sizeof($where_possible); $i++ ) {
            if ($where_possible[$i][0] < $y1) $y1 = $where_possible[$i][0];
            if ($where_possible[$i][0] > $y2) $y2 = $where_possible[$i][0];
            if ($where_possible[$i][1] < $x1) $x1 = $where_possible[$i][1];
            if ($where_possible[$i][1] > $x2) $x2 = $where_possible[$i][1];
          }
          //see if options are constrained to any row/col
          if ($y1 == $y2) {
            //one row affected
            for ($x = 0; $x < 9; $x++) {
              if (!IsKnown($x,$y1)) { 
                if (GetBlock($x) != $bx ) {
                  $instructions++; $newpos = RemoveValFromArray($possible[$y1][$x],$val);
                  $pruned += (sizeof($possible[$y1][$x]) - sizeof($newpos));
                  $possible[$y1][$x] = $newpos;
                }
              }
            }
          }
          if ($x1 == $x2) {
            //one col affected
            for ($y = 0; $y < 9; $y++) {
              if (!IsKnown($x1,$y)) { 
                if (GetBlock($y) != $by ) {
                  $instructions++; $newpos = RemoveValFromArray($possible[$y][$x1],$val);
                  $pruned += (sizeof($possible[$y][$x1]) - sizeof($newpos));
                  $possible[$y][$x1] = $newpos;
                }
              }
            }
          }
        }
      }
    }
  }
  
  return $pruned;
}

function PrunePossibleXwing() {
  //When there are 
  //only two possible cells for a value in each of two different rows,
  //and these candidates lie also in the same columns,
  //then all other candidates for this value in the columns can be eliminated.
  //The reverse is also true for 2 columns with 2 common rows.
  //  . . . Ab. . . Ac. .  
  //  . . . x . . . y . . 
  //  . . . Ad. . . Ae. . 
  //  . . . z . . . w . .
  // [A] is only possible in [Ab] or [Ac] and also only possible in [Ad] or [Ae], 
  // so [A] must be in columns 7 and 7
  // so remove [A] from possibilities in x,y,z,w (other cells in column 3 and 7)
  global $instructions;
  global $possible;
  //global $board;
  
  $pruned = 0;
  
  //get all paired options in each row
  $valuepairedinrow = array();
  for( $val = 1; $val < 10; $val++ ) {
    $valuepairedinrow[$val] = array();
    for( $row = 0; $row < 9; $row++ ) {
      $instructions++; $opts = OptionsForValInRow( $row, $val );
      if( count( $opts ) == 2 ) {
        $valuepairedinrow[$val][$row] = $opts; //opts is array of (row,col) pairs
      }
    }
  }
  //echo "<pre>"; print_r($valuepairedinrow); echo "</pre>"; exit;
  //so now valpairedinrow for example above would be 
  //[A][0] = (0,3),(0,7)
  //[A][2] = (2,3),(2,7)
  
  //now see if there are two rows with the same paired value, and tally the columns with the values  
  for( $val = 1; $val < 10; $val++ ) {
    if( count( $valuepairedinrow[$val] ) >= 2 ) { //two (or more) rows have paired options for this value
      foreach( $valuepairedinrow[$val] as $row => $cells ) {
        foreach( $valuepairedinrow[$val] as $row2 => $cells2 ) {
          if( $row != $row2 ) { //then compare these two            
            $instructions++; 
            $matched_cols = array();
            foreach( $cells as $cell ) {
              foreach( $cells2 as $cell2 ) {
                if( $cell[1] == $cell2[1] ) $matched_cols[] = $cell[1];                
              }
            }
            if( sizeof( $matched_cols ) == 2 ) { //ok, these two rows are paired for this value in the set of 2 columns
              //echo $val . ": " . $row . " + " . $row2; print_r($matched_cols); exit;
              for( $rw = 0; $rw < 9; $rw++ ) {
                if( $rw != $row && $rw != $row2 ) { //don't remove from x-wing rows, obviously
                  for( $icol = 0; $icol < 2; $icol++ ) { //for each of the two columns
                    if( ! IsKnown( $matched_cols[$icol], $rw ) ) {
                      $instructions++; $options = sizeof( $possible[$rw][$matched_cols[$icol]] );
                      $possible[$rw][$matched_cols[$icol]] = RemoveValFromArray( $possible[$rw][$matched_cols[$icol]], $val );
                      $options2 = sizeof( $possible[$rw][$matched_cols[$icol]] );
                      $pruned += ($options2 - $options);                      
                    }
                  }                  
                }
              }
            }
          }
        }
      }
    }
  }
 
  return $pruned;
  
}

//only one possibility
function SetCertain($ply) {
  global $instructions;
  global $possible;
  global $board;
  global $ply_set;
  
  $numset = 0;
  
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      $instructions++; 
      if (sizeof($possible[$y][$x]) == 1) {
        $board[$y][$x] = $possible[$y][$x][0]; 
        $numset++;
        $ply_set[$y][$x] = $ply;
      }
    }
  }
  return $numset;
}

//if only once cell in a row/col/block has a needed value as a possibility, then it must be the one
function SetRequired($ply) {  
  global $instructions;
  global $ply_set;
  global $board;
  
  $numset = 0;
  
  //cols
  for ($x = 0; $x < 9; $x++) {
    for ($val = 1; $val < 10; $val++) {
      if (!IsSetInCol($x, $val)) {        
        $instructions++; $opts = OptionsForValInCol($x, $val);      
        if (sizeof($opts) == 1) {
          $y = $opts[0][0];
          $board[$y][$x] = $val;
          $numset++;
          $ply_set[$y][$x] = $ply;
        }
      }
    }
  }
  
  //rows
  for ($y = 0; $y < 9; $y++) {
    for ($val = 1; $val < 10; $val++) {
      if (!IsSetInRow($y, $val)) {
        $instructions++; $opts = OptionsForValInRow($y, $val);      
        if (sizeof($opts) == 1) {
          $x = $opts[0][1];
          $board[$y][$x] = $val;
          $numset++;
          $ply_set[$y][$x] = $ply;
        }
      }
    }
  }
  
  //blocks
  for ($by = 0; $by < 3; $by++) {
    for ($bx = 0; $bx < 3; $bx++) {
      for ($val = 1; $val < 10; $val++) {
        if (!IsSetInBlock($bx, $by, $val)) {
          $instructions++; $opts = OptionsForValInBlock($bx, $by, $val);      
          if (sizeof($opts) == 1) {
            $y = $opts[0][0];
            $x = $opts[0][1];
            $board[$y][$x] = $val;
            $numset++;
            $ply_set[$y][$x] = $ply;
          }
        }
      }
    }
  }
  
  return $numset;
}


function Solve($puzzle) {
  global $board;
  global $init_board;
  global $possible;
  global $ply_set;
  global $ply;
  global $start_time;
  global $end_time;
  
  $ply = 0;
  $numset = 0;
  
  Init($puzzle);
  $start_time = microtime(true);
  do {
    $ply++;
    SetPossible();
    do {
      $pruned = 0;
      $pruned += PrunePossibleRuleOfK();
      $pruned += PrunePossibleExcluder();
      $pruned += PrunePossibleXwing();
      $pruned += PrunePossibleNakedTriples();
    } while( $pruned ); 
    $numset = SetCertain( $ply );
    $numset += SetRequired( $ply );    
  } while( $numset > 0 && !IsSolved() );
  $end_time = microtime(true);
  //echo $ply . "<pre>"; print_r($possible); echo "</pre>"; exit;
  
  //getting the 7 in position y=0,x=3: need to see that other cols and rows are blocked for 7: see fiendish_1.jpg.
  //
  //PrunePossible();
  //PrunePossibleXwing();
  
  
  return;
}
?>