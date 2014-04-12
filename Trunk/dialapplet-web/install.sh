#!/bin/bash

#Instalador de dialapplet-web v.4.0

mkdir /var/log/dialapplet-web
chmod -R 777 /var/log/dialapplet-web
clear
#TODO: insertar en crontab scritps "cron_monitoring.php" y "importUploadedContacts.php"


##########################################
#Funciones y útiles
##########################################

function header()
{
	echo ""
	echo "============================================================="
	echo "                 $SectionHeader"
	echo "============================================================="
	echo ""
}
function numericInput()
{
	while ! [[ "$numericField" =~ ^[0-9]+$ ]];do
		 if [ -z $numericField ];
		 then    
			numericField=$defaultValue
		 else   
			echo "This ('$numericField') is not a number" 
		 	echo -n $message
		 	read -e numericField
		 fi
	done

}

##########################################
#Parametros de Asterisk
##########################################
SectionHeader="Asterisk Parameters"
header
echo -n "Enter Asterisk Server (127.0.0.1): "
read -e AsteriskServer
if [ -z $AsteriskServer ]; then 
	AsteriskServer="127.0.0.1"
fi
defaultValue="5038"
message="Enter Asterisk Port ($defaultValue): "
echo -n $message
read -e numericField
numericInput
AsteriskPort=$numericField

echo -n "Enter Asterisk User (admin): "
read -e AsteriskUser
if [ -z $AsteriskUser ]; then
	AsteriskUser='admin'
fi

echo -n "Enter Asterisk Password (123456): "
read -e AsteriskPassword
if [ -z $AsteriskPassword ]; then
	AsteriskPassword='123456'
fi

##########################################
# Parámetros de Postgresql
##########################################
SectionHeader="Postgresql Parameters"
header

echo -n "Enter Postgresql Server (127.0.0.1): "
read -e PostgresServer
if [ -z $PostgresServer ]; then
	PostgresServer='127.0.0.1'
fi
echo -n "Enter Postgresql Database (dialapplet): "
read -e PostgresDatabase
if [ -z $PostgresDatabase ]; then
        PostgresDatabase='dialapplet'
fi 
echo -n "Enter Postgresql User (dialapplet): "
read -e PostgresUser
if [ -z $PostgresUser ]; then
        PostgresUser='dialapplet'
fi
echo -n "Enter Postgresql Password (123456): "
read -e PostgresPass
if [ -z $PostgresPass ]; then
        PostgresPass='123456'
fi  

#########################################
# Parámetros para el WebService
#########################################
SectionHeader="WebService Parameters"
header

echo -n "Enter WebService Server (127.0.0.1): "
read -e WebServiceURL
if [ -z $WebServiceURL ]; then
        WebServiceURL="127.0.0.1"
fi      
defaultValue="8080"
message="Enter WebService Port ($defaultValue): "
echo -n $message
read -e numericField
numericInput
WebServicePort=$numericField


#########################################
# Parámetros para SMS 
#########################################
SectionHeader="SMS Parameters"
header

echo -n "Enter SMS User: "
read -e SMSUser
if [ -z $SMSUser ]; then
	SMSUser=''
fi
echo -n "Enter SMS Password: "
read -e SMSPass
if [ -z $SMSPass ]; then
        SMSPass=''
fi     

#########################################
# Grabaciones
#########################################

echo -n "Enter Recordings Path (/var/spool/asterisk/monitor): "
read -e RecPath
if [ -z $RecPath ]; then
        RecPath='/var/spool/asterisk/monitor'
fi  

echo -n "Enter VideoRecordings Path (/srv/ftp/VIDEO_RECORDS): "
read -e VideoRecPath
if [ -z $VideoRecPath ]; then
        VideoRecPath='/srv/ftp/VIDEO_RECORDS'
fi 


##########################################
#Print File /etc/dialapplet-web
##########################################

echo "<?php" >/etc/dialapplet-web.php
echo "" >>/etc/dialapplet-web.php
serverIP=`/sbin/ifconfig | grep "inet "| head -n 1|awk {'print $2'}|awk 'BEGIN {FS=":"};{print $2}'`
dwFolder=$(pwd)
echo "\$config['dwFolder']='$dwFolder';" >>/etc/dialapplet-web.php
echo "set_include_path (get_include_path().':'.\$config['dwFolder']);" >>/etc/dialapplet-web.php
echo "" >>/etc/dialapplet-web.php

echo "\$config['monitor_path']='$RecPath';" >>/etc/dialapplet-web.php
echo "\$config['VideoRecPath']='$VideoRecPath';" >>/etc/dialapplet-web.php

echo "\$config['AsteriskServerName']='$AsteriskServer';" >>/etc/dialapplet-web.php
echo "\$config['AsteriskPort']='$AsteriskPort';" >>/etc/dialapplet-web.php
echo "\$config['AsteriskUserName']='$AsteriskUser';" >>/etc/dialapplet-web.php
echo "\$config['AsteriskPassword']='$AsteriskPassword';" >>/etc/dialapplet-web.php
echo "" >>/etc/dialapplet-web.php

echo "\$config['postgresql_server']='$PostgresServer';" >>/etc/dialapplet-web.php
echo "\$config['postgresql_database']='$PostgresDatabase';" >>/etc/dialapplet-web.php
echo "\$config['postgresql_username']='$PostgresUser';" >>/etc/dialapplet-web.php
echo "\$config['postgresql_password']='$PostgresPass';" >>/etc/dialapplet-web.php
echo "" >>/etc/dialapplet-web.php
			          
echo "\$config['WebServiceServerName']='$WebServiceURL';" >>/etc/dialapplet-web.php
echo "\$config['WebServicePort']='$WebServicePort';" >>/etc/dialapplet-web.php
echo "" >>/etc/dialapplet-web.php

echo "\$config['thisServer']='$serverIP';">>/etc/dialapplet-web.php

echo "\$config['SMSUser']='$SMSUser';" >>/etc/dialapplet-web.php
echo "\$config['SMSPass']='$SMSPass';" >>/etc/dialapplet-web.php
echo "" >>/etc/dialapplet-web.php


echo "?>" >>/etc/dialapplet-web.php
SectionHeader="Configuration File generated"
header
cat /etc/dialapplet-web.php


##########################################
#Print File Add lines to crontab
##########################################
#echo "Añadiendo lineas al fichero /etc/crontab"
#echo "\timportUploadedContacts.php"
#echo "* * * * * root php $dwFolder/importUploadedContacts.php 2>>/var/log/dialapplet-web/importUploadedContacts.log >>/var/log/dialapplet-web/importUploadedContacts.log" >>/etc/crontab
#echo "\tcron_monitoring.php"
#echo "* * * * * root php $dwFolder/cron_monitoring.php" >>/etc/crontab
#echo "\tdialapplet_harmonization.php"
#echo "* * * * * root php $dwFolder/dialapplet_harmonization.php" >>/etc/crontab

