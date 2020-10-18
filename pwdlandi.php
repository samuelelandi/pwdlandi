#!/usr/bin/php
<?php
//***************************************************************************
//**** PASSWORD MANAGER FOR SHELL ver 1.1
//**** Features:
//**** 3 layers of encryption: AES 256, Camellia 256 and Chacha
//**** Clear data is never saved on the disk
//**** No cache
//**** short source code easy to check for security
//**** db saved in the current folder as pwd.encrypted
//**** db backup at every rewrite as pwd.encrypted.backup
//**** no graphical interface
//**** Requirements:
//**** - PHP 7
//**** - OPENSSL library for PHP
//****
//**** Tested on:
//**** Mac Os/x 13.4 (High Sierra), requirement are installed by default.
//**** It should work  on Linux and Windows as well.
//***************************************************************************

// create empty db if not yet present
if(!file_exists("pwd.encrypted"))
    create_pwdfile();
$r=NULL;
// ask Master Password
while($r==NULL){
    $pwd=ask_hidden("Master Password:" );
    $GLOBALS['r']=load_pwdfile($pwd);
    if($GLOBALS['r']==NULL){
     echo "Wrong password\n";
     continue;
     }
    echo "\n";
    system("clear");
}
$s=explode("\n",$GLOBALS['r']);
while(1){
    echo "######################################################################\n";
    echo "Password Manager - Commands: /add /delete /exit /pwd /gen /all/ /help\n";
    echo "######################################################################\n";
    $ss=readline("Search String/Command: ");
    if(strlen($ss)==0)
        continue;
    if(strstr(strtoupper($ss),"/ADD")!=NULL){
        new_entry($pwd);
        $s=explode("\n",$GLOBALS['r']);
        continue;
    }
    if(strstr(strtoupper($ss),"/EXIT")!=NULL)
        exit(0);
    if(strstr(strtoupper($ss),"/QUIT")!=NULL){
        system(clear);
        exit(0);        
    }
    if(strstr(strtoupper($ss),"/PWD")!=NULL){
        change_pwd($pwd);
        continue;
    }
    if(strstr(strtoupper($ss),"/HELP")!=NULL){
        show_help();
        continue;        
    }
    if(strstr(strtoupper($ss),"/GEN")!=NULL){
        generate_randompwd();
        continue;        
    }
    if(strstr(strtoupper($ss),"/DELETE")!=NULL){
        delete_entry($ss,$pwd);        
        $s=explode("\n",$GLOBALS['r']);  
        continue;
    }      
    //*** SEARCHING
    $c=1;
    $x=count($s);
    $ss=strtoupper($ss);
    for($i=1;$i<$x;$i++){
            $j=json_decode($s[$i]);
            if(strstr(strtoupper($j->description),$ss)!=NULL ||
               strstr(strtoupper($j->username),$ss)!=NULL ||
               strstr(strtoupper($j->url),$ss)!=NULL || ($ss=="/ALL" && strlen($j->description)>0))
            {
                echo "******************************************************************\n";
                echo "#..........:".$i."\n";
                echo "Description: ".$j->description."\n";
                echo "Username...: ".$j->username."\n";
                echo "Password...: ";
                echo "\033[30;40m"; 
                echo $j->password;
                echo "\033[0m";
                echo "\n";                
                echo "Url........: ".$j->url."\n";                
                $c=$c+1;
            }
    }
    if($c>1) echo "******************************************************************\n";
}
//******************************************
// function to change password
//******************************************
function change_pwd($pwd){
    while(1){
        $pwd1=ask_hidden("Current Master Password: ");    
        if(strlen($pwd1)==0)
            return;
        if($pwd!=$pwd1){
            echo "Wrong password\n";
            continue;
        }
        $pwdn1=ask_hidden("NEW Master Password: ");
        $pwdn2=ask_hidden("Repeat NEW Master Password:");
        if($pwdn1!=$pwdn2){
            echo "NEW Passwords are not the same!\n";
            continue;
        }
        save_pwdfile($pwdn1);
        $GLOBALS['pwd']=$pwdn1;
        echo "Password changed.";   
        return;
    }
}

