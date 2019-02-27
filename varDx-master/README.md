# varDx
Simple PHP flat-file data storage library  
Current version: `1.4`  
License: `MIT License`  

## About
varDx is a tiny library (~2 KB; <100 lines) that allows you to store data (objects) in files in the form of "keys", which you can later read and modify.  
It was initially developed primarily to facilitate sharing of data between independent PHP script and sessions, but it can be used to create and manage simple databases.

## Requirements
1. PHP 5.6+

## Installation
1. Save `varDx.php` on your server. You can rename it.  
2. Add this to the top of all your files: `require 'varDx.php';`

## Keys
Because varDx uses [`serialize()`](https://secure.php.net/manual/en/function.serialize.php), the keys can be of any data type or structure.

## Functions
### `def(<filename>)`
Defines the file in which all data is to be stored. Should be called before using any other functions. If the file doesn't exist, it will be created when data is first written to it using `write()` or `modify()`.

### `write(<key_name>, <key_value>)`
Writes the value to the file. Creates the file if it doesn't exist. Returns `ERR_DX_KEY_ALREADY_EXISTS` if the key already exists in the file.

### `modify(<key_name>, <key_value>)`
Changes the value of the key in the file. Creates the file if it doesn't exist. Creates key if it doesn't already exist in the file.

### `read(<key_name>)`
Reads the value of the key from the file and returns it. Returns `ERR_DX_KEY_NOT_FOUND` if the key doesn't exist in the file. Returns `ERR_DX_FILE_DOES_NOT_EXIST` if the file doesn't exist.

### `check(<key_name>)`
Checks if the key exists in the file, and returns `true` if it does. Returns `ERR_DX_KEY_NOT_FOUND` if the key doesn't exist in the file. Returns `ERR_DX_FILE_DOES_NOT_EXIST` if the file doesn't exist.

### `del(<key_name>)`
Checks if the key exists in the file, and deletes it if it does. Returns `ERR_DX_FILE_DOES_NOT_EXIST` if the file doesn't exist.


## Usage
```
<?php
require 'varDx.php';
$dx = new \varDx\cDX; //create an object
$dx->def('file2.dat'); //define data file
$val1 = "this is a string";
$dx->write('data1', $val1); //writes key to file
echo $dx->read('data1'); //returns key value from file
```
See [this gist](https://gist.github.com/rahuldottech/3ad60944374c6aaf657588787dd0bdcd) for more examples.

## Data File Format
The keys are stored in this format in the data file:
```
keyname__=__urlencode(serialize(value_of_key))
```
If you're adding keys manually, note that all keys must be on separate newlines!  
Lines which are empty or don't match this format are automatically skipped.

This format also allows for data to be easily read by applications or scripts written in languages other than PHP.

## Misc. Considerations
1. Performance is better with smaller files. (So, for example, instead of using one file for the data of all users, use separate files for separate users).
2. Make sure that the data files are not publically accessible.
3. Always sanitize any user input with [`htmlspecialchars()`](https://secure.php.net/manual/en/function.htmlspecialchars.php) or [`htmlentities()`](https://secure.php.net/manual/en/function.htmlentities.php).

## Tips
To make sure that your data files aren't accessible from the web, do one of these:
1. Save your files with the `.php` extension and put the following at the beginning of all your files:
    ```
    <?php
    __halt_compiler();
    
    
    ```
    (The trailing newline at the end is important!) This will stop PHP from parsing the data in your file, but will also not show anything when the file is accessed from the web.  
    
    Here's a function which allows you to create files with these lines at the top easily:
    ```
    function makeDataFile($filename){
	    file_put_contents($filename, base64_decode("PD9waHANCl9faGFsdF9jb21waWxlcigpOw0KDQo="), FILE_APPEND);
    }
    
    makeDataFile(myData.dat.php);
    ```

2. Use a particular extensions (like `.dat`) for your data files and use the following code in your `.htaccess` file:
    ```
    <Files ~ "\.(dat)$">
    order deny,allow
    deny from all
    </Files>
    ```
3. Place them outside of the web root directory.

## Changelog
 - `v1.0`: Initial version
 - `v1.1`: Bugfixes
 - `v1.2`: Implement serialization
 - `v1.3`: Bugfixes
 - `v1.4`: Bugfixes

## Report Bugs
Create an Issue or tweet to me at @rahuldottech

