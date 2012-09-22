<?php
/*
The following is a fairly straightforward implementation of Cpanel's API 2 functionality for addon domain creation.
This script requires an input file, which should be in newline dilimited format and consist only of domain names in 

domain.tld

format.  Anything other than this will result in errors.

This script is specifically designed with bulk capacity in mind and is not necessarily meant for adding single addon domain names.  
*/

//function 'verify' is designed to ensure all fields are inputted.  This will limit erroneous submissions.
function verify($post){
	if($post['input_file']==''||$post['host']==''||$post['cpanelusername']==''||$post['cpanelpass']==''||$post['cpanel_skin']==''||$post['addonpass']==''){
		$errors[]=1;
	}
	return $errors;
}
//function 'processreq' is designed to receive input from the form after validation.  See additional inline comments for more information.
function processreq($input_file,$host,$cpaneluser,$cpanelpass,$cpanel_skin,$addonpass){
	/*by associating doms with @file($input_file) an array named $doms is created.  Each element of $doms is associated with a single line, 
	which is equal to a single domain name.  This means that your input file must be in newline delimited format.*/
	$doms = @file($input_file);
	//if this file does not exist, then let the form know.
	if (!$doms) {
		return 'no dom';
	}
	//Cycle through each element of the $doms and act on them
	foreach($doms as $dom) {
		//Using 'explode' breaks the domain into its constituent pieces, the name and the extension (TLD), and puts them in an array
		$domain = explode('.',$dom);
		/*the directory path is defined by the first element appended to public_html.  It does not matter where this file, 'addonhelper.php', is located - 
		the program will always install the addon domains to this directory.  If this needs to be changed, this line needs to be updated.
		Some versions of Cpanel do not allow for this to be changed and will ignore changes.  Most notably - version X.*/
		$dir="public_html/".trim($domain[0]).".".trim($domain[1]);
		//the user is the first element of the $domain, as requested
		$user=trim($domain[0]).trim($domain[1]);
		//put the domain back together and trim whitespace.
		$dom=trim($domain[0]).".".trim($domain[1]);
		//create the cpanel request.
		$request = "/frontend/".$cpanel_skin."/addon/doadddomain.html?domain=".$dom."&user=".$user."&dir=".$dir."&pass=".$addonpass."";
		//process the request with addondomain below
		$result = addondomain($host,$cpaneluser,$cpanelpass,$request);
		//parse for easy reporting
		$show = strip_tags($result);
		$return[]=$show."<br><br>";
	}
	//return the result to the form
	return $return;
}
function addondomain($host,$ownername,$passw,$request) {
	//open a connection
	$sock = @fsockopen($host,2082);
	if(!$sock) {
		print('Socket error');
		exit();
	}
	//authenticate the connection
	$authstr = "$ownername:$passw";
	//make the passphrase slightly more difficult to decipher
	$pass = base64_encode($authstr);
	$in = "GET $request\r\n";
	$in .= "HTTP/1.0\r\n";
	$in .= "Host:$host\r\n";
	$in .= "Authorization: Basic $pass\r\n";
	$in .= "\r\n";
	//process
	fputs($sock, $in);
	while (!feof($sock)) {
		$result .= fgets ($sock,128);
	}
	fclose( $sock );
	return $result;
}
//the form.
function form($errors=array(),$success=0){
	?>
	<html>
	<head>
	<title>SpotOn SEO Services cPanel Addon Domains</title>
	<LINK REL=StyleSheet HREF="style.css" TYPE="text/css" MEDIA=screen>
	<script type='text/javascript'>
	function progress(){
		document.getElementById('progressbar').style.display='';
		document.getElementById('submit').style.display='none';
	}
	</script>
	</head>
	<body>
	<center>
	<div id='wrap'>
	<?
	if(count($errors)>0){
		echo "<div id='message'>";
		foreach($errors as $error){
			echo "<p class='error'>$error</p>";
		}
		echo "</div>";
	}elseif($success){
		echo "<div id='message'>";
		echo "<p class='success'>Process completed.  Communication with Cpanel successful.  Please check your control panel to make certain all domains were added correctly.</p>";
		echo "</div>";
	}
	?>
	<div id='header'></div>
	<div id='subtext'><p>To view installation instruction <a href="http://www.spotonseoservices.com/free-cpanel-php-script-automate-creation-addon-domains/">go here</a>.  This free PHP cPanel addon script created and supported by SpotOn <a href="http://www.spotonseoservices.com">SEO Services</a>.</p>
	</div>
	<form action='index.php' method='post'>
	<div id='form'>
	<p class='title'>Upload .txt file with domain names and type it's name here.</p>
	<p>File Name: <input type='text' name='input_file'></p>
	</div>
	<div id='form'>
	<p class='title'>Grant Cpanel Access</p>
	<p>Host: <input type='text' name='host'> (typically equal to 'localhost')</p>
	<p>cPanel Username: <input type='text' name='cpanelusername'></p>
	<p>cPanel Password: <input type='text' name='cpanelpass'></p>
	<p>cPanel Skin Name: <input type='text' name='cpanel_skin'></p>
	</div>
	<div id='form'>
	<p class='title'>Select a password for every addon domain</p>
	<p><input type='text' name='addonpass'> (This will be the FTP password for every addon domain added through this script)</p>
	<p style='text-align: right;'><input type='submit' name='submit' id='submit' value='Import Addon Domains' onclick='progress()'> <img style='display:none;padding-top:5px;' id='progressbar' src='progressbar.gif'></p>
	</div>
	</form>
	</div>
	</center>
	</body>
	</html>
	<?
}
?>