//******************************************
//*** function to delete an entry
//******************************************
function delete_entry($ss,$pwd){
$id=substr($ss,8);
if($id==0){
    echo "Entry number is missing. for example: /delete 2\n";
    echo "to delete the entry #2\n";
    return;
}
$s=explode("\n",$GLOBALS['r']);
echo "******************************************************************\n";
$j=json_decode($s[$id]);
echo "#..........:".$id."\n";
echo "Description: ".$j->description."\n";
echo "Username...: ".$j->username."\n";
echo "Password...: ";
echo "\033[30;40m"; 
echo $j->password;
echo "\033[0m";
echo "\n";
echo "Url........: ".$j->url."\n";
echo "******************************************************************\n";
$c=readline("Delete? (Y/n) ");
if(strtoupper($c)=="Y"){
    $s[$id]="";
    $x=count($s);
    $GLOBALS['r']="";
    for($i=0;$i<$x;$i++){
        if(strlen($s[$i])>0)
            $GLOBALS['r'].=$s[$i]."\n";
    }
    save_pwdfile($pwd);
    echo "#".$id." has been deleted.\n";
}
return;
}
//******************************************
//*** Load pwd.encrypted
//******************************************
function load_pwdfile($pwd){
    $s=file_get_contents("pwd.encrypted");
    $iv=substr($s,0,512);
    $dbh=substr($s,512);
    $db=base64_decode($dbh);
    $dpwd=openssl_pbkdf2($pwd,$iv,64,10000,"sha512");
    $ivl=openssl_cipher_iv_length($cipher="CAMELLIA-256-CFB");
    $ivc=substr($iv,0,$ivl);
    $r=openssl_decrypt($db,"CAMELLIA-256-CFB",$dpwd,$options=OPENSSL_RAW_DATA,$ivc);
    $ivl=openssl_cipher_iv_length($cipher="ChaCha");
    $ivc=substr($iv,0,$ivl);
    $r=openssl_decrypt($r,"ChaCha",$dpwd,$options=OPENSSL_RAW_DATA,$ivc);
    $ivl=openssl_cipher_iv_length($cipher="AES-256-OFB");
    $ivc=substr($iv,0,$ivl);
    $r=openssl_decrypt($r,"AES-256-OFB",$dpwd,$options=OPENSSL_RAW_DATA,$ivc);
    if(substr($r,0,1)=="{")
        return($r);
    else
        return(NULL);
}
//******************************************
//**** function to add new entry    
//******************************************
function new_entry($pwd){
    while(1){
        $d=readline("Description: ");
        $u=readline("Username...: ");
        $p=readline("Password...: ");
        $url=readline("Url........: ");
        $c=readline("Confirm?(Y/N/Edit)");
        if($c=="Y" || $c=="y"){
            $s='{"description":';
            $s.=json_encode($d);
            $s.=',"username":';
            $s.=json_encode($u);
            $s.=',"password":';
            $s.=json_encode($p);
            $s.=',"url":';
            $s.=json_encode($url);
            $s.="}\n";
            $GLOBALS['r'].=$s;
            save_pwdfile($pwd);
        }
        if($c=="E" || $c=="e")
            continue;
        return;
    }

}
//******************************************
//**** function to create new pwd.encrypted
//******************************************
function create_pwdfile(){
    echo "Creating new pwd.encrypted file\n";
    INIT:
    $pwd=ask_hidden("Master Password (very,very long please!): ");
    $pwd2=ask_hidden("Repeat Master Password:");
    if($pwd!=$pwd2){
        echo "Passwords are not the same!\n";
        goto INIT;
    }
    $GLOBALS['r']="{}\n";
    save_pwdfile($pwd);
    echo "pwd.encrypted file has been created!\n";   
    return;
}
//***********************************************
// function to save pwd.encrypted
//**********************************************
function save_pwdfile($pwd){
echo "Generating True Random Init Vector...\n";
$iv=openssl_random_pseudo_bytes(512,$cs);
$iv=substr(base64_encode($iv),0,512);
echo "Encrypting...\n";
$s=$iv;
$dpwd=openssl_pbkdf2($pwd,$iv,64,10000,"sha512");
$ivl=openssl_cipher_iv_length($cipher="AES-256-OFB");
$ivc=substr($iv,0,$ivl);
$rc=openssl_encrypt($GLOBALS['r'],"AES-256-OFB",$dpwd,$options=OPENSSL_RAW_DATA,$ivc);
$ivl=openssl_cipher_iv_length($cipher="ChaCha");
$ivc=substr($iv,0,$ivl);
$rc=openssl_encrypt($rc,"ChaCha",$dpwd,$options=OPENSSL_RAW_DATA,$ivc);
$ivl=openssl_cipher_iv_length($cipher="CAMELLIA-256-CFB");
$ivc=substr($iv,0,$ivl);
$rc=openssl_encrypt($rc,"CAMELLIA-256-CFB",$dpwd,$options=OPENSSL_RAW_DATA,$ivc);
$rcf=$iv."!".base64_encode($rc);
echo "Encryption completed\n";
system("cp pwd.encrypted pwd.encrypted.backup");
file_put_contents("pwd.encrypted",$rcf);
return;
}

function ask_hidden( $prompt ) {
	echo $prompt;
	echo "\033[30;40m";  
	$input=readline();
	echo "\033[0m";      
	return rtrim( $input, "\n" );
}
//******************************************************
// FUNCTION TO GENERATE A TRUE RANDOM STRONG PASSWORD
//******************************************************
function generate_randompwd(){
    $tr=openssl_random_pseudo_bytes(512);
    $p=substr(bin2hex($tr),0,64);
    echo "Random STRONG password: ".$p."\n";
    return;
}
//*****************************************
//*** function to show an help
//******************************************
function show_help(){
echo "****************************************************************\n";
echo "Password Manager - Help\n";
echo "****************************************************************\n";
echo "You can write a string to search or a command and press enter.\n";
echo "The available commands are the following:\n";
echo "/add - To add a new entry\n";
echo "/delete # - To delete and entry where # should the entry number\n";
echo "/pwd - To change the master password\n";
echo "/gen - Generate a true random strong passwrd\n";
echo "/all - List all the entries\n";
echo "/exit - To exit from the program\n";
echo "/help - To access this help\n";
echo "****************************************************************\n";
return;
}
?>
    
