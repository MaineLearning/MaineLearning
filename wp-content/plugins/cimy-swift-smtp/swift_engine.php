<?php

#OVERRIDE WP_MAIL FUNCTION!!!!
if ( !function_exists('wp_mail') ) {
function wp_mail($to, $subject, $message, $headers = '', $attachments = array(), $echo_error = false) {
	global $st_smtp_config;

	// Compact the input, apply the filters, and extract them back out
	extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );

	if ( !is_array($attachments) )
		$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );

	require_once 'Swift/lib/swift_required.php';

	// Headers
	if ( empty( $headers ) ) {
		$headers = array();
	} else {
		if ( !is_array( $headers ) ) {
			// Explode the headers out, so this function can take both
			// string headers and an array of headers.
			$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
		} else {
			$tempheaders = $headers;
		}
		$headers = array();

		// If it's actually got contents
		if ( !empty( $tempheaders ) ) {
			// Iterate through the raw headers
			foreach ( (array) $tempheaders as $header ) {
				if ( strpos($header, ':') === false ) {
					if ( false !== stripos( $header, 'boundary=' ) ) {
						$parts = preg_split('/boundary=/i', trim( $header ) );
						$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
					}
					continue;
				}
				// Explode them out
				list( $name, $content ) = explode( ':', trim( $header ), 2 );

				// Cleanup crew
				$name    = trim( $name    );
				$content = trim( $content );

				switch ( strtolower( $name ) ) {
					// Mainly for legacy -- process a From: header if it's there
					case 'from':
						if ( strpos($content, '<' ) !== false ) {
							// So... making my life hard again?
							$from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
							$from_name = str_replace( '"', '', $from_name );
							$from_name = trim( $from_name );

							$from_email = substr( $content, strpos( $content, '<' ) + 1 );
							$from_email = str_replace( '>', '', $from_email );
							$from_email = trim( $from_email );
						} else {
							$from_email = trim( $content );
						}
						break;
					case 'content-type':
						if ( strpos( $content, ';' ) !== false ) {
							list( $type, $charset ) = explode( ';', $content );
							$content_type = trim( $type );
							if ( false !== stripos( $charset, 'charset=' ) ) {
								$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
							} elseif ( false !== stripos( $charset, 'boundary=' ) ) {
								$boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
								$charset = '';
							}
						} else {
							$content_type = trim( $content );
						}
						break;
					case 'cc':
						$cc = array_merge( (array) $cc, explode( ',', $content ) );
						break;
					case 'bcc':
						$bcc = array_merge( (array) $bcc, explode( ',', $content ) );
						break;
					default:
						// Add it to our grand headers array
						$headers[trim( $name )] = trim( $content );
						break;
				}
			}
		}
	}

	// overwriting if specified or necessary
	if ($st_smtp_config['overwrite_sender'] == "overwrite_always") {
		$from_name = $st_smtp_config['sender_name'];
		$from_email = $st_smtp_config['sender_mail'];
	}

	// From email and name
	// If we don't have a name from the input headers
	if (empty($from_name))
		$from_name = 'WordPress';

	/* If we don't have an email from the input headers default to wordpress@$sitename
	 * Some hosts will block outgoing mail from this address if it doesn't exist but
	 * there's no easy alternative. Defaulting to admin_email might appear to be another
	 * option but some hosts may refuse to relay mail from an unknown domain. See
	 * http://trac.wordpress.org/ticket/5007.
	 */
	// Get the site domain and get rid of www.
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}
	$wp_from_email = 'wordpress@' . $sitename;
	if (empty($from_email) || $from_email == $wp_from_email) {
		if ($st_smtp_config['overwrite_sender'] == "overwrite_wp_default") {
			$from_name = $st_smtp_config['sender_name'];
			$from_email = $st_smtp_config['sender_mail'];
		}
		else {
			$from_email = $wp_from_email;
		}
	}

	//Create a message
	$message = Swift_Message::newInstance($subject)
		->setFrom(array(apply_filters('wp_mail_from', $from_email) => apply_filters('wp_mail_from_name', $from_name)))
		->setBody($message)
	;

	// Set destination addresses
	if (!is_array($to))
		$to = explode( ',', $to);

	foreach ((array)$to as $recipient) {
		// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
		$recipient_name = '';
		if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
			if ( count( $matches ) == 3 ) {
				$recipient_name = $matches[1];
				$recipient = $matches[2];
			}
		}
		$message->addTo(trim($recipient), $recipient_name);
	}

	// Add any CC and BCC recipients
	if (!empty($cc)) {
		foreach ((array) $cc as $recipient) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
			$recipient_name = '';
			if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
				if ( count( $matches ) == 3 ) {
					$recipient_name = $matches[1];
					$recipient = $matches[2];
				}
			}
			$message->addCc(trim($recipient),  $recipient_name);
		}
	}

	if (!empty($bcc)) {
		foreach ((array) $bcc as $recipient) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
			$recipient_name = '';
			if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
				if ( count( $matches ) == 3 ) {
					$recipient_name = $matches[1];
					$recipient = $matches[2];
				}
			}
			$message->addBcc(trim($recipient), $recipient_name);
		}
	}

	// Set Content-Type and charset
	// If we don't have a content-type from the input headers
	if (empty($content_type))
		$content_type = 'text/plain';

	$content_type = apply_filters('wp_mail_content_type', $content_type);

	$message->setContentType($content_type);

	// If we don't have a charset from the input headers
	if ( !isset( $charset ) )
		$charset = get_bloginfo('charset');

	// Set the content-type and charset
	Swift_Preferences::getInstance()->setCharset(apply_filters('wp_mail_charset', $charset));

	// Set custom headers
	if ( !empty( $headers ) ) {
		$msg_headers = $message->getHeaders();

		foreach((array) $headers as $name => $content) {
			$msg_headers->addTextHeader($name, $content);
		}

		if (false !== stripos($content_type, 'multipart') && ! empty($boundary))
			$msg_headers->addTextHeader("Content-Type", sprint("%s;\n\t boundary=\"%s\"", $content_type, $boundary));
	}

	if (!empty($attachments)) {
		foreach ($attachments as $attachment) {
			// bug in Swift Mailer https://github.com/swiftmailer/swiftmailer/issues/274
			if (empty($attachment))
				continue;
			try {
				$message->attach(Swift_Attachment::fromPath($attachment));
			} catch (Swift_IoException $e) {
				continue;
			}
		}
	}

	// default server if none inserted
	if (empty($st_smtp_config['server']))
		$st_smtp_config['server'] = "localhost";

	// default port if none inserted
	if (empty($st_smtp_config['port']))
		$st_smtp_config['port'] = 25;

	// we should try first and _maybe_ echo failure error
	try {
		// Create the Transport then call setUsername() and setPassword()
		$transport = Swift_SmtpTransport::newInstance($st_smtp_config['server'], $st_smtp_config['port']);

		if (!empty($st_smtp_config['ssl']))
			$transport->setEncryption($st_smtp_config['ssl']);

		if (!empty($st_smtp_config['username']))
			$transport->setUsername($st_smtp_config['username']);

		if (!empty($st_smtp_config['password']))
			$transport->setPassword($st_smtp_config['password']);

		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		// Send!
		$result = $mailer->send($message, $failures);
	}
	catch (Exception $e) {
		$result = false;
		if ($echo_error)
			echo $e->getMessage();
	}

	return $result;
}
}
?>