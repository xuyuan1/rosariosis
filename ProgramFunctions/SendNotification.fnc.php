<?php
/**
 * Send Notification functions.
 *
 * @package RosarioSIS
 * @subpackage ProgramFunctions
 */

/**
 * Send Create Student Account notification
 *
 * @since 5.9
 *
 * @param int    $student_id Student ID.
 * @param string $to         To email address. Defaults to $RosarioNotifyAddress (see config.inc.php).
 *
 * @return bool  False if email not sent, else true.
 */
function SendNotificationCreateStudentAccount( $student_id, $to = '' )
{
	global $RosarioNotifyAddress;

	require_once 'ProgramFunctions/SendEmail.fnc.php';

	if ( empty( $to ) )
	{
		$to = $RosarioNotifyAddress;
	}

	if ( ! $student_id
		|| ! filter_var( $to, FILTER_VALIDATE_EMAIL ) )
	{
		return false;
	}

	$student_name = DBGetOne( "SELECT " . DisplayNameSQL() . " AS FULL_NAME
		FROM STUDENTS
		WHERE STUDENT_ID='" . $student_id . "'" );

	$message = _( 'New student account was created for %s (%d) (inactive).' );

	if ( Config( 'CREATE_STUDENT_ACCOUNT_AUTOMATIC_ACTIVATION' ) )
	{
		// @since 5.9 Automatic Student Account Activation.
		$message = _( 'New student account was activated for %s (%d).' );
	}

	$message = sprintf( $message, $student_name, $student_id );

	return SendEmail( $to, _( 'Create Student Account' ), $message );
}

/**
 * Send Create User Account notification
 *
 * @since 5.9
 *
 * @param int    $staff_id Staff ID.
 * @param string $to       To email address. Defaults to $RosarioNotifyAddress (see config.inc.php).
 *
 * @return bool  False if email not sent, else true.
 */
function SendNotificationCreateUserAccount( $staff_id, $to = '' )
{
	global $RosarioNotifyAddress;

	require_once 'ProgramFunctions/SendEmail.fnc.php';

	if ( empty( $to ) )
	{
		$to = $RosarioNotifyAddress;
	}

	if ( ! $staff_id
		|| ! filter_var( $to, FILTER_VALIDATE_EMAIL ) )
	{
		return false;
	}

	$user_name = DBGetOne( "SELECT " . DisplayNameSQL() . " AS FULL_NAME
		FROM STAFF
		WHERE STAFF_ID='" . $staff_id . "'" );

	$message = sprintf(
		_( 'New user account was created for %s (%d) (No Access).' ),
		$user_name,
		UserStaffID()
	);

	return SendEmail( $to, _( 'Create User Account' ), $message );
}

/**
 * Send New Administrator notification
 *
 * @since 5.9
 *
 * @param int    $staff_id Staff ID.
 * @param string $to       To email address. Defaults to $RosarioNotifyAddress (see config.inc.php).
 *
 * @return bool  False if email not sent, else true.
 */
function SendNotificationNewAdministrator( $staff_id, $to = '' )
{
	global $RosarioNotifyAddress;

	require_once 'ProgramFunctions/SendEmail.fnc.php';

	if ( empty( $to ) )
	{
		$to = $RosarioNotifyAddress;
	}

	if ( ! $staff_id
		|| ! filter_var( $to, FILTER_VALIDATE_EMAIL ) )
	{
		return false;
	}

	$admin_name = DBGetOne( "SELECT " . DisplayNameSQL() . " AS FULL_NAME
		FROM STAFF
		WHERE STAFF_ID='" . $staff_id . "'" );

	$message = sprintf(
		_( 'New Administrator account was created for %s, by %s.' ),
		$admin_name,
		User( 'NAME' )
	);

	return SendEmail( $to, _( 'New Administrator Account' ), $message );
}

/**
 * Send Activate Student Account notification
 * Do not send notification if RosarioSIS installed on localhost (Windows typically).
 *
 * @since 5.9
 *
 * @uses _rosarioLoginURL() function
 *
 * @param int    $student_id Student ID.
 * @param string $to         To email address. Defaults to student email (see Config( 'STUDENTS_EMAIL_FIELD' )).
 *
 * @return bool  False if email not sent, else true.
 */
function SendNotificationActivateStudentAccount( $student_id, $to = '' )
{
	require_once 'ProgramFunctions/SendEmail.fnc.php';

	if ( empty( $to ) )
	{
		if ( ! Config( 'STUDENTS_EMAIL_FIELD' ) )
		{
			return false;
		}

		// @since 5.9 Send Account Activation email notification to Student.
		$student_email_field = Config( 'STUDENTS_EMAIL_FIELD' ) === 'USERNAME' ?
			'USERNAME' : 'CUSTOM_' . Config( 'STUDENTS_EMAIL_FIELD' );

		$to = DBGetOne( "SELECT " . $student_email_field . " FROM STUDENTS
			WHERE STUDENT_ID='" . $student_id . "'" );
	}

	if ( ! $student_id
		|| ! filter_var( $to, FILTER_VALIDATE_EMAIL ) )
	{
		return false;
	}

	$rosario_url = _rosarioLoginURL();

	if ( ( strpos( $rosario_url, '127.0.0.1' ) !== false
			|| strpos( $rosario_url, 'localhost' ) !== false )
		&& ! ROSARIO_DEBUG )
	{
		// Do not send notification if RosarioSIS installed on localhost (Windows typically).
		return false;
	}

	$message = _( 'Your account was activated (%d). You can login at %s' );

	$message = sprintf( $message, $student_id, $rosario_url );

	return SendEmail( $to, _( 'Create Student Account' ), $message );
}

/**
 * Send Activate User Account notification
 * Do not send notification if RosarioSIS installed on localhost (Windows typically).
 *
 * @since 5.9
 *
 * @uses _rosarioLoginURL() function
 *
 * @param int    $staff_id User ID.
 * @param string $to       To email address. Defaults to user email.
 *
 * @return bool  False if email not sent, else true.
 */
function SendNotificationActivateUserAccount( $staff_id, $to = '' )
{
	require_once 'ProgramFunctions/SendEmail.fnc.php';

	if ( empty( $to ) )
	{
		$to = DBGetOne( "SELECT EMAIL FROM STAFF
			WHERE STAFF_ID='" . $staff_id . "'" );
	}

	if ( ! $staff_id
		|| ! filter_var( $to, FILTER_VALIDATE_EMAIL ) )
	{
		return false;
	}

	$is_no_access_profile = DBGetOne( "SELECT 1 FROM STAFF
		WHERE STAFF_ID='" . $staff_id . "'
		AND PROFILE='none'" );

	if ( $is_no_access_profile )
	{
		return false;
	}

	$rosario_url = _rosarioLoginURL();

	if ( ( strpos( $rosario_url, '127.0.0.1' ) !== false
			|| strpos( $rosario_url, 'localhost' ) !== false )
		&& ! ROSARIO_DEBUG )
	{
		// Do not send notification if RosarioSIS installed on localhost (Windows typically).
		return false;
	}

	$message = _( 'Your account was activated (%d). You can login at %s' );

	$message = sprintf( $message, $staff_id, $rosario_url );

	return SendEmail( $to, _( 'Create User Account' ), $message );
}

/**
 * RosarioSIS login page URL
 * Removes part beginning with 'Modules.php' or 'index.php' from URI.
 *
 * Local function
 *
 * @since 5.9
 *
 * @return string Login page URL.
 */
function _rosarioLoginURL()
{
	$page_url = 'http';

	if ( isset( $_SERVER['HTTPS'] )
		&& $_SERVER['HTTPS'] == 'on' )
	{
		$page_url .= 's';
	}

	$page_url .= '://';

	$root_pos = strpos( $_SERVER['REQUEST_URI'], 'Modules.php' ) ?
		strpos( $_SERVER['REQUEST_URI'], 'Modules.php' ) : strpos( $_SERVER['REQUEST_URI'], 'index.php' );

	$root_uri = substr( $_SERVER['REQUEST_URI'], 0, $root_pos );

	if ( $_SERVER['SERVER_PORT'] != '80'
		&& $_SERVER['SERVER_PORT'] != '443' )
	{
		$page_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $root_uri;
	}
	else
	{
		$page_url .= $_SERVER['SERVER_NAME'] . $root_uri;
	}

	return $page_url;
}
