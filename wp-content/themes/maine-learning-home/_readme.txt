See also readme at root.

--------------------------------------------------------
2011-08-02 

BP DEFAULT theme

Header.php

Added label for search

WAS

<input type="text" id="search-terms" name="search-terms" value="" />

SHOULD BE

<label for="search-terms" id="search-terms-label">Search the site: </label>Ê
<input type="text" id="search-terms" name="search-terms" value="" />

WITH THE FOLLOWING OPTIONAL CSS:

#search-terms-label {
display: none;
}



--------------------------------------------------------
2011-08-04 

BP DEFAULT theme

members/single/activity.php