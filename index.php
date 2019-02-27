<?php
// error_reporting( E_ALL );
// require_once 'beadle.php';
require "varDx.php";

// initialization
// all "time" in seconds and relative to time();
$states = "00001111";
$previous_states = "00001111";
$time_now = time();
$time_of_last_change = $time_now;
$array_of_states = str_split(substr($states, 0,8));
$array_of_previous_states = str_split(substr($previous_states, 0,8));

$are_we_in_the_middle_of_a_drama = FALSE;
$time_now_scale = 1; // EDITABLE for development values up to 20 will speed up the beacon
$minimum_duration = 20 / $time_now_scale;
$possible_durations_for_dramatic_states = array(120 / $time_now_scale, 150 / $time_now_scale, 180 / $time_now_scale);

$time_for_dramatic_change	= $time_now + $possible_durations_for_dramatic_states[rand(0, 2)];
$time_for_next_change = $time_now + calcNextDuration($array_of_states);

$dx = new \varDx\cDX; //create an object
$dx->def("beacon.dat.php"); //define data file

// one-time writing code to set up the dat file`
// function makeDataFile($filename){
//     file_put_contents($filename, base64_decode("PD9waHANCl9faGFsdF9jb21waWxlcigpOw0KDQo="), FILE_APPEND);
// }
// makeDataFile("beacon.dat.php");
// $dx = new \varDx\cDX; //create an object
// $dx->def("beacon.dat.php"); //define data file
// $dx->write("beacon_states", $states);
// $dx->write("previous_states", $previous_states);
// $dx->write("time_of_last_change", $time_of_last_change);
// $dx->write("time_for_next_change", $time_for_next_change);
// $dx->write("time_for_dramatic_change", $time_for_dramatic_change);
// echo "done ...";

$command_state = 0;	// TODO default
$need_to_output_the_old_outmoded_QuickTime_list = TRUE;
$json = FALSE;
$verbose = FALSE;
$do_first_set = FALSE;
$do_second_set = FALSE;
$stop_beacon = FALSE;

// ---- SERVER CONTROL ----

if (isset($_SERVER['QUERY_STRING'])) {
	// echo $_SERVER['QUERY_STRING'];
	if (isset($_GET['p'])) {
		// "p" for parameter
		$parameter = $_GET['p'];
		switch ($parameter) {
			case 'v': // verbose
				$verbose = TRUE;
				break;
			case 'j': // TODO for JSON
				$verbose = FALSE;
				$need_to_output_the_old_outmoded_QuickTime_list = FALSE;
				$json = TRUE;
				break;
			case 'k': // TODO for light house keeper lhk
				$verbose = TRUE;
				$need_to_output_the_old_outmoded_QuickTime_list = FALSE;
				break;
		}
	}

	if ( isset($_GET['a']) || isset($_GET['b']) ) {
		// mild security: check that a command giver has this key:
		if ( isset( $_GET['k']) ) {
			$parameter = $_GET['k'];
			// do the commands
			if ($parameter == 'keeper' || $parameter == "keeper") {
				// accept the instructions
				$command_state = 1;
				if ( isset( $_GET['a']) ) {
					$do_first_set = TRUE;
					$parameter = $_GET['a'];
					$first_set_value = intval($parameter);
					if ($first_set_value > 9) {
						$first_set_value = 0;
					}
				}
				if ( isset( $_GET['b']) ) {
					$do_second_set = TRUE;
					$parameter = $_GET['b'];
					$stop_beacon = FALSE;
					switch ($parameter) {
						case 'sink':
							$second_set_value = 2;
							break;
						case 'float':
							$second_set_value = 0;
							break;
						case 'surface':
							$second_set_value = 1;
							break;
						case '9' :
							$stop_beacon = TRUE;
							break;
						default:
							$second_set_value = intval($parameter);
//							if ($second_set_value == 9) {
//								$stop_beacon = TRUE;
//							}
//							else {
//								$stop_beacon = FALSE;
//							}
							if ($second_set_value > 2 || $second_set_value < 0) {
								$second_set_value = 0;
							}
					}
				}
			}
		}
	}
}
// ----

