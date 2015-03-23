<?php

require_once 'api_password.php';

require_once 'TextMagicAPI.php';
$api = new TextMagicAPI(array(
    "username" => "paul.gardner-stephen@flinders.edu.au", 
    "password" => $api_password
));

echo "<hr>Fetch:\n";

$results = $api->receive(0);

echo "<hr>Message list:<p>\n";

if (isset($results["messages"])) {
        $list = $results["messages"];
        
        foreach ($list as $message) {
	  // Get important fields
          $text = $message["text"];
          $text = preg_replace("/[^A-Za-z0-9+=]/", '', $text);
          $msgid = $message["message_id"];
          $sender = $message["from"];
          $prefix = substr($text,0,8);
	  $sender = preg_replace("/[^A-Za-z0-9 ]/", '', $sender);
          $name="/tmp/succinctdata/from_$sender/$prefix";

	  // Make sure prefix is at least 8 bytes
	  if ( strlen($prefix) == 8 ) {
	    echo "$name \n";
	    echo "message = $text\n";

	    if (!file_exists("/tmp/succinctdata/from_$sender"))
	      mkdir("/tmp/succinctdata/from_$sender");
	    chmod("/tmp/succinctdata/from_$sender",0755);
	    if (file_exists($name)) unlink($name);
	    file_put_contents($name,text);  
	  }
        }
    }

echo "<hr>End<p>\n";

?>
