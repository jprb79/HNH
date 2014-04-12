<?php require_once "phpuploader/include_phpuploader.php" ?>
<?php

$uploader=new PhpUploader();

if(@$_GET["download"])
{
	/*
    $fileguid=$_GET["download"];
	$mvcfile=$uploader->GetUploadedFile($fileguid);*/
    $filepath = $_GET["download"];
    $filename = $_GET["name"];

    switch (pathinfo($filename, PATHINFO_EXTENSION)) {
        case 'pdf':
            header("Content-type: application/pdf");
            header("Content-Disposition: inline; filename=\"" . $filename . "\"");
            break;
        default:
            header("Content-Type: application/oct-stream");
            header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
            break;
    }
    @readfile($filepath);
}

if(@$_POST["delete"])
{
	/*
    $fileguid=$_POST["delete"];
	$mvcfile=$uploader->GetUploadedFile($fileguid); */
    $filepath = $_POST["delete"];
	unlink($filepath);
	echo("OK");
}

if(@$_POST["guidlist"])
{
	$guidarray=explode("/",$_POST["guidlist"]);

	//OUTPUT JSON

	echo("[");
	$count=0;
	foreach($guidarray as $fileguid)
	{
		$mvcfile=$uploader->GetUploadedFile($fileguid);
		if(!$mvcfile)
			continue;
		
		//process the file here , move to some where
        $folder = "/var/www/html/modules/agent_console/upload";
        $prefix = date("HisdmY");

        $targetfilepath = "$folder/$prefix".'_'. $mvcfile->FileName;
        if( is_file ($targetfilepath) )
            unlink($targetfilepath);
        $mvcfile->MoveTo($targetfilepath );
        //rename(...)
		
		if($count>0)
			echo(",");
		echo("{");
		echo("FileGuid:'");echo($mvcfile->FileGuid);echo("'");
		echo(",");
		echo("FileSize:'");echo($mvcfile->FileSize);echo("'");
		echo(",");
		echo("FileName:'");echo($mvcfile->FileName);echo("'");
        echo(",");
        echo("OriginalFileName:'");echo($targetfilepath);echo("'");
		echo("}");
		$count++;
	}
	echo("]");
}

exit(200);

?>