// grammar-like associative array
$grammar_rules_for_changes_of_states = array(
"<00>"=>"0|0|1|2",
"<02>"=>"0|2|2",
"<01>"=>"0|1|1",
"<20>"=>"0|0|1|1|1|2",
"<22>"=>"0|0|2|2|2|2",
"<21>"=>"0|0|1|1|1",
"<10>"=>"0|0|1|2|2",
"<12>"=>"0|2|2|2",
"<11>"=>"0|1|1|1",

"<000>"=>"0|0|0|0|1|1|2|2|3|4|5",
"<020>"=>"0|0|1|1|2|2|3|4|5",
"<010>"=>"0|0|0|0|1|1|2|2|3|4|5",
"<200>"=>"0|0|1|1|2|2|3|4|5",
"<220>"=>"0|0|1|1|2|2|3|4|5",
"<210>"=>"0|0|0|0|1|1|2|2|3|4|5",
"<100>"=>"0|0|1|1|2|2|3|4|5",
"<120>"=>"0|0|1|1|2|2|3|4|5",
"<110>"=>"0|0|0|0|0|0|0|0|1|1|2|2|3|4|5",

"<001>"=>"0|0|1|1|1|1|2|2|3|4|5",
"<021>"=>"0|0|1|1|2|2|3|4|5",
"<011>"=>"0|0|1|1|1|1|2|2|3|4|5",
"<201>"=>"0|0|1|1|2|2|3|4|5",
"<221>"=>"0|0|1|1|2|2|3|4|5",
"<211>"=>"0|0|1|1|1|1|2|2|3|4|5",
"<101>"=>"0|0|1|1|2|2|3|4|5",
"<121>"=>"0|0|1|1|2|2|3|4|5",
"<111>"=>"0|0|1|1|1|1|1|1|1|1|2|2|3|4|5",

"<002>"=>"0|0|1|1|2|2|2|2|3|4|5",
"<022>"=>"0|0|1|1|2|2|3|4|5",
"<012>"=>"0|0|1|1|2|2|2|2|3|4|5",
"<202>"=>"0|0|1|1|2|2|3|4|5",
"<222>"=>"0|0|1|1|2|2|3|4|5",
"<212>"=>"0|0|1|1|2|2|2|2|3|4|5",
"<102>"=>"0|0|1|1|2|2|3|4|5",
"<122>"=>"0|0|1|1|2|2|3|4|5",
"<112>"=>"0|0|1|1|2|2|2|2|2|2|2|2|3|4|5",

"<003>"=>"0|1|2|3|3|3|3|4|5",
"<023>"=>"0|1|2|3|3|4|5",
"<013>"=>"0|1|2|3|3|3|3|4|5",
"<203>"=>"0|1|2|3|3|4|5",
"<223>"=>"0|1|2|3|3|4|5",
"<213>"=>"0|1|2|3|3|3|3|4|5",
"<103>"=>"0|1|2|3|3|4|5",
"<123>"=>"0|1|2|3|3|4|5",
"<113>"=>"0|1|2|3|3|3|3|4|5",

"<004>"=>"0|1|2|3|4|4|4|4|5",
"<024>"=>"0|1|2|3|4|4|5",
"<014>"=>"0|1|2|3|4|4|4|4|5",
"<204>"=>"0|1|2|3|4|4|5",
"<224>"=>"0|1|2|3|4|4|5",
"<214>"=>"0|1|2|3|4|4|4|4|5",
"<104>"=>"0|1|2|3|4|4|5",
"<124>"=>"0|1|2|3|4|4|5",
"<114>"=>"0|1|2|3|4|4|4|4|5",

"<005>"=>"0|1|2|3|4|5|5|5|5",
"<025>"=>"0|1|2|3|4|5|5",
"<015>"=>"0|1|2|3|4|5|5|5|5",
"<205>"=>"0|1|2|3|4|5|5",
"<225>"=>"0|1|2|3|4|5|5",
"<215>"=>"0|1|2|3|4|5|5|5|5",
"<105>"=>"0|1|2|3|4|5|5",
"<125>"=>"0|1|2|3|4|5|5",
"<115>"=>"0|1|2|3|4|5|5|5|5");

// ---- DATABASE ACCESS AND CALCULATION -- NOT USED! ----
// get the stored times and states
// $mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_database);
// if ($mysqli->connect_errno) {
//     echo "Failed to connect to MySQL: " . $mysqli->connect_error;
// }
//

// get current state from the memory store
// $result_of_query = $mysqli->query("SELECT * FROM beacon_data");
// $row = $result_of_query->fetch_assoc();
// print_r($row); // DEBUG prints the array of field/column names and values
// from DATABASE:
$states = $dx->read("beacon_states");
$previous_states = $dx->read("previous_states");
$time_of_last_change = $dx->read("time_of_last_change");
$time_for_next_change = $dx->read("time_for_next_change");
$time_for_dramatic_change	= $dx->read("time_for_dramatic_change");

