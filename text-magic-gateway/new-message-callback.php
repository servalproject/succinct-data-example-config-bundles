<?php

$spool_dir = "/tmp/succinctdata";
$smac = "/Users/gardners/g/smac/smac";
$recipe_dir = "";
$sd_spool_dir = "";
$sd_output_dir = "";
$sd_passphrase_file = "";

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
          $dest_dir = "$spool_dir/from_$sender";
          $name="$dest_dir/$prefix";

	  // Make sure prefix is at least 8 bytes
	  if ( strlen($prefix) == 8 ) {
	    echo "$name \n";
	    echo "message = $text\n";

	    // Write message
	    if (!file_exists($dest_dir)) mkdir($dest_dir);
	    chmod($dest_dir,0755);
	    if (file_exists($name)) unlink($name);
	    file_put_contents($name,text);

	    // Call SMAC to import message in case it completes a message
	    shell_exec("$smac recipe decrypt $dest_dir $sd_spool_dir @$sd_passphrase_file > $spool_dir/import.log");

	    // XXX - Delete message from TextMagic server
	  }
        }
}

// After receiving all new messages, do a succinct data import of any reassembled
// messages
shell_exec("$smac recipe decompress $recipe_dir $sd_spool_dir $sd_output_dir >$spool_dir/decompress.log");


echo "<hr>End<p>\n";

?>
