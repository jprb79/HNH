#!/bin/bash

if [[ "$1"  == 'l4s' ]]
then

	echo "<?php" >includes/clientExtensions.php
	echo "\$features = array();" >>includes/clientExtensions.php
	echo "\$features['l4sEnabled']=true;" >>includes/clientExtensions.php
	echo "\$features['SMSEnabled']=false;" >>includes/clientExtensions.php
	echo "\$features['contactCenter']=false;" >>includes/clientExtensions.php
	echo "\$features['worflowContract'] = false;" >>includes/clientExtensions.php
	echo "?>" >>includes/clientExtensions.php
fi

if [[ "$1"  == 'alcalabc' ]]
then
	echo "<?php" >includes/clientExtensions.php
	echo "\$features = array();" >>includes/clientExtensions.php
	echo "\$features['l4sEnabled']=false;" >>includes/clientExtensions.php
	echo "\$features['SMSEnabled']=false;" >>includes/clientExtensions.php
	echo "\$features['contactCenter']=false;" >>includes/clientExtensions.php
	echo "\$features['worflowContract'] = true;" >>includes/clientExtensions.php
	echo "?>" >>includes/clientExtensions.php
fi

if [[ "$1" == 'all' ]]
then
	echo "<?php" >includes/clientExtensions.php
	echo "\$features = array();" >>includes/clientExtensions.php
	echo "\$features['l4sEnabled']=true;" >>includes/clientExtensions.php
	echo "\$features['SMSEnabled']=true;" >>includes/clientExtensions.php
	echo "\$features['contactCenter']=true;" >>includes/clientExtensions.php
	echo "\$features['worflowContract'] = true;" >>includes/clientExtensions.php
	echo "?>" >>includes/clientExtensions.php
fi
