<tr valign="top">
                        <th scope="row">PHPList list ID</th>
                        <td><input size="2" type="text" name="nsu_mailinglist[phplist_list_id]" value="<?php
                    if (isset($opts['phplist_list_id'])) {
                        echo $opts['phplist_list_id'];
                    } else {
                        echo 1;
                    };
                    ?>" /></td>
                    </tr>