<?php

/*
 * FileWriter contains methods for writing textfiles on the server.
 */
class FileWriter {
		
	/*
	 * Constructs a new FileWriter object.
	 */
	function FileWriter() {
	}
	
	/*
	 *  Writes text to file. Returns true or false depending on whether file writing was
	 *  successful.
	 *
	 *  @param $file -  The file to write the text to (absolute or relative path accepted.)
	 *  @param $text - The text to write
	 *  @return True or fase depending on whether  write was successful.
	 */
	function writeFile($file, $text) {
		$fh = fopen($file, 'w') or die("can't open file");
		fwrite($fh, $text);
		fclose($fh);
	}
}

?>