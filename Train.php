<?php

// Reads: 
//sentence \t score \n
//sentence \t score \n
//sentence \t score \n
//...
//
// Returns a newline delimited array of sentence \t score
// return array('sentence \t score', 'sentence \t score', 'sentence \t score' /*...*/);
function ReadTrainingFile($filename){
  $file = fopen($filename, 'r');
  $data = fread($file, filesize($filename));
  fclose($file);
  $data = explode("\n", $data);
  return $data;
}

// Fixed parts of the SQL
$sql_fields = 'INSERT INTO `Dictionary` (`Hash`, `Word`, `Count`,`AmazonPositive`,`AmazonNegative`,`IMDBPositive`,`IMDBNegative`,`YelpPositive`,`YelpNegative`) ';
$sql_on_dup = 'ON DUPLICATE KEY UPDATE `Count` = `Count` + 1, ';

$sql_queue = array(); // Store SQL queries here


// Training Data
$amazon = "Data/amazon_cells_labelled.txt";
$imdb = "Data/imdb_labelled.txt";
$yelp = "Data/yelp_labelled.txt";


// 0 = AMAZON
// 1 = IMDB
// 2 = YELP
// For all the data sets
for($current_group = 0; $current_group <= 2; $current_group++){
  
  $data = array();
  
  // AMAZON
  if($current_group == 0){
    echo 'Reading AMAZON data...';
    $data = ReadTrainingFile($amazon); 
  }
  
  // IMDB
  elseif($current_group == 1){
    echo 'Reading IMDB data...';
    $data = ReadTrainingFile($imdb); 
  }
  
  // YELP
  elseif($current_group == 2){
    echo 'Reading YELP data...';
    $data = ReadTrainingFile($yelp); 
  }
  echo ' Done.' . PHP_EOL;
  
  // AMAZON
	if($current_group == 0){
	  echo 'Processing AMAZON data...';
	}

	// IMDB
	elseif($current_group == 1){
	  echo 'Processing IMDB data...';
	}

	// YELP
	elseif($current_group == 2){
	  echo 'Processing YELP data...';
	}

  foreach($data as &$sentement){
    
    
    $sentement = explode("\t", $sentement); // Split by tabs  
    $sentement[0] = preg_replace("/[^a-zA-Z0-9\s]/", "", $sentement[0]); // Remove symbols
    $words = explode(' ', $sentement[0]); // Split words
    $words = array_filter($words); // Remove empty
    
    $s_positive = 0;
    $s_negative = 0;
      
    // If the score is 1 then s_positive = 1
    if(@$sentement[1] == 1){
      $s_positive = 1;
    }
    else{ // The score is 0 so s_negative = -1
      $s_negative = -1;
    }

    // For all the words in the sentence 
    foreach($words as &$word){
      $word = strtolower($word);
      $hash = hash('md5', $word);
      
      // Build SQL
      $sql = $sql_fields;
      if($current_group == 0){// AMAZON
        $sql .= "VALUES('$hash', '$word', 1, $s_positive, $s_negative, 0, 0, 0, 0) ";
        $sql .= $sql_on_dup;
        
        if($s_positive == 1){
          $sql .= "`AmazonPositive` = `AmazonPositive` + 1";
        }
        elseif($sentement[1] == 0){
           $sql .= "`AmazonNegative` = `AmazonNegative` + -1";
        }
      }
      elseif($current_group == 1){// IMDB
        $sql .= "VALUES('$hash', '$word', 1, 0, 0, $s_positive, $s_negative, 0, 0) ";
        $sql .= $sql_on_dup;
        
        if($s_positive == 1){
          $sql .= "`IMDBPositive` = `IMDBPositive` + 1";
        }
        elseif($s_negative == -1){
           $sql .= "`IMDBNegative` = `IMDBNegative` + -1";
        }
      }
      elseif($current_group == 2){// YELP
        $sql .= "VALUES('$hash', '$word', 1, 0, 0, 0, 0, $s_positive, $s_negative) ";
        $sql .= $sql_on_dup;
        
        if($s_positive == 1){
          $sql .= "`YelpPositive` = `YelpPositive` + 1";
        }
        elseif($s_negative == -1){
           $sql .= "`YelpNegative` = `YelpNegative` + -1";
        }
      }
      
      // Save SQL in the queue
      $sql_queue[] = $sql;
    }
    
    
  }
  echo ' Done.' . PHP_EOL; // done processing
}



echo 'Connecting to Database...';

// MySQL Server Credentials
$server = 'localhost';
$username = 'root';
$password = '';
$db = 'SentimentPolarity';

// Create connection
$conn = new mysqli($server, $username, $password, $db);

// Check connection
if ($conn->connect_error) {
  die("MYSQL DB Connection failed: " . $conn->connect_error);
}

echo ' Done.' . PHP_EOL;

echo 'Updating Sentiment Analysis Dictionary...';
foreach($sql_queue as $sql){
  // Uncomment this if you want to watch the 
  // queries though it really slows things down.
  //echo $sql . PHP_EOL; 
  $conn->query($sql);
}
echo 'Done' . PHP_EOL;

// Disconnect $conn from MySQL
$conn->close();


echo 'Training Complete.' . PHP_EOL;
