<?php
//
// Example PHP program for listing files and folders.
//

// Set directory to list.
$DIR='.';

// Define template for constructing link to a file.
// The variables $dir and $file get substituted with the directory name and file name.
$DIRLIST_LINK_TEMPLATE = '<a href="$dir/$file">$file</a>';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
<title>Tree Menu - Make menus that expand when clicked</title>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="description" content="TreeMenu creates tree menus out of UL/LI tags that pop open when clicked.">
<meta name="author" content="Mack Pexton">

<link rel="stylesheet" href="acmebase.css" type="text/css">


<style type="text/css">
/* Menu container */
.menu	{
	position:relative;
	width:320px;
	min-height:100px;
	border:solid #FF9900 1px;
	padding:10px 5px 10px 5px;
	margin:12px 12px 12px 50px;
	_width:364px;
	_height:100px;
	}

/* Menu styles */
.menu ul
	{
	margin:0px;
	padding:0px;
	text-decoration:none;
	}
.menu li
	{
	margin:0px 0px 0px 5px;
	padding:0px;
	list-style-type:none;
	text-align:left;
	font-family:Arial,Helvetica,sans-serif;
	font-weight:normal;
	font-size:13px;
	line-height:18px;
	}

/* Submenu styles */
.menu ul ul 
	{ }
.menu li li
	{ margin:0px 0px 0px 16px; }
.menu li li.open,
.menu li li.close
	{ margin:0px 0px 0px 16px; }

/* Symbol styles */
.menu .symbol-gif,
.menu .symbol-png,
.menu .symbol-jpg,
.menu .symbol-txt,
.menu .symbol-zip,
.menu .symbol-html,
.menu .symbol-file,
.menu .symbol-item
	{
	float:left;
	width:18px;
	height:16px;
	background-position:left center;
	background-repeat:no-repeat;
	}
.menu .symbol-gif   { background-image:url(icons/gif.png); }
.menu .symbol-png   { background-image:url(icons/gif.png); }
.menu .symbol-jpg   { background-image:url(icons/jpeg.png); }
.menu .symbol-txt   { background-image:url(icons/txt.png); }
.menu .symbol-zip   { background-image:url(icons/zip.png); }
.menu .symbol-html  { background-image:url(icons/html.png); }
.menu .symbol-file  { background-image:url(icons/file.png); }
.menu .symbol-item  { background-image:url(icons/page.png); }
.menu .symbol-open,
.menu .symbol-close
	{
	float:left;
	width:34px;
	height:16px;
	background-position:left center;
	background-repeat:no-repeat;
	}
.menu .symbol-close { background-image:url(icons/plus-folder-closed.png); }
.menu .symbol-open  { background-image:url(icons/minus-folder-open.png); }
.menu .symbol-item.last  { }
.menu .symbol-close.last { }
.menu .symbol-open.last  { }

/* Menu line styles */
.menu li.item  { font-weight:normal; }
.menu li.close { font-weight:normal; }
.menu li.open  { font-weight:bold; }
.menu li.item.last  { }
.menu li.close.last { }
.menu li.open.last  { }

/* Show All / Hide All buttons */
.buttons
	{
	position:absolute;
	left:250px;
	top:10px;
	}
</style>

<script src="TreeMenu.js" type="text/javascript"></script>
</head>



<body>
<a name="top"></a>
<div align="center"><div class="page">
<div class="banner"><a href="http://www.acmebase.org"><img src="acmebase-banner-page.gif" alt="Home" /></a></div>
<div class="content">

<!-- Header -->
<h1>Tree Menu <span class="version">v1.4</span>
<span class="author"><a href="&#109;ailto&#58;mack&#64;acmebase&#46;org?subject=TreeMenu%20v1.4">Mack Pexton</a></span>
<span class="date">July 13, 2006</span></h1>

<div class="firstbr"></div>

<a name="introduction" href="#top" class="h2br" title="Top of page."><img src="top.gif" border="0" alt="Top"></a>
<h2>File Listing</h2>


<p>This is a PHP program demonstrating the use of <a href="index.htm">TreeMenu</a> to produce a list of files and folders.
The directory listed by the program is hardwired to be the folder where the program is located, but that could easily be changed.</p>




