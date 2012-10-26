<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license GNU Affero General Public License
 * @link https://blueimp.net/ajax/
 */

#error_reporting(E_ALL);
#ini_set('display_errors', '1');

class CustomAJAXChat extends AJAXChat {
	public $bp_config              = array();
	public $channels               = null;
	public $users                  = null;

	public $loggedin_user_fullname = null;
	public $xml_logout_url         = null;
    public $loggedin_user_id       = null;
    public $name_type              = null;


	// Returns an associative array containing userName, userID and userRole
	// Returns null if login is invalid
    function getValidLoginUserData() {
        $customUsers = $this->users;

        #print "<pre>'";
        #print_r ($customUsers);
        #print "'</pre>";

		
		foreach($customUsers as $key=>$value) {
			if(($value['userName'] == $this->loggedin_user_fullname) && ($value['password'] == $this->loggedin_user_fullname)) {
				$userData = array();
				$userData['userID'] = $key;
				$userData['userName'] = $this->trimUserName($value['userName']);
				$userData['displayName'] = $this->trimUserName($value['displayName']);
				$userData['userRole'] = $value['userRole'];
				$userData['profile'] = $value['profile'];
				return $userData;
			}
		}
		// Guest users:
		return $this->getGuestUser();
	}

	// Initialize custom configuration settings
	function initCustomConfig() {
        if ( $this->users == null )
        { 
            include ( dirname(__FILE__) . '/../../../config/bp-chat-config.php');

            $this->bp_config['username']           = $bp_chat_config_user;
            $this->bp_config['password']           = $bp_chat_config_pass;
            $this->bp_config['database']           = $bp_chat_config_db;
            $this->bp_config['bp_group_table']     = $bp_chat_config_db_table_prefix . "bp_groups";          //This is the Buddypress group table name: default = wp_bp_groups
            $this->bp_config['wp_sitemeta_table']  = $bp_chat_config_db_table_prefix . "options";           //This is the Wordpress sitemeta table
            $this->bp_config['wp_users_table']     = $bp_chat_config_db_table_prefix . "users";              //This is the WPMU user table name: default = wp_users
            $this->bp_config['bp_groups_members']  = $bp_chat_config_db_table_prefix . "bp_groups_members";  //This is the Buddypress group members table name: wp_bp_groups_members
            $this->bp_config['db']                 = $bp_chat_config_db_host;

            $this->user_type                       = "display_name";
	}

	#print "<pre>";
	#print_r ($this);
	#print "</pre>";
	#exit;

		if (isset($_COOKIE['loggedin_user_fullname']))
		    $this->loggedin_user_fullname          = $_COOKIE['loggedin_user_fullname'];
        if (isset($_COOKIE['xml_logout_url']))
		    $this->xml_logout_url                  = $_COOKIE['xml_logout_url'];
        #if (isset($_COOKIE['loggedin_user_id']))
	#	    $this->loggedin_user_id                = $_COOKIE['loggedin_user_id'];
	    
        if ( $this->users == null )
        { 
            $this->setConfig('logoutData', false, htmlspecialchars_decode($this->xml_logout_url));

            $this->getAllUserData();
        }
		//if(!$this->isLoggedIn() && !$this->getRequestVar('logout') && (isset($this->loggedin_user_id))) {
	if(isset($this->loggedin_user_fullname) && ($this->loggedin_user_fullname != "")) 
        {
            $userData = $this->getValidLoginUserData();

            if ( $this->name_type == 'user_login' )
            {
                $this->setUserName($userData['userName']);
                $this->setLoginUserName($userData['userName']);
            } else {
			    $this->setUserName($userData['displayName']);
			    $this->setLoginUserName($userData['displayName']);
            }
            $this->setUserID($userData['userID']);
			$this->setUserRole($userData['userRole']);
			$this->setRequestVar('login', true);
       	} else {
            $this->setRequestVar('login', false);
            $this->setRequestVar('logout', true);
            $this->logout();
        }
	}
	
	// Initialize custom request variables:
	// Initialize custom request variables:
	function initCustomRequestVars() {
		// Auto-login wordpress users:
		#if(!$this->getRequestVar('logout') && (isset($this->loggedin_user_id)) && ($this->loggedin_user_id != 0)) {
		if(isset($this->loggedin_user_fullname) && ($this->loggedin_user_fullname != "")) {
			$this->setRequestVar('login', true);
		} 
		//if (!$this->getRequestVar('logout') || (isset($this->loggedin_user_id) && $this->loggedin_user_id == 0))
		//{
		//	$this->setRequestVar('logout', true);
		//}
	}

	// Store the channels the current user has access to
	// Make sure channel names don't contain any whitespace
	function &getChannels() {
		if($this->_channels === null) {
			$this->_channels = array();
			
			$customUsers = $this->users;
			
			// Get the channels, the user has access to:
			if($this->getUserRole() == AJAX_CHAT_GUEST) {
				$validChannels = $customUsers[0]['channels'];
			} else {
				$validChannels = $customUsers[$this->getUserID()]['channels'];
			}

			// Add the valid channels to the channel list (the defaultChannelID is always valid):
			foreach($this->getAllChannels() as $key=>$value) {
				if(!in_array($value, $validChannels)) {
					continue;
				}

				if(in_array($value, $validChannels) || $key == $this->getConfig('defaultChannelID')) {
					$this->_channels[$key] = $value;
				}
			}
		}
		
		return $this->_channels;
	}