$array_of_states = str_split(substr($states, 0,8));
$array_of_previous_states = str_split(substr($previous_states, 0,8));

echo "got here ... " . "\n";

// do we have a command to carry out?
if (1 == $command_state) {
	$are_we_in_the_middle_of_a_drama = TRUE;
	if ($do_first_set) {
		for ($i = 0; $i < 4; $i++) {
			$array_of_states[$i] = $first_set_value;
		}
	}
	if ($do_second_set && (FALSE == $stop_beacon)) {
		for ($i = 4; $i < 8; $i++) {
			$array_of_states[$i] = $second_set_value;
		}
	}
	$drama_duration = $possible_durations_for_dramatic_states[rand(0, 2)];
	$time_for_dramatic_change = $time_now + $drama_duration;
	doNewDurationAndPreviousStates();
	storeStates();
}
// else check to see if we have a calculation to do
elseif($time_now >= $time_for_next_change) {
	// echo "time for new states" . "<br>"; // DEBUG
	// check to see if there should be a 'dramatic' change of states
	if($time_now >= $time_for_dramatic_change) {
		$are_we_in_the_middle_of_a_drama = TRUE;
		// echo "time for new drama" . "<br>"; // DEBUG
		$array_of_states = doDramaValues($array_of_states);
		// choose one of the three possible times until
		// a dramatic change: 120, 150 or 180 seconds
		$drama_duration = $possible_durations_for_dramatic_states[rand(0, 2)];
		$time_for_dramatic_change = $time_now + $drama_duration;
		// dramatic change of states
	}
	else {
		// generate new values for next state
		$array_of_states = doNextStateValues($array_of_states);
	}

	doNewDurationAndPreviousStates();

	storeStates();

} // end of time for a change

function doNewDurationAndPreviousStates() {
	global $array_of_states, $are_we_in_the_middle_of_a_drama, $stop_beacon;
	global $time_for_next_change, $time_now, $time_for_next_change, $new_last_time, $previous_states, $states;
	$duration = calcNextDuration($array_of_states);
	if ($are_we_in_the_middle_of_a_drama) {
		// double time until next change if there's
		// been a dramatic change
		$duration *= 2;
		$are_we_in_the_middle_of_a_drama = FALSE;
	}

	// to stop the beacon for 15 mins:
	if ($stop_beacon) {
		$duration = 900;
	}

	$time_for_next_change = $time_now + $duration;

	$new_last_time 	= $time_now;
	// update previous_states
	$previous_states = $states;
	// now change the states for updating the db
	$states = implode($array_of_states);
}

function storeStates() {
	// global $mysqli;
	global $dx;
	global $time_for_next_change, $time_for_dramatic_change, $time_for_next_change, $new_last_time, $previous_states, $states;
	// store new values
	// $mysqli->prepare(
	//      "UPDATE `beacon_data` SET `beacon_states`=\"$states\", `previous_states`=\"$previous_states\", `last_computation`=$new_last_time, `next_computation`=$time_for_next_change, `next_dramatic`=$time_for_dramatic_change WHERE id = 1"
	// )->execute();
	$dx->modify("beacon_states", $states);
	$dx->modify("previous_states", $previous_states);
	$dx->modify("time_of_last_change", $time_of_last_change);
	$dx->modify("time_for_next_change", $time_for_next_change);
	$dx->modify("time_for_dramatic_change", $time_for_dramatic_change);
}
// ----

// ---- WRITE QLIST ----

// top line, with no headers, for compatibility with QuickTime 'listening movies'
if ($need_to_output_the_old_outmoded_QuickTime_list) {
	echo makeQuickTimeList($command_state, $array_of_states);
}
// nine numbers presented as a QList (QuickTime List)
// The first number - default 0 - will be set to correspond with 'commands'
// sent to the server (this php) as query strings appended to the URL

// ---- HTML GENERATED IF ?p=v (parameter = verbose) ----

$associations = array(
"beacon_states" => $states,
"previous_states" => $previous_states,
"time_of_last_change" => $time_of_last_change,
"time_for_next_change" => $time_for_next_change,
"time_for_dramatic_change" => $time_for_dramatic_change);

