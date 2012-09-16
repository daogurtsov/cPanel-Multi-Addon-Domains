<?
require_once('addonhelper.php');
if(isset($_POST['submit'])){
	$verify=verify($_POST);
	if(count($verify)>0){
		$errors[]="All Fields are Required";
		echo form($errors);
	}else{
		$process=processreq($_POST['input_file'],$_POST['host'],$_POST['cpanelusername'],$_POST['cpanelpass'],$_POST['cpanel_skin'],$_POST['addonpass']);
		if($process=='no dom'){
			$errors[]="The input file you selected does not exist or is not readable.  Please try again.";
			echo form($errors);
		}else{
			echo form(array(),1);
		}
	}
}else{
	echo form();
}
?>


