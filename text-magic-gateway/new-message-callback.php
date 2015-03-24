<?php

$pid = posix_getpid();

$spool_dir = "/tmp/succinctdata/smsspool";
$smac = "/Users/gardners/g/smac/smac";
$statsdat_path = "/tmp/succinctdata";
$recipe_dir = "/tmp/succinctdata/recipes";
$sd_spool_dir = "/tmp/succinctdata/sdspool";
$sd_output_dir = "/tmp/succinctdata/sdoutput";
$sd_passphrase_file = "/tmp/succinctdata/passphrase";
$odk_aggregate_instance = "http://serval1.csem.flinders.edu.au:8080/ODKAggregate";

// $credentialsfile="/newpool/odk/succinctdata/odkcredentials.txt";
$credentialsfile="/tmp/succinctdata/odkcredentials.txt";
$cookiefile="/tmp/succinctdata/cookies.$pid";
// $curl="/opt/csw/bin/curl";
$curl="/usr/bin/curl";

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
          $prefix = substr($text,0,10);
	  $sender = preg_replace("/[^A-Za-z0-9 ]/", '', $sender);
          $dest_dir = "$spool_dir/from_$sender";
          $name="$dest_dir/$prefix";

	  // Make sure prefix is at least 8 bytes
	  if ( strlen($prefix) == 10 ) {
	    echo "$name \n";
	    echo "message = $text\n";

	    // Write message
	    if (!file_exists($dest_dir)) mkdir($dest_dir);
	    chmod($dest_dir,0755);
	    if (file_exists($name)) unlink($name);
	    file_put_contents($name,$text);

	    // Call SMAC to import message in case it completes a message
	    echo "cd $statsdat_path ; $smac recipe decrypt $dest_dir $sd_spool_dir @$sd_passphrase_file\n";
	    shell_exec("cd $statsdat_path ; $smac recipe decrypt $dest_dir $sd_spool_dir @$sd_passphrase_file");

	    // Delete message from TextMagic server
	    echo "msgid=$msgid\n";
	    $api->deleteReply(array($msgid));
	  }
        }
}

// After receiving all new messages, do a succinct data import of any reassembled
// messages
echo "<hr>SD Import<p>\n";
shell_exec("cd $statsdat_path ; $smac recipe decompress $recipe_dir $sd_spool_dir $sd_output_dir >$spool_dir/decompress.log");

// Then push any XML files to the ODK Aggregate instance
echo "<hr>Push to ODK Aggregate<p>\n";

// log in to ODK Aggregate instance if required
if (file_exists($cookiefile)) unlink($cookiefile);
shell_exec("$curl -v --digest --cookie-jar $cookiefile -u `cat $credentialsfile` $odk_aggregate_instance/local_login.html >&/tmp/curl.log");

$path = realpath($sd_output_dir);

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $file){
  $filename=$file->getPathname(); // $file->getFilename();
    echo "file $filename\n";
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ( $ext == "xml") {
      echo "<p>Processing $filename\n";
      shell_exec("$curl -v -b $cookiefile --cookie-jar $cookiefile  -F \"xml_submission_file=@$filename\" $odk_aggregate_instance/submission");
    }
}
if (file_exists($cookiefile)) unlink($cookiefile);

// XXX - Also push to Magpi server if required
echo "<hr>Push to Magpi<p>\n";


echo "<hr>End<p>\n";

?>
