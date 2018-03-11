<?php 
    
    // Set min and max values for the numbers to be outputted. 
    $minumum = 1;
    $maximum = 100;
    
    /*
    // Loop through numbers and print 
    for ($counter = $minumum; $counter<$maximum+1; $counter++) {
        
        if ($counter%3 == 0 && $counter%5 == 0) {
            echo "triplefiver <br>";
        }
        else if ($counter%3 == 0){
            
            echo "triple<br>";
        }
        else if ($counter%5 == 0){
            
            echo "fiver<br>";
        }
        else {
            echo $counter . "<br>";
        }
        
    }*/
    
 //$time_start = microtime(true); 
    
    
   /* for ($i = 0; $i<1000; $i++) {
        
        for ($counter = $minumum; $counter<$maximum+1; $counter++) {

            $outString = "";

            if ($counter%3 == 0) {
                $outString .= "triple";
            }
            if ($counter%5 == 0){ 
                $outString .= "fiver";
            }
            if ($outString == ""){
                $outString = $counter;
            }

            $outString .= "<br>";

            echo $outString;

        }
		}
		*/
	
		    // FIRST METHOD
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
    
  //  echo 'Total execution time in seconds: ' . (microtime(true) - $time_start);
    
?>