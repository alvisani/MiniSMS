<?php
	/* Variables */
	$device = "/dev/ttyUSB0";
	$destination = "08xxxxxxxxxx";
	$message = "Hello World!";

	/* Check DIO extension loaded */
	if (!extension_loaded("dio")) {
		echo "Direct IO not loaded";
		exit();
	}

	/* Get response */
	function get_response($wd) {
		do {
			$response = trim(str_replace(array("\r\n","\r","\n"), '', dio_read($wd)));
		} while ($response == "");
		return $response;
	}

	/* Wait response */
	function wait_response($wd, $str) {
		$success = false;
		do {
		  $response = get_response($wd);
		    if(strpos($response,$str) !== false) {
				$success = true;
		  } else {
				dio_close($wd);
				echo "an Error occurred";
				exit();
		  }
		} while (!$success);
	}

	/* Open port */
	$handle = dio_open($device, O_RDWR | O_NOCTTY | O_NONBLOCK);

	/* Set attributes */
	dio_fcntl($handle, F_SETFL, O_SYNC);
	dio_tcsetattr($handle, array('baud' => 115200, 'bits' => 8, 'stop'  => 1, 'parity' => 0));

	/* Send SMS function */
	if ($handle) {
		/* Set SMS text mode */
		dio_write($handle, "AT+CMGF=1\r\n");
		wait_response($handle, "OK");
		/* Send SMS */
		dio_write($handle, "AT+CMGS=\"".$destination."\"\r\n");
		wait_response($handle, ">");
		dio_write($handle, $message);
		dio_write($handle, chr(26));
		wait_response($handle, "OK");
		/* Message sent successfully */
		echo "Message sent successfully";
	} else {
		/* Device isnt connected */
		echo "Device isnt connected";
	}

	/* Close handle */
	dio_close($handle);
?>