	// Store all existing channels
	// Make sure channel names don't contain any whitespace
	function &getAllChannels() {
		if($this->_allChannels === null) {
			// Get all existing channels:
			$customChannels = $this->channels;
			$defaultChannelFound = false;
			
			foreach($customChannels as $key=>$value) {
				$forumName = $this->trimChannelName($value["name"]);
				
				$this->_allChannels[$forumName] = $key;
				
				if($key == $this->getConfig('defaultChannelID')) {
					$defaultChannelFound = true;
				}
			}
			
			if(!$defaultChannelFound) {
				// Add the default channel as first array element to the channel list:
				$this->_allChannels = array_merge(
					array(
						$this->trimChannelName($this->getConfig('defaultChannelName'))=>$this->getConfig('defaultChannelID')
					),
					$this->_allChannels
				);
			}
		}
		return $this->_allChannels;
	}

    function &setCustomChannel($channel) {
?>
    <script>
        $(document).ready(function() {
            setTimeout(function() { 
                ajaxChat.switchChannel("<?php echo "$channel"; ?>"); 
            }, 1000);
        });
    </script>
<?php
    }

    # Here we set up ALL of the user data with one db connection
    function getAllUserData()
    {
		$database=$this->bp_config['database'];
        $usertable=$this->bp_config['wp_users_table'];
        $grouptable=$this->bp_config['bp_groups_members'];
        $groups=$this->bp_config['bp_group_table'];

        $link = mysql_connect($this->bp_config['db'],$this->bp_config['username'],$this->bp_config['password']);

        #This handles Russian characters
        mysql_query("SET NAMES 'utf8'");

        #Get all of the channels
		$query="SELECT id, name FROM $database.$groups WHERE name != ''";
        $result=mysql_query($query);

	    $this->channels = array();

        $i = 2;
        $this->channels = array(array("name" => "Public", "index" => 0, "id" => -1), array("name" => "Private", "index" => 1, "id" => -2));
        while ($row = mysql_fetch_assoc($result)) {
			array_push($this->channels, array("name" => $row["name"], "index" => $i++, "id" => $row["id"]));
        }

        #print "<pre>-";
        #print_r ($this->channels);
        #print "-</pre>";


        # Get the users
        $query="SELECT ID, user_nicename, display_name FROM $database.$usertable order by user_nicename asc";
		$result=mysql_query($query);

		$i=0;
		$j=1;
		// Default guest user (don't delete this one):
		$this->users = array();
		$this->users[0]['userRole'] = AJAX_CHAT_GUEST;
		$this->users[0]['userName'] = null;
		$this->users[0]['displayName'] = null;
		$this->users[0]['password'] = null;
		$this->users[0]['channels'] = array(0);
		$this->users[0]['userID']   = '0';
        $this->users[0]['profile']  = '';
        while ($row = mysql_fetch_array($result)) {
			$this->users[$j]['userName'] = $row['user_nicename'];
			$this->users[$j]['displayName'] = $row['display_name'];
			$this->users[$j]['password'] = $row['user_nicename'];
			$this->users[$j]['userID']   = "$j";
			$this->users[$j]['profile']  = "<a href='/members/" . $row['user_nicename'] . "/profile'>Profile Url</a>";
			if ($row['user_nicename'] == "admin")
			{
				$this->users[$j]['userRole'] = AJAX_CHAT_ADMIN;
				$this->users[$j]['channels'] = array();
                $group = 0;

				for ($group; $group < count($this->channels); $group++)
				{
					array_push($this->users[$j]['channels'], $group);
				}
				$i++;
				$j++;
				continue;
			} else {
				$this->users[$j]['userRole'] = AJAX_CHAT_USER;
            }
			# This is butt ugly!  
			$query="SELECT DISTINCT group_id, name FROM $database.$grouptable inner join $database.$groups on $database.$groups.id = group_id WHERE user_id = '".$row['ID']."' AND $database.$grouptable.is_confirmed = '1' AND is_banned = '0' order by name asc";
			$sub_result=mysql_query($query);

			$this->users[$j]['channels'] = array(0,1);
            while ($row = mysql_fetch_assoc($sub_result)) {
                #Find the index based on the group id in channels and push the index here
                foreach ($this->channels as $value )
                {
                    if ( $value["id"] == $row["group_id"] )
                    {
                        array_push($this->users[$j]['channels'], $value["index"]);
                    }
                }
            }
			$j++;
			$i++;
        }

        #print "<pre>-";
        #print_r ($this->users);
        #print "-</pre>";

        # Get the sitewide config setting for login name or display names
	$query="SELECT option_value FROM " . $this->bp_config['database'] ."." . $this->bp_config['wp_sitemeta_table'] . " WHERE option_name = 'bp-chat-setting-username'";
        $result=mysql_query($query);

        if ( @mysql_num_rows($result) > 0 )
        {
            $row = mysql_fetch_array($result);
            $this->name_type = $row['option_value'];
        }

	mysql_close($link);
    }

	function getLogoutXMLMessage() {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<root>';
		$xml .= '<infos>';
		$xml .= '<info type="logout">';
		$xml .= '<![CDATA['.urldecode($this->getConfig('logoutData')).']]>';
		$xml .= '</info>';
		$xml .= '</infos>';
		$xml .= '</root>';
		return $xml;
	}	
}
?>
