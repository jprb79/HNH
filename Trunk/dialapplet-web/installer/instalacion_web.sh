#!/bin/bash

######################################################
######  	Web	   ###########################
######################################################
#Version web
echo "Indica la versión de la web a instalar 4.0.xxx" 
read version

#Descarga web
cd /var/www/html
wget http://www.dialapplet.com/downloads/dialapplet-web/dialapplet-web-4.0.$version.tgz
#Descomprimir
tar -xvzf dialapplet-web-4.0.$version.tgz
#comprobar que no existe otra version de la web
#y hacer copia en ese caso
ls | grep dialapplet-web > versionold
if [ -n $versionold];then
	cp dialapplet-web dialapplet-web-old
	rm dialapplet-web
fi
rm $versionold
#enlace simbolico
ln -s dialapplet-web-4.0.$version dialapplet-web
#permisos
chmod -R 777 dialapplet-web


#######################################################
##########	SCRIPT INSTALL.SH  	###############
#######################################################
#Instalador de dialapplet-web v.4.0
mkdir /var/log/dialapplet-web
clear

pwd
cd dialapplet-web
./install.sh
echo "Fin de la instalacion"


############################################################################################################

#IMPORTAR CONTACTOS CRONTAB
#Comprobar si ya se incluye la linea
contact="/var/www/htdocs/dialapplet-web/importUploadedContacts.php"
cat /etc/crontab | grep $contact > esta
if [ -z $esta];then
	echo "* * * * * root php /var/www/htdocs/dialapplet-web/importUploadedContacts.php 2>>/var/log/dialapplet-web/importUploadedContacts.log >>/var/log/dialapplet-web/importUploadedContacts.log" >> /etc/crontab
	echo "Contactos importados"
fi
rm esta

###########################################################################
######################  Dialserver	###################################
##########################################################################
clear
cd ..
echo "Instalando Dialserver..."

#Capturamos la arquitectura
arch=`uname -a | awk '{ print  $(NF-1) }'`
if [ "$arch" != "x86_64" ] ; then
	arch='i386'
fi


#cat /etc/issue
#Capturamos el SO TODO capturar CENTOS
if [ -f /etc/SuSE-release ]; then
	#Suse
	so="rpm"
elif [ -f /etc/debian_version ]; then
	#Debian
	so="debian"
elif [ -f /etc/redhat-release ]; then
	#CentOS
	so="rpm"
else 
	so="otro"
fi


# Indicar tipo de instalación 	Webservice
echo -n "Desea instalar versión con WebService? (s/n) "
read ws
echo -n "Indica la version de DialServer a instalar "
read dials


#Mostrar tipo de instalacion
if [ $ws="s" ]; then 
  webserv="con"
elif [ $ws="n" ]; then
  webserv="sin"
fi 
echo "Se va a instalar una versión $webserv webservice en una distribución $so de una arquitectura de $arch" 



#Con webservice
if [ $ws="s" ]; 
  then
  case "$so" in
    #SUSE Y CENTOS
    rpm)if [ $arquitectura="i386" ]; then
	#32 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-ws-$dials-1centos5.i386.rpm
	rpm -i --nomd5 dialserver-ws-$dials-1centos5.i386.rpm 
      else
	#64 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-ws-$dials-1centos5.x86_64.rpm
	rpm -i --nomd5 dialserver-ws-$dials-1centos5.x86_64.rpm
      fi;;
    #DEBIAN
    debian)if [ $arquitectura="i386" ];
	then
	#32 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-ws-$dials-debian.i386.deb
	dpkg -i dialserver-ws-$dials-debian.i386.deb
      else
	#64 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-ws-$dials-debian.x86_64.deb
	dpkg -i dialserver-ws-$dials-debian.x86_64.deb
      fi;;
  esac
#SIN WEBSERVICE
else
  case "$so" in
    #SUSE Y CENTOS
    rpm)if [ $arquitectura="i386" ];
	then
	# 32 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-$dials-1centos5.i386.rpm
	rpm -i --nomd5 dialserver-$dials-1centos5.i386.rpm
      else
	#64 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-$dials-1centos5.x86_64.rpm
	rpm -i --nomd5 dialserver-$dials-1centos5.x86_64.rpm
      fi;;
    #DEBIAN
    debian)if [ $arquitectura="i386" ];
	then
	#32 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-$dials-debian.i386.deb
	dpkg -i dialserver-$dials-debian.i386.deb
      else
	#64 bits
	wget http://www.dialapplet.com/downloads/dialserver/dialserver-$dials-debian.x86_64.deb
	dpkg -i dialserver-$dials-debian.x86_64.deb
	
      fi;;
  esac	
fi      

#Exportar librerias necesarias
export LD_LIBRARY_PATH=/usr/share/dialserver/bin:$LD_LIBRARY_PATH


#Configuracion /etc/dialserver.conf
echo -n "Introduce la licencia de Dialserver: "
read serial

#sed 's/cadena1/cadena2/' fichero 
#sed -i s/<a_reemplazar>/<reemplazo>/g <fichero>
#se
licencia="SerialNumber=$serial"
sed -i s/";SeriaNumber=PXXXXXXXXXXXXXXX"/$licencia/g /etc/dialserver.conf


#Configuracion /etc/asterisk/manager.conf
# TODO manager.conf enabled.yes
grep "enabled = yes" /etc/asterisk/ > enable

if [ -n "$enable" ]; then
	set enable = yes;
fi

# TODO machaca el fichero cdr_manager,conf
cd /etc/asterisk/
echo "[general] \n enabled=yes" > cdr_manager.conf


#echo "CDR_manager.conf enable=yes" >> /etc/asterisk/manager.conf
asterisk -rx "manager reload"


#Arranque dialserver
/etc/init.d/dialserver start


##################################
## DialStatus ####################
##################################
cd /usr/bin
wget http://www.dialapplet.com/downloads/scripts/dial_status.sh
chmod +x dial_status.sh

echo "* * * * * root /usr/bin/dial_status.sh" >> etc/crontab

#Editar script para poner nombre del cliente
echo "Introduce el nombre del cliente"
read cli
sed -i 's/LANZO DIALSERVER CLIENTE: (CLIENTE)/LANZO DIALSERVER CLIENTE: ('$cli')/'  /usr/bin/dialstatus.sh


#Comprobar que funciona
/etc/init.d/dialserver stop
/etc/init.d/dialserver start


####################################################################
############ CRON MONITORING    ####################################
####################################################################

echo "0 1 * * * root php/var/www/html/dialapplet-web/dialapplet_armonization.php" >> /etc/crontab
echo "* * * * * root php/var/www/html/dialapplet-web/cron_monitoring.php" >> /etc/crontab


### backup base de datos
#script
wget http://www.dialapplet.com/downloads/scripts/backup.sh
#script restauracion
wget http://www.dialapplet.com/downloads/scripts/restore_backup.sh

#copiar dependiendo del SO /var/lib/postgresql o /var/lib/pgsql
cp backup.sh /var/lib/postgresql
cp restore_backup.sh /var/lib/postgresql

cp backup.sh /var/lib/pgsql
cp restore_backup.sh /var/lib/pgsql

#Damos permisos de ejecucion
chmod +x backup.sh
chmod +x restore_backup.sh


#editar crontab
echo "30 15 * * * /var/lib/postgresql/backup.sh" >>crontab
echo "30 15 * * * /var/lib/pgsql/backup.sh" >>crontab


echo "Fin de la instalación"
read fin












