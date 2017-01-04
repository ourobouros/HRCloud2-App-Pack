<?php

/*//
HRCLOUD2-PLUGIN-START
App Name: PHP-AV
App Version: 1.0.2-1.0 (1-3-2017 00:00)
App License: GPLv3
App Author: FujitsuBoy (aka Keyboard Artist) & zelon88
App Description: A simple HRCloud2 App for scanning files for viruses.
App Integration: 0 (False)
App Permission: 0 (Admin)
HRCLOUD2-PLUGIN-END
//*/
?>
<script type="text/javascript">
    function Clear() {    
      document.getElementById("AVScanTarget").value= ""; }
    function toggle_visibility(id) {
      var e = document.getElementById(id);
      if(e.style.display == 'block')
         e.style.display = 'none';
      else
         e.style.display = 'block'; }
    function goBack() {
      window.history.back(); }
    </script>
<div align="center"><h3>PHP-AV</h3><hr /></div>
<?php
// PHP ANTI-VIRUS v1.0.2
// Written by FujitsuBoy (aka Keyboard Artist)

// If you have any comments or suggestions please let FujitsuBoy know:
// keyboardartist@users.sourceforge.net

// / The following code loads needed HRCloud2 features and functions.
require('/var/www/html/HRProprietary/HRCloud2/config.php');
require('/var/www/html/HRProprietary/HRCloud2/commonCore.php');

// / The following code checks if App permission is set to '1' and if the user is an administrator or not.
$initData = file_get_contents('PHP-AV.php');
if (strpos($initData, 'App Permission: 0') == 'true' or strpos($initData, 'App Permission: Admin') == 'true'
  or strpos($initData, 'App Permission: admin') == 'true') {
  if ($UserIDRAW !== '1') {
  	$txt ('ERROR!!! HRC2PHPAVApp28, A non-administrator attempted to execute the PHP-AV App on '.$Time.'!'); 
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);  
    die($txt); } }
if (!isset($_POST['AVScan'])) { ?>
<div align="center">
<br>
<p style="text-align:left; margin:15px;">PHP-AV is an open-source server-side anti-virus and vulnerability detection tool 
	written in PHP developed by FujitsuBoy (aka Keyboard Artist) and heavily modified by zelon88.</p>
<p style="text-align:left; margin:15px;">This tool will scan for dangerous files, malicious file-contents,  
	active vulnerabilities and potentially dangerous scripts and exploits.</p>
<br>
<button onclick="toggle_visibility('Options');">Options</button>
<form type="multipart/form-data" action="PHP-AV.php" method="POST">
<div name="Options" id="Options" style="display:none;">
<a style="max-width:75%;"><hr /></a>
<p>Specify a Cloud directory/filename: </p><input type="text" name="AVScanTarget" id="AVScanTarget" value="">
<a style="max-width:75%;"><hr /></a>
</div>
<br>
<input type="submit" name="AVScan" id="AVScan" value="Scan Server"></form>
</div>
<?php }
if (isset($_POST['AVScan'])) {
// default configuration
$CONFIG = Array();
$CONFIG['debug'] = 0;
$CONFIG['scanpath'] = $_SERVER['DOCUMENT_ROOT'];
$CONFIG['extensions'] = Array();
$debug = null;
if (isset($_POST['AVScanTarget'])) {
$CONFIG['scanpath'] = str_replace(' ', '\ ', str_replace(str_split('[]{};:$!#^&%@>*<'), '', $_POST['AVScanTarget'])); }
if (!isset($_POST['AVScanTarget']) or $_POST['AVScanTarget'] == '') {
$CONFIG['scanpath'] = $_SERVER['DOCUMENT_ROOT']; }
// attempt to load configuration file
include('config.php');

function file_scan($folder, $defs, $debug) {
	// hunts files/folders recursively for scannable items
	global $dircount, $report;
	$dircount++;
	if ($debug)
		$report .= '<p class="d">Scanning folder $folder ...</p>';
	if ($d = @dir($folder)) {
		while (false !== ($entry = $d->read())) {
			$isdir = @is_dir($folder.'/'.$entry);
			if (!$isdir and $entry!='.' and $entry!='..') {
				virus_check($folder.'/'.$entry,$defs,$debug); } 
			elseif ($isdir  and $entry!='.' and $entry!='..') {
				file_scan($folder.'/'.$entry,$defs,$debug); } }
		$d->close(); } }

function virus_check($file, $defs, $debug) {
	global $filecount, $infected, $report, $CONFIG;
	// find scannable files
	$scannable = 0;
	foreach ($CONFIG['extensions'] as $ext) {
		if (substr($file,-3)==$ext)
			$scannable = 1; }
	// compare against defs
	if ($scannable) {
		// affectable formats
		$filecount++;
		$data = file($file);
		$data = implode('\r\n', $data);
		$clean = 1;
		foreach ($defs as $virus) {
			if (strpos($data, $virus[1])) {
				// file matches virus defs
				$report .= '<p class="r">Infected: ' . $file . ' (' . $virus[0] . ')</p>';
				$infected++;
				$clean = 0; } }
		if (($debug)&&($clean))
			$report .= '<p class="g">Clean: ' . $file . '</p>'; } }

function load_defs($file, $debug) {
	// reads tab-delimited defs file
	$defs = file($file);
	$counter = 0;
	$counttop = sizeof($defs);
	while ($counter < $counttop) {
		$defs[$counter] = explode('	', $defs[$counter]);
		$counter++; }
	if ($debug)
		echo '<p>Loaded ' . sizeof($defs) . ' virus definitions</p>';
	return $defs; }

function check_defs($file) {
	// check for >755 perms on virus defs
	clearstatcache();
	$perms = substr(decoct(fileperms($file)),-2);
	if ($perms > 55)
		return false;
	else
		return true; }

function renderhead() {
?>
<html>
<head>
<title>Virus scan</title>
<style type="text/css">
h1 {
	font-family: arial; }

p {
	font-family: arial;
	padding: 0;
	margin: 0;
	font-size: 10px; }

.g {
	color: #009900; }

.r {
	color: #990000;
	font-weight: bold; }

.d {
	color: #ccc; }

#summary {
	border: #333 solid 1px;
	background: #f0efca;
	padding: 10px;
	margin: 10px; }

#summary p {
	font-size: 12px; }
</style>
</head>
<body>
<?php }

// declare variables
$report = '';
// output html headers
renderhead();
// set counters
$dircount = 0;
$filecount = 0;
$infected = 0;
// load virus defs from flat file
if (!check_defs('virus.def'))
	trigger_error("Virus.def vulnerable to overwrite, please change permissions", E_USER_ERROR);
$defs = load_defs('virus.def', $debug);
// scan specified root for specified defs
file_scan($CONFIG['scanpath'], $defs, $CONFIG['debug']);
// output summary
echo '<h2>Scan Completed</h2>';
echo '<div id=summary>';
echo '<p><strong>Scanned folders:</strong> ' . $dircount . '</p>';
echo '<p><strong>Scanned files:</strong> ' . $filecount . '</p>';
echo '<p class=r><strong>Infected files:</strong> ' . $infected . '</p>';
echo '</div>';
// output full report
echo $report; } ?>
</body>
</html>