// , "previous_states" => $previous_states, "last_computation" => $new_last_time, "next_computation" => $time_for_next_change, "next_dramatic" => $time_for_dramatic_change];

if ($json) {
	echo $_GET['callback'] . "(" . json_encode($associations) . ")";
}

if ($verbose) {
	// a simple webpage showing the state of the beacon
	echo "<br><h3>Current States</h3>";

	// TEST/DEVELOPMENT HTLML

	echo "<pre>";

	// TEST echo $duration;

	echo "\n";

	print_r( str_split($states) );

	// echo "grammar_rules_for_changes_of_states:\n"; // DEBUG
	// print_r($grammar_rules_for_changes_of_states); // DEBUG

	// TEST $arr = array(1, 2, 3, 4, 5, 6, 7, 8);

	// TEST echo "\n";

	// TEST echo implode($arr);

	echo "seconds until next change of states: " . ($time_for_next_change - time()) . "\n";
	echo "second until next dramatic change: " . ($time_for_dramatic_change - time()) . "\n";

	echo "</pre>";

}
// ----

// ---- FUNCTIONS ----

function makeQuickTimeList($c, $s) {
	$new_string = "<n>" . $c . "</n>";
	foreach ($s as $n) {
		$new_string = $new_string . "<n>" . $n . "</n>";
	}
	return $new_string;
}

function doDramaValues($sa) {
	$r = 9;
	while (notInSet($r,$sa,0,3)) $r = rand(0, 5);
	// the first four items will be a randomly choosen
	// value at least one instance of which occured
	// for one of these items in the previous state
	for ($i = 0; $i < 4; $i++) {
		// deal with first four elements
		$sa[$i] = $r;
	}
	$r = 8;
	while (notInSet($r,$sa,4,7)) $r = rand(0, 2);
	// the second four items will be a randomly choosen
	// value at least one instance of which occured
	// for one of these items in the previous state
	for ($i = 4; $i < 8; $i++) {
		// deal with next four elements
		$sa[$i] = $r;
	}
	return $sa;
}

function notInSet($n,$sa,$s,$e) {
	for ($i = $s; $i < $e+1; $i++) {
		if ($sa[$i] == $n) {
			return FALSE;
		}
	}
	return TRUE;
}

function doNextStateValues($sa) {
	for ($i = 0; $i < 4; $i++) {
		// deal with first four elements
		$sa[$i] = getFirstSetValue($sa,$i);
	}
	for ($i = 4; $i < 8; $i++) {
		// deal with next four elements
		$sa[$i] = getSecondSetValue($sa,$i);
	}
	return $sa;
}

function getFirstSetValue($sa,$i) {
	global $array_of_previous_states,$grammar_rules_for_changes_of_states; // the set before $sa
	$key = "<" . $array_of_previous_states[$i+4] . $sa[$i+4] . $sa[$i] . ">";
	// echo $key . "<br>"; // DEBUG
	$v = $grammar_rules_for_changes_of_states[$key];
	$r_array = preg_split("[\|]",$v);
	// print_r($r_array); // DEBUG
	// echo "<br>"; // DEBUG
	return $r_array[rand(0, count($r_array)-1)];
}

function getSecondSetValue($sa,$i) {
	global $array_of_previous_states,$grammar_rules_for_changes_of_states; // the set before $sa
	$key = "<" . $array_of_previous_states[$i] . $sa[$i] . ">";
	// echo $key . "<br>"; // DEBUG
	$v = $grammar_rules_for_changes_of_states[$key];
	$r_array = preg_split("[\|]",$v);
	// print_r($r_array); // DEBUG
	// echo "<br>"; // DEBUG
	return $r_array[rand(0, count($r_array)-1)];
}

function calcNextDuration($sa) {
	global $minimum_duration, $time_now_scale;
	$duration = $minimum_duration;
	for ($i = 4; $i < 8; $i++) {
		// add 5 seconds duration for every value 1
		// amongst items 4 thru 7 and
		if ($sa[$i] == 1) $duration += (5 / $time_now_scale);
		// add 5 seconds duration for every value 1
		// amongst items 4 thru 7 and
		if ($sa[$i] == 2) $duration += (2.5 / $time_now_scale);
	}
	// $duration = round($duration);
	return $duration;
}
// ----

/*

(Ian H's notes:)

MYSQL database
  timestamp of last computation
  timestamp of next computation
  8 cached values

PHP
  grab database $
  looks at current time
  compares $

    returns cached values

    computes new values and caches them
 */


?>