<div class="menu">
<ul id="folder_list"><?php
////////////////////////////////////////////////////////////////////////////////

/*
 * List folders and files.
 */

// Retrieve menu states from cookie.
$menu_states = split('x',$_COOKIE['tm_folder_list']);
$menu_index = 0;

function dirlist($dir='.', $currdir='.', $prefix='  ') {
	// Recursive function to list out directories, subdirectories, and their files.

	global $DIRLIST_LINK_TEMPLATE;
	global $menu_index;

	$dirpath = realpath($currdir . '/' . $dir);
	if ($dh = @opendir($dirpath)) {
		// Fill array with file names.
		$files = array();
		while (($file = readdir($dh)) !== false) {
			if ($file == '.') continue;
			if ($file == '..') continue;

			////////////////////////
			// Filter files here. //
			////////////////////////
			// no filters...

			$files[] = $file;
		}
		closedir($dh);

		sort($files,SORT_STRING);

		// List directories first
		for ($i=0; $i<count($files); $i++) {
			$file = $files[$i];
			if (! is_dir($dirpath.'/'.$file)) continue;

			$symbol = menu_symbol($menu_index);
			$class = ' class="'.menu_li_class($menu_index).'"';
			$style = ' style="'.menu_ul_style($menu_index).'"';
			$menu_index++;

			print "\n$prefix<li$class>$symbol$file";
			print "\n$prefix  <ul$style>";

			// Recursive call.
			dirlist($file, ($currdir ? "$currdir/" : '').$dir, $prefix.'    ');

			print "\n$prefix  </ul>";
			print "\n$prefix</li>";
		}

		// List files next
		for ($i=0; $i<count($files); $i++) {
			$file = $files[$i];
			if (is_dir($dirpath.'/'.$file)) continue;

			$symbol = '<span class="'.get_file_type($file).'"></span>';

			if ($DIRLIST_LINK_TEMPLATE) {
				$link = $DIRLIST_LINK_TEMPLATE;
				$link = preg_replace('/\$file/',$file,$link);
				$linkdir = $currdir.'/'.$dir;
				$linkdir = preg_replace('|^(\./)+|','',$linkdir);
				$link = preg_replace('/\$dir/',$linkdir,$link);
			}
			else {
				$link = $file;
			}

			print "\n$prefix<li>$symbol$link</li>";
		}
		
	}
	else {
		//print "\n<br>Cannot open $dirpath.";

		$symbol = '<span class="symbol-error"></span>';
		print "\n$prefix<li>$symbol$dir</li>";
	}
}
function menu_symbol($menu_index) {
	// Return the appropriate symbol for the next menu.

	global $menu_states;

	if ($menu_states[$menu_index]) {
		return '<span class="symbol-open" onclick="TreeMenu.toggle(this)"></span>';
	}
	else {
		return '<span class="symbol-close" onclick="TreeMenu.toggle(this)"></span>';
	}
}

function menu_li_class($menu_index) {
	// Return the appropriate open/closed style for menu.

	global $menu_states;

	if ($menu_states[$menu_index]) {
		return 'open';
	}
	else {
		return 'close';
	}
}

function menu_ul_style($menu_index) {
	// Return the appropriate open/closed style for menu.

	global $menu_states;

	if ($menu_states[$menu_index]) {
		return 'display:block;';
	}
	else {
		return 'display:none;';
	}
}

function get_file_type($file) {
	// Return a class name for the different types of files.

	$ext = strtolower(preg_replace('/.*\./','',$file));
	switch ($ext) {
	case 'gif':	return 'symbol-gif';
	case 'png':	return 'symbol-png';
	case 'jpg':	return 'symbol-jpg';
	case 'jpeg':	return 'symbol-jpg';
	case 'zip':	return 'symbol-zip';
	case 'gz':	return 'symbol-zip';
	case 'html':	return 'symbol-html';
	case 'htm':	return 'symbol-html';
	case 'php':	return 'symbol-html';
	case 'css':	return 'symbol-html';
	case 'js':	return 'symbol-html';
	case 'txt':	return 'symbol-txt';
	default:	return 'symbol-file';
	}
}


// Print initial folder
$symbol = menu_symbol($menu_index);
$class = ' class="'.menu_li_class($menu_index).'"';
$style = ' style="'.menu_ul_style($menu_index).'"';
$menu_index++;

$file = basename(realpath($DIR));
print "\n$prefix<li$class>$symbol$file";
print "\n$prefix  <ul$style>";


print dirlist($DIR);


print "\n$prefix".'  </ul>';
print "\n$prefix".'</li>';
////////////////////////////////////////////////////////////////////////////////
?>

</ul>
<script type="text/javascript">make_tree_menu('folder_list',1,0,0,1)</script>

<script type="text/javascript">
/*
 * Scripts to set file folder icons.
 *
 * These are supplemental functions assisting TreeMenu. They are needed only
 * because the symbol tags were not added by TreeMenu.
 *
 * The set_class_all() function supports TreeMenu.show_all() and TreeMenu.hide_all()
 * functions by setting the className attribute on all the symbol tags added each
 * line.
 *
 * The restore_class_all() function searches the tree menu for all the symbol tags
 * added to the list and sets their className attributes appropriately. This function
 * is needed to ensure the symbols are set properly when the user refreshes their
 * browser or returns to a page with their back button.
 *
 */

function set_class_all(e,symboltagname,oldclassname,newclassname) {
	// Support function to alter all the open-folder/closed-folder symbols.
	e = TreeMenu.get_ref(e);
	tags = e.getElementsByTagName(symboltagname.toUpperCase());
	for (var i = 0; i < tags.length; i++) {
		if (tags[i].className == oldclassname) tags[i].className = newclassname;
	}
}

function restore_class_all(e,symboltagname,closeclassname,openclassname) {
	// Set symbols' className to correspond to the menu state.
	e = TreeMenu.get_ref(e);
	var m = TreeMenu.menus[TreeMenu.get_top_ul(e).id];
	var states = m.get_menu_states();

	tags = e.getElementsByTagName(symboltagname.toUpperCase());
	menu_index = 0;
	for (var i = 0; i < tags.length; i++) {
		if (tags[i].className == closeclassname || tags[i].className == openclassname) {
			if (states[menu_index] == '1')	tags[i].className = openclassname;
			else				tags[i].className = closeclassname;
			menu_index++;
		}
	}
}
restore_class_all('folder_list','SPAN','symbol-close','symbol-open')
</script>



<div class="buttons">
<button onclick="TreeMenu.show_all(document.getElementById('folder_list'));set_class_all('folder_list','SPAN','symbol-close','symbol-open')">Show All</button><br />
<button onclick="TreeMenu.hide_all(document.getElementById('folder_list'));set_class_all('folder_list','SPAN','symbol-open','symbol-close')">Hide All</button><br />
<button onclick="TreeMenu.reset(document.getElementById('folder_list'));location.reload();">Reset</button><br />
</div>

</div>







<p>In this example, instead of letting the Javascript add the file and
folder icons like normal, they are added on the server side so that
the icons for different file types could be easily determined. Adding
the icons on the server side requires an extra support function for the
<code>TreeMenu.show_all()</code> and <code>TreeMenu.hide_all()</code>
functions in order to properly change the state of the added icons.
It also requires a custom function to set the initial state of the
symbols when ever the page is rendered. This page contains examples
of both functions.</p>


<h2>Download</h2>

<p>A zip file of this document and the the TreeMenu JavaScript source code can be downloaded here:
<a href="http://www.acmebase.org/tree_menu/TreeMenu-1.4.zip">TreeMenu-1.4.zip</a></p>

<p>A compressed commercial version of TreeMenu (only 4.8KB) can be ordered at:
<a href="http://order.acmebase.com/order.php?p=acmetreemenu">order.acmebase.com</a></p>





<a name="bottom" href="#top" class="lastbr" title="Top of page."><img src="top.gif" border="0" alt="Top"></a>
</div>


<div class="footer">
</div>
</div></div>

</body>
</html>
