<?php
$database_defaults = $api->get_database_defaults();
$database_previous = $api->get_previous_database_settings();
$default_url = $api->get_default_url();

$custom_home_tip = 'OPTIONAL. This is also known as the site address. This is the home address
	where your main site resides. This may differ from your WordPress URL. Ex: http://foo.com';
?>

<form action="?step=4" method=post>
	<input type="hidden" name="options" id="pb_options" value="<?php echo htmlspecialchars( serialize( $this->_options ) ); ?>" />
	
	<h3>URL Settings</h3>
	<div style="margin-left: 20px;">
		
		<label>
			WordPress Address
			<?php $this->tip( 'This is the address where you want the final WordPress site you are
				restoring / migrating to reside. Ex: http://foo.com/wp', '', true ); ?>
			<br>
			<span class="light">(Site URL)</span>
		</label>
		<input type="text" name="siteurl" value="<?php echo $default_url; ?>" size="50" /><br>
		&nbsp;<span class="light" style="display: inline-block; width: 400px;">previously: <?php echo $this->_backupdata['siteurl']; ?></span>
		
		<?php if ( isset( $this->_backupdata['is_multisite'] ) && ( ( $this->_backupdata['is_multisite'] === true ) || ( $this->_backupdata['is_multisite'] == 'true' ) ) ) { // multisite ?>
			<label>
				MultiSite Domain
				<?php $this->tip( 'This is the MultiSite main domain. Ex: foo.com', '', true ); ?><br>
				&nbsp;
			</label>
			<input type="text" name="domain" value="<?php echo $api->get_default_domain(); ?>" size="50" /><br>
			&nbsp;<span class="light" style="display: inline-block; width: 400px;">previously: <?php echo $this->_backupdata['domain']; ?></span>
		<?php } else { ?>
		
		<label style="width: 100%; margin-left: 150px;">
			<input type="checkbox" name="custom_home" class="option_toggle" value="on" id="custom_home">
			Use optional custom site address (Home URL)
			<?php $this->tip( $custom_home_tip, '', true ); ?>
		</label>
		<br><br>
		
		<div class="custom_home_toggle" style="display: none;">
			<label>
				Site Address
				<?php $this->tip( $custom_home_tip, '', true ); ?>
				<br>
				<span class="light">(Home URL)</span>
			</label>
			<input type="text" name="home" value="<?php echo $default_url; ?>" size="50" />			<br>
			&nbsp;<span class="light" style="display: inline-block; width: 400px;">previously: <?php echo $this->_backupdata['homeurl']; ?></span>
		</div>
		<br><br>
		
		<?php } // end non-multisite ?>
		
	</div>
	
	<h3>Database Settings<?php
		$this->tip( 'These settings control where your backed up database will be restored to.
		If you are restoring to the same server, the settings below will import the database
		to your existing WordPress database location, overwriting your existing WordPress database
		already on the server.  If you are moving to a new host you will need to create a database
		to import into. The database settings MUST be unique for each WordPress installation.  If
		you use the same settings for multiple WordPress installations then all blog content and
		settings will be shared, causing conflicts!', '', true );
	?></h3>
	<div style="margin-left: 20px;">
		
		<img src="?ezimg=bullet_error.png" style="float: left;">
		<div style="margin-left: 20px;">
			Database settings must be unique to each WordPress installation (they can not share identical settings).
			Create a new database or unique prefix for each WordPress installation on this server.
		</div>
		<br>
		
		<label>
			MySQL Server
			<?php $this->tip( 'This is the address to the mySQL server where your database will be stored.
					99% of the time this is localhost.  The location of your mySQL server will be provided
					to you by your host if it differs.', '', true ); ?>
		</label>
		<input type="text" name="db_server" id="mysql_server" value="<?php echo $database_defaults['server']; ?>" style="width: 175px;" />
		<?php if ( $database_previous['server'] != '' ) { echo '<span class="light">previously: ' . $database_previous['server'] . '</span>'; } ?>
		<br>
		
		<label>
			Database Name
			<?php $this->tip( 'This is the name of the database you want to import your blog into. The database
				user must have permissions to be able to access this database.  If you are migrating this blog
				to a new host you will need to create this database (ie using CPanel or phpmyadmin) and create
				a mysql database user with permissions.', '', true ); ?>
		</label>
		<input type="text" name="db_name" id="mysql_name" value="<?php echo $database_defaults['database']; ?>" style="width: 175px;" />
		<?php if ( $database_previous['database'] != '' ) { echo '<span class="light">previously: ' . $database_previous['database'] . '</span>'; } ?>
		<br>
		
		<label>
			Database User
			<?php $this->tip( 'This is the database user account that has permission to access the database name
				in the input above.  This user must be given permission to this database for the import to work.', '', true ); ?>
		</label>
		<input type="text" name="db_user" id="mysql_user" value="<?php echo $database_defaults['user']; ?>" style="width: 175px;" />
		<?php if ( $database_previous['user'] != '' ) { echo '<span class="light">previously: ' . $database_previous['user'] . '</span>'; } ?>
		<br>
		
		<label>
			Database Pass
			<?php $this->tip( 'This is the password for the database user.', '', true ); ?>
		</label>
		<input type="text" name="db_password" id="mysql_password" value="<?php echo $database_defaults['password']; ?>" style="width: 175px;" />
		<?php if ( $database_previous['password'] != '' ) { echo '<span class="light">previously: ' . $database_previous['password'] . '</span>'; } ?>
		<br>
		
		<label>
			Database Prefix
			<?php $this->tip( 'This is the prefix given to all tables in the database.  If you are cloning the site
				on the same server AND the same database name then you will want to change this or else the imported
				database will overwrite the existing tables.', '', true ); ?>
		</label>
		<input type="text" name="db_prefix" id="mysql_prefix" id="mysql_prefix" value="<?php echo $database_defaults['prefix']; ?>" style="width: 175px;" />
		<?php if ( $database_previous['prefix'] != '' ) { echo '<span class="light">previously: ' . $database_previous['prefix'] . '</span>'; } ?>
		<br>
		
		<div style="font-size: 9px; margin-bottom: 7px;">
			<label>&nbsp;</label>&nbsp;
			<a target="_new" href="http://pluginbuddy.com/tutorial-create-database-in-cpanel/">
				Need help creating a database in cPanel? See this tutorial.
			</a>
		</div>
		
		<label>&nbsp;</label>
		
		<div style="margin-left: 20px; margin-top: 12px;">
			<input type="hidden" name="wipe_database" id="wipe_database" value="<?php echo $database_defaults['wipe']; ?>">
			<span class="toggle button-secondary" id="ithemes_mysql_test">Test database settings...</span>
			<?php
			/*
			<span class="toggle button-secondary" id="advanced">Advanced Configuration Options</span>
			<div id="toggle-advanced" class="toggled" style="margin-top: 12px; margin-left: 135px;">
				<?php
				//$this->alert( 'WARNING: These are advanced configuration options.', 'Use caution as improper use could result in data loss or other difficulties.' );
				?>
				<b>WARNING:</b> Improper use of Advanced Options could result in data loss.
				<br><br>
				
				<input type="checkbox" name="wipe_database" onclick="
					if ( !confirm( 'WARNING! WARNING! WARNING! WARNING! WARNING! \n\nThis will clear any existing WordPress installation or other content in this database. This could result in loss of posts, comments, pages, settings, and other software data loss. Verify you are using the exact database settings you want to be using. PluginBuddy & all related persons hold no responsibility for any loss of data caused by using this option. \n\n Are you sure you want to do this and potentially wipe existing data? \n\n WARNING! WARNING! WARNING! WARNING! WARNING!' ) ) {
						return false;
					}
				" <?php if ( $this->_options['wipe_database'] == '1' ) echo 'checked'; ?>> Wipe database on import. Use with caution. <?php $this->tip( 'WARNING: Checking this box will have this script clear ALL existing data from your database prior to import, including non-WordPress data. This is useful if you are restoring over an existing site or for repaired a failed migration. Use caution when using this option.' ); ?><br>
				<input type="checkbox" name="skip_database_import" <?php if ( $this->_options['skip_database_import'] == '1' ) echo 'checked'; ?>> Skip import of database. <br>
				<input type="checkbox" name="skip_database_migration" <?php if ( $this->_options['skip_database_migration'] == '1' ) echo 'checked'; ?>> Skip migration of database. <br>
				<br>
				<b>After importing, skip data migration on these tables:</b><?php $this->tip( 'Database tables to exclude from migration. These tables will still be imported into the database but URLs and paths will not be modified. This is useful if the migration is timing out.' ); ?><br><textarea name="exclude_tables" style="width: 300px; height: 75px;"></textarea>
			</div>
			*/
			?>
			<div style="display: none; background-color: #F1EDED; -moz-border-radius:4px 4px 4px 4px; border:1px solid #DFDFDF; margin:10px; padding:3px; margin-left: 135px;" id="ithemes_loading">
				<?php echo ezimg::genImageTag( 'loading.gif' ) . ' Loading ...'; ?>
			</div>
		</div>
		
		<?php if ( ( $this->_options['force_high_security'] != false ) || ( isset( $this->_backupdata['high_security'] ) && ( $this->_backupdata['high_security'] === true ) ) ) { ?>
			<label>&nbsp;</label><br>
			<h3>Create Administrator Account <?php $this->tip( 'Your backup was created either with High Security Mode enabled or from a WordPress Multisite installation. For security your must provide a WordPress username and password to grant administrator privileges to.', '', true ); ?></h3>
			<label>
				New admin username
			</label>
			<input type="text" name="admin_user" id="admin_user" value="" style="width: 175px;" />
			<span class="light">(if user exists, it will be overwritten)</span>
			<br>
			<label>
				Password
			</label>
			<input type="text" name="admin_pass" id="admin_pass" value="" style="width: 175px;" />
			<br>
		<?php } // end high security. ?>
		
		
	</div><br>
	
	<input type="hidden" name="file" value="<?php echo htmlentities( $this->_options['file'] ); ?>" />
	<p style="text-align: center;"><input type="submit" name="submit" value="Next Step &raquo;" class="button" /></p>
</form>