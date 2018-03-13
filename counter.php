<?php 
    
    // Set min and max values for the numbers to be outputted. 
    $minumum = 1;
    $maximum = 100;
    
        for ($counter = $minumum; $counter<$maximum+1; $counter++) {

            if ($counter%3 == 0 && $counter%5 == 0) {        
            	echo "triplefiver";       
            }
            else if ($counter%3 == 0){
                echo "triple";
            }
            else if ($counter%5 == 0){
                echo "fiver";
            }
            else {
                echo $counter;
            }
            
            echo "\n";
        }
?>