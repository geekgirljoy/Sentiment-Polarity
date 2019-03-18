<?php


function AnalyseSentencePolarity(&$sentence, &$conn){
      
    $sentence = preg_replace("/[^a-zA-Z0-9\s]/", "", trim($sentence)); // remove symbols
    $sentence = strtolower($sentence);
    $words = explode(' ', $sentence); // split words
    $words = array_filter($words); // remove empty
    $unknown_words = array();
	
    $score = array();
	$unknown_word_count = 0;
    foreach($words as $key=>$word){

      $temp_word = strtolower($word);

      $words[$key] = strtolower($word);
      
      // Query the Tags table for all the known tags
      $sql = "SELECT * FROM `Dictionary` WHERE `Word` = '$temp_word'";
        
      $result = $conn->query($sql);
      if($result->num_rows > 0){
        while($row = mysqli_fetch_assoc($result)) {
          
          @$score[$temp_word]['Positive'] += $row['AmazonPositive'] + $row['IMDBPositive'] + $row['YelpPositive'];
          @$score[$temp_word]['Negative'] += $row['AmazonNegative'] + $row['IMDBNegative'] + $row['YelpNegative'];
        }
      }else{
        @$score[$temp_word]['Positive'] += 0;
        @$score[$temp_word]['Negative'] += 0;
		$unknown_word_count++;
		$unknown_words[] = $temp_word;
      }
    }
    return EvaluatePolarity($score, $words, $unknown_words);
}


function EvaluatePolarity(&$score, &$words, $unknown_words = NULL){
  
  $output = '';
  $total_score = 0;
  
  // Calculate sentence polarity score
  foreach($words as $key=>$word){
    $total_score += $score[$word]['Positive'] + $score[$word]['Negative'];
  }
  
  // Concat the number of words onto the output string
  $output .= 'Number of Words: ' . count($words) . PHP_EOL;
  
  
  // If unknown_words isnt null and is an array then - redundent I guess...
  // but tell me that doesn't just taste grok!
  if($unknown_words != NULL && is_array($unknown_words)){
	  $output .= 'Number of Unknown Words: ' . count($unknown_words) . PHP_EOL;
	  $output .= 'Unknown Words: ';
	  foreach($unknown_words as $word){
	      $output .= "\t $word";
	  }
	  $output .= PHP_EOL;
	  
	  $average_score = $total_score / (count($words) - count($unknown_words));
  }
  else{
	  $average_score = $total_score / count($words); // no unknown_words to account for
  }
  
  // Concat the sentence score and the average word score
  $output .= "Total Score: $total_score" . PHP_EOL;
  $output .= "Average Word Score: $average_score" . PHP_EOL;
  
  // Evaluate the polarity
  if($total_score > 0){
    $output .= "This sentence seems more Positive" . PHP_EOL;
  }
  elseif($total_score < 0){
    $output.= "This sentence seems more Negative" . PHP_EOL;
  }
  else{
    $output.= "This sentence seems Neutral" . PHP_EOL;
  }
  
  return $output;
}


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

echo str_repeat(PHP_EOL, 100); // clear terminal screen by writing 100 line ends.

// Welcome blurb
echo wordwrap("Welcome to the Sentiment Polarity Prototype.\n\nYou can say 'quit' or 'exit' to exit the program.\n\nSay something to analyze how positive or negative it is.\n\n", 75, PHP_EOL);
while(true){
  $sentence = readline("Say Something: ");
  readline_add_history($sentence); // allows you to press the up arrow and recover previously typed strings
  
  echo "I heard you say: ( $sentence )" . PHP_EOL;
  
  $end_program_words = array('quit', 'exit');
  
  
  // if user entered one of the word in the $end_program_words array
  if(in_array($sentence,$end_program_words)){
    die('Goodbye!' . PHP_EOL); // end program
  }
  elseif($sentence == 'score file') { // sentence IS the phrase score file
	  $filepath = readline("File Name: ");

	  if(is_file(getcwd() . "\\$filepath")){		  
		  $file = fopen(getcwd() . "\\$filepath", 'r');
		  $sentences = fread($file, filesize(getcwd() . "\\$filepath"));
		  fclose($file);
		  $file = fopen(getcwd() . "\\$filepath.scores.txt", 'w');
		  $sentences = explode(PHP_EOL, $sentences);
		  foreach($sentences as $key=>$sentence){
			  fwrite($file, "\"$sentence\"" . PHP_EOL . AnalyseSentencePolarity($sentence, $conn) .  PHP_EOL);
		  }
		  fclose($file);
		  
		  echo getcwd() . "\\$filepath.scores.txt was generated." .  PHP_EOL;
	  }
	  
  }
  else{ // Do Polarity Analysis
    echo AnalyseSentencePolarity($sentence, $conn) .  PHP_EOL;
  }
}
