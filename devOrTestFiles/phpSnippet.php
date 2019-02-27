<?php 

/*
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
 
$db = mysql_connect('localhost', 'beacon_user','lem0nheart');
 
function good_query($string, $debug=0) {

    if ($debug == 1) print $string;
    if ($debug == 2) error_log($string);

    $result = mysql_query($string);
 
    if ($result == false) {
        error_log("SQL error: ".mysql_error()."\n\nOriginal query: $string\n");
        // Remove following line from production servers 
        die("SQL error: ".mysql_error()."\b<br>\n<br>Original query: $string \n<br>\n<br>");
    }
    return $result;
}
 

function good_query_list($sql, $debug=0) {
     // this function require presence of good_query() function
     $result = good_query($sql, $debug);
     
     if($lst = mysql_fetch_row($result)) {
         mysql_free_result($result);
         return $lst;
     }
     mysql_free_result($result);
     return false;
}
 
 
 
$result = good_query_list("SELECT * FROM beacon");
 
print_r($result);
 
 
 
 
 
 
 ?>