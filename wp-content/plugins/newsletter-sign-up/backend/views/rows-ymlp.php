<tr valign="top"><th scope="row"><label for="use_api">Use the YMLP API? <span class="ns_small">(recommended)</span></label></th>
                        <td><input type="checkbox" id="use_api" name="nsu_mailinglist[use_api]" value="1"<?php
                    if (isset($opts['use_api']) && $opts['use_api'] == '1') {
                        echo ' checked="checked"';
                    }
                    ?> /></td>
                    </tr>

                    <tbody class="api_rows"<?php if (!isset($opts['use_api']) || $opts['use_api'] != 1)
                        echo ' style="display:none" '; ?>>
                        <tr valign="top"><th scope="row">YMLP API Key <a target="_blank" href="http://www.ymlp.com/app/api.php">(?)</a></th>
                            <td><input size="50%" type="text" id="ymlp_api_key" name="nsu_mailinglist[ymlp_api_key]" value="<?php if (isset($opts['ymlp_api_key']))
                        echo $opts['ymlp_api_key']; ?>" /></td>
                        </tr>
                        <tr valign="top"><th scope="row">YMLP Username</th>
                            <td><input size="50%" type="text" id="ymlp_username" name="nsu_mailinglist[ymlp_username]" value="<?php if (isset($opts['ymlp_username']))
                        echo $opts['ymlp_username']; ?>" /></td>
                        </tr>
                        <tr valign="top"><th scope="row">YMLP GroupID<span class="ns_small">(starts at 1, check URL when 'viewing all contacts' in certain group)</span></th>
                            <td><input size="50%" type="text" id="ymlp_groupid" name="nsu_mailinglist[ymlp_groupid]" value="<?php if (isset($opts['ymlp_groupid']))
                        echo $opts['ymlp_groupid']; ?>" /></td>
                        </tr>
                    </tbody>