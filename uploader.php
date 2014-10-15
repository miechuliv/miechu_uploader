<?php
/**
 * Created by JetBrains PhpStorm.
 * User: USER
 * Date: 24.04.14
 * Time: 11:51
 * To change this template use File | Settings | File Templates. bla bal bla bla bla
*/
// connect and login to FTP server
$ftp_server = "demo.stronazen.pl";
$ftp_username = 'demostrona';
$ftp_userpass = 'GKv0jFcXYB';
$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);

$file = "localfile.txt";
$uploadFolder = 'c:/xampp/htdocs/miechu_uploader/upload/';
$backupFolder = 'c:/xampp/htdocs/miechu_uploader/backup/';

$di = new RecursiveDirectoryIterator($uploadFolder,RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($di);

// root do którego wrzucamy pliki
$serverBasePath = '/public_html/livesell/';

$files = array();

foreach($it as $file)
{

        $files[] = array(
            'localPath' => $file->getPathname(),
            'serverPath' => str_ireplace(rtrim($uploadFolder,'/'),$serverBasePath,$file->getPathname()),
        );
}



foreach($files as $file)
{
    // upload file
    $uploadName = str_ireplace('//','/',str_ireplace('\\','/',$file['serverPath']));
    $t = explode('/',$uploadName);
    array_pop($t);
    // direcotory do którego wrzucamy
    $dir = implode('/',$t);


    if (@ftp_chdir($ftp_conn, $dir))
    {

        // sciagamy dziada

        // a teraz zapisujemy
        downM($ftp_conn,$uploadName,$file,$uploadFolder,$backupFolder);
        saveM($ftp_conn,$uploadName,$file);
    }
    else
    {


        ftp_mksubdirs($ftp_conn,'',$dir);

        saveM($ftp_conn,$uploadName,$file);

    }
}

function downM($ftp_conn, $uploadName, $file, $uploadFolder, $backupFolder)
{


    $backup_path = str_ireplace(rtrim($uploadFolder,'/'),$backupFolder,$file['localPath']);
    $backup_path = str_ireplace('/\\','/',$backup_path);
    $backup_path = str_ireplace('\\','/',$backup_path);

    $ldir = $backup_path;

    $t = explode('/',$ldir);
    array_pop($t);
    $ldir = implode('/',$t);


    if(file_exists($ldir))
    {
        touch($backup_path);
        ftp_get($ftp_conn,$backup_path,$uploadName, FTP_BINARY);
    }
    elseif(!file_exists($ldir))
    {

        mkdir($ldir,0777,true);
        touch($backup_path);
        ftp_get($ftp_conn,$backup_path,$uploadName, FTP_BINARY);
    }



}

function saveM($ftp_conn, $uploadName, $file)
{

    if (ftp_put($ftp_conn, $uploadName, $file['localPath'], FTP_BINARY))
    {
        echo "Successfully uploaded $file.";
    }
    else
    {

        echo "Error uploading $file.";
    }
}

function ftp_mksubdirs($ftpcon,$ftpbasedir,$ftpath){
    @ftp_chdir($ftpcon, $ftpbasedir); // /var/www/uploads
    $parts = explode('/',$ftpath); // 2013/06/11/username
    foreach($parts as $part){
        if(!@ftp_chdir($ftpcon, $part)){
            ftp_mkdir($ftpcon, $part);
            ftp_chdir($ftpcon, $part);
            //ftp_chmod($ftpcon, 0777, $part);
        }
    }
}

function mksubdirs($ftpath){

    $parts = explode('/',$ftpath); // 2013/06/11/username
    array_shift($parts);
    foreach($parts as $part){
        if(!is_dir($part)){
            mkdir($part);
            chdir($part);
            //ftp_chmod($ftpcon, 0777, $part);
        }
    }
}

// close connection
ftp_close($ftp_conn);
?>