#!/usr/bin/php
<?php

$commitFiles = getCommitFiles();
// most important thing: is our config parsable, and are sensitive things such as api keys excluded?
$succes = checkConfigFile($commitFiles);
if ($succes) {
	$succes = checkSeleniumFile($commitFiles);
}
$returnVal = 0;
if (!$succes) {
	echo "Invalid commit, stopping now\n";
	$returnVal = 1;
}
exit ( $returnVal ); // 0: succes, 1, otherwise


function getCommitFiles() {
	$commitFiles = [];
	$lines = explode("\n", `git diff --cached --name-status`);
	foreach ($lines AS $line) {
		$cols = explode("\t", $line);
		$file = end($cols);
		if (strlen($file)) {
			$commitFiles[] = $file;
		}
	}
	return $commitFiles;
}
function checkSeleniumFile($commitFiles) {
	$succes = true;
	$configFile = "bin/selenium/selenium.properties";
	if (in_array($configFile, $commitFiles) && file_exists ($configFile)) {
		$props = parse_properties(file_get_contents($configFile));
		if (!$props) {
			echo "Unable to prase selenium properties file\n";
			return false;
		} else {
			foreach ($props as $key => $val) {
				if ($val !== false) {
					echo "Not all properties are empty!\n";
					echo $key." => ".$val."\n";
					return false;
				}
			}
		}
	}
	
	return $succes;
}
function checkConfigFile($commitFiles) {
	$succes = true;
	$configFile = "src/main/webapp/config/config.json";
	if (in_array($configFile, $commitFiles) && file_exists ( $configFile )) {
		$json = json_decode ( file_get_contents ( $configFile ), true );
		if (! $json) {
			echo "Unable to decode json config file\n";
			return false;
		} else {
			if (arrayKeyFilled ( $json, "bitlyApiKey" )) {
				echo "The bitly api key is still in the config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "bitlyUsername" )) {
				echo "The bitly username is still in the config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "googleAnalyticsId" )) {
				echo "The google analytics id is still in the config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "mysqlUsername" )) {
				echo "the mysql username is still in config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "mysqlPassword" )) {
				echo "the mysql password is still in config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "mysqlHost" )) {
				echo "the mysql host is still in config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "githubUsername" )) {
				echo "the github user name is still in config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "githubOathToken" )) {
				echo "the github oath token is still in config file.\n";
				return false;
			}
			if (arrayKeyFilled ( $json, "githubRepo" )) {
				echo "the github repo is still in config file.\n";
				return false;
			}
		}
	}
	
	return $succes;
}
function arrayKeyFilled($array, $key) {
	return (array_key_exists ( $key, $array ) && strlen ( $array [$key] ) > 0);
}
function parse_properties($txtProperties) {
	$result = array ();
	
	$lines = split ( "\n", $txtProperties );
	$key = "";
	
	$isWaitingOtherLine = false;
	foreach ( $lines as $i => $line ) {
		
		if (empty ( $line ) || (! $isWaitingOtherLine && strpos ( $line, "#" ) === 0))
			continue;
		
		if (! $isWaitingOtherLine) {
			$key = substr ( $line, 0, strpos ( $line, '=' ) );
			$value = substr ( $line, strpos ( $line, '=' ) + 1, strlen ( $line ) );
		} else {
			$value .= $line;
		}
		
		/* Check if ends with single '\' */
		if (strrpos ( $value, "\\" ) === strlen ( $value ) - strlen ( "\\" )) {
			$value = substr ( $value, 0, strlen ( $value ) - 1 ) . "\n";
			$isWaitingOtherLine = true;
		} else {
			$isWaitingOtherLine = false;
		}
		
		$result [$key] = $value;
		unset ( $lines [$i] );
	}
	
	return $result;
}
	
	
