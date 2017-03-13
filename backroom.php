<?php

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


function PrunePossibleRuleOfK() {
  //e.g. if n cells in row/col/block have the same n possibilities then can remove those n possibilities from any other cells in row/col/block
  
  global $possible;
  $numopts = 0;
  $matching = 0;
  $pruned = 0;
  
  for ($y = 0; $y < 9; $y++) {
    for ($x = 0; $x < 9; $x++) {
      if (!IsKnown($x,$y)) {
        $numopts = sizeof($possible[$y][$x]);
        $matching = CountSamePossibleAs_Row($x, $y);          
        if ($matching == $numopts) {
          //if ($y == 7 && $x == 6) { echo "<pre>"; print_r($possible); echo "</pre>"; }
          $pruned += RemoveSamePossibleAs_Row($x, $y);
          //if ($y == 7 && $x == 6) { echo "<pre>"; print_r($possible); echo "</pre>"; }
          //if ($y == 7 && $x == 6) exit;
        }
        $matching = CountSamePossibleAs_Col($x, $y);
        if ($matching == $numopts) $pruned += RemoveSamePossibleAs_Col($x, $y);
        $matching = CountSamePossibleAs_Block($x, $y);
        if ($matching == $numopts) $pruned += RemoveSamePossibleAs_Block($x, $y);
      }
    }
  }
  return $pruned;
}




function depth_picker($arr, $temp_string, &$collect) {
  if ($temp_string != "") 
    $collect []= $temp_string;

  for ($i=0; $i<sizeof($arr);$i++) {
    $arrcopy = $arr;
    $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
    if (sizeof($arrcopy) > 0) {
      depth_picker($arrcopy, $temp_string ." " . $elem[0], $collect);
    } else {
      $collect []= $temp_string. " " . $elem[0];
    }   
  }   
}

function CountPossibleContains_Row($x, $y, $arr_vals) {
  global $possible;
  
  $matching = 0;
  for( $xx = 0; $xx < 9; $xx++ ) {
    if( !IsKnown( $xx, $y ) ) {
      $match = true;
      for( $i = 0; $i < sizeof( $arr_vals ); $i++ ) {
        if( !in_array( $arr_vals[$i], $possible[$y][$xx] ) ) $match = false;
      }
      if( $match ) $matching++;
    }
  }
  return $matching;
}

function CountPossibleContains_Col($x, $y, $arr_vals) {
  global $possible;
  
  $matching = 0;
  for( $yy = 0; $yy < 9; $yy++ ) {
    if( !IsKnown( $x, $yy ) ) {
      $match = true;
      for( $i = 0; $i < sizeof( $arr_vals ); $i++ ) {
        if( !in_array( $arr_vals[$i], $possible[$yy][$x] ) ) $match = false;
      }
      if( $match ) $matching++;
    }
  }
  return $matching;
}

function CountPossibleContains_Block($x, $y, $arr_vals) {
  global $possible;
  
  $block_y = GetBlock($y);
  $block_x = GetBlock($x);
  
  $matching = 0;
  for ($yy = $block_y*3; $yy < $block_y*3+3; $yy++) {
    for ($xx = $block_x*3; $xx < $block_x*3+3; $xx++) {
      if( !IsKnown( $xx, $yy ) ) {
        $match = true;
        for( $i = 0; $i < sizeof( $arr_vals ); $i++ ) {
          if( !in_array( $arr_vals[$i], $possible[$yy][$xx] ) ) $match = false;
        }
        if( $match ) $matching++;
      }
    }
  }
  return $matching;
}

function RemovePossibleContains_Row( $x, $y, $arr_vals ) {
  global $possible;
  
  $removed = 0;
  for( $xx = 0; $xx < 9; $xx++ ) {
    if( !IsKnown( $xx, $y ) ) {
      $match = true;
      for( $i = 0; $i < sizeof( $arr_vals ); $i++ ) {
        if( !in_array( $arr_vals[$i], $possible[$y][$xx] ) ) $match = false;
      }
      if( $match ) { //remove any other possibilities from this cell
        $removed += sizeof( $possible[$y][$xx] ) - sizeof( $arr_vals );
        $possible[$y][$xx] = $arr_vals;
      }
    }
  }
  return $removed;
}

function RemovePossibleContains_Col( $x, $y, $arr_vals ) {
  global $possible;
  
  $removed = 0;
  for( $yy = 0; $yy < 9; $yy++ ) {
    if( !IsKnown( $x, $yy ) ) {
      $match = true;
      for( $i = 0; $i < sizeof( $arr_vals ); $i++ ) {
        if( !in_array( $arr_vals[$i], $possible[$yy][$x] ) ) $match = false;
      }
      if( $match ) { //remove any other possibilities from this cell
        $removed += sizeof( $possible[$yy][$x] ) - sizeof( $arr_vals );
        $possible[$yy][$x] = $arr_vals;
      }
    }
  }
  return $removed;
}

function RemovePossibleContains_Block( $x, $y, $arr_vals ) {
  global $possible;
  
  $block_y = GetBlock($y);
  $block_x = GetBlock($x);
  
  $removed = 0;
  for ($yy = $block_y*3; $yy < $block_y*3+3; $yy++) {
    for ($xx = $block_x*3; $xx < $block_x*3+3; $xx++) {
      if( !IsKnown( $xx, $yy ) ) {
        $match = true;
        for( $i = 0; $i < sizeof( $arr_vals ); $i++ ) {
          if( !in_array( $arr_vals[$i], $possible[$yy][$xx] ) ) $match = false;
        }
        if( $match ) { //remove any other possibilities from this cell
          $removed += sizeof( $possible[$yy][$xx] ) - sizeof( $arr_vals );
          $possible[$yy][$xx] = $arr_vals;
        }
      }
    }
  }
  return $removed;
}

//maybe a better rule:
  //see if any sets of possibilities in row/col/block have a minimal no. of cells, then eliminate any other possibilities in those cells:
  //e.g. if block has possibilities (2,7,8) (2,7,9) and none of the other cells have 2 and 7 as possibilities, then can remove 8 and 9 from those two block 
  //possibilities, since the cells can only be 2 and 7, its just which is which that is unclear.
  //compile list of all possible permutations (p) of all lengths (n) from available possibilities for a cell
  //  count occurrence in the other cells.  if count = n then remove any other possibilities from matching cells
function PrunePossibleMmm() {
  global $possible;
  $matching = 0;
  $pruned = 0;
  
  for( $y = 0; $y < 9; $y++ ) {
    for( $x = 0; $x < 9; $x++ ) {
      if( !IsKnown( $x, $y ) && sizeof( $possible[$y][$x] ) > 1 ) {
        $collect = array();
        depth_picker( $possible[$y][$x], "", $collect );
        
        //for each permutation of possibilities of length 2,3..n
        //count how many times this possibility exist in row/col/block
        //if it equals the length n, then remove all other possibilities from the cells in which it was found
        for( $perm = 0; $perm < sizeof( $collect ); $perm++ ) {
          $option = explode(" ", trim($collect[$perm]));
          //print_r($collect[$perm]); print_r($option); exit;
          if( sizeof( $option ) > 1 ) {
            $matching = CountPossibleContains_Row( $x, $y, $option ); 
            //if ($x == 7 && $y == 7 ) { echo $matching; print_r($option); echo "<pre>"; print_r($possible); }
            if( $matching == sizeof( $option ) ) $pruned += RemovePossibleContains_Row( $x, $y, $option );
            //if ($x == 7 && $y == 7 ) { print_r($possible); echo "</pre>"; echo $pruned; exit; }
            $matching = CountPossibleContains_Col( $x, $y, $option ); 
            if( $matching == sizeof( $option ) ) $pruned += RemovePossibleContains_Col( $x, $y, $option );
            $matching = CountPossibleContains_Block($x, $y, $collect[$perm]); 
            if( $matching == sizeof( $option ) ) $pruned += RemovePossibleContains_Block( $x, $y, $option );
          }
        }
      }
    }
  }
  return $pruned;
}



      
      
      foreach( $valuepairedinrow[$val] as $row => $cells ) {
        foreach( $cells as $cell ) {
          $cols[$cell[1]] = ( isset( $cols[$cell[1]] )? $cols[$cell[1]]+1 : 1 );
        }
      }
      //PROBLEM: if eg row 1 has value in cols 1 and 3; row 2 has vals in cols 3 and 5 and row 3 has vals in cols 5 and 7, then
      //cols 3 and 5 come out with count of 2, but actually they aren't valid
      //
      //so for val = A, cols[3] = 2 and cols[7] = 2
      //now see if two columns have scores of 2+
      $matchedcols = 0;
      
      for( $c = 0; $c < 9; $c++ ) {
        if( isset($cols[$c]) ) {
          if( $cols[$c] > 1 ) $matchedcols++;
        }
      }
      if( $matchedcols > 1 ) { //ok, now we can remove all incidence of the value from the possibilities of other cells in these two columns
        //foreach row r
          //if r is not one of the $valuepairedinrow[$val][$row]
            //foreach col c where $cols[c] > 1
            //if cell(r,c) has possibility $val then remove it
              //phew
        foreach( $cols as $col => $colcount ) {          
          for( $rw = 0; $rw < 9; $rw++ ) {
            if( !in_array( $rw, $valuepairedinrow[$val] ) ) { //don't remove from x-wing rows, obviously
              if( ! IsKnown( $col, $rw ) ) {
                $options = sizeof( $possible[$rw][$col] );
                $possible[$rw][$col] = RemoveValFromArray( $possible[$rw][$col], $val );
                $options2 = sizeof( $possible[$rw][$col] );
                $pruned += ($options2 - $options);
              }
            }
          }
        }
      }
    }
?>