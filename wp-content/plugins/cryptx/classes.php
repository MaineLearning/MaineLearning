<?php
/**
 * Classes for CryptX
 */

/**
 * Don't load this file direct!
 */
if (!defined('ABSPATH')) {
	return ;
	}

/**
 * class to scan files for string
 */
class rw_cryptx_FileSystemStringSearch 
{ 
    //  MEMBERS 
     
    /** 
     * @var string $_searchPath path to file/directory to search 
     */ 
    var $_searchPath; 
     
    /** 
     * @var string $_searchString the string to search for 
     */ 
    var $_searchString; 
     
    /** 
     * @var array $_searchResults holds search result information 
     */ 
    var $_searchResults; 
     
    //  CONSTRUCTOR 
     
    /** 
     * Class constructor 
     * @param string $searchPath path to file or directory to search 
     * @param string $searchString string to search for 
     * @return void 
     */ 
    function rw_cryptx_FileSystemStringSearch($searchPath, $searchString) 
    { 
        $this->_searchPath = $searchPath; 
        $this->_searchString = $searchString; 
        $this->_searchResults = array(); 
    } 
     
    //  MANIPULATORS 
     
    /** 
     * Checks path is valid 
     * @return bool 
     */ 
    function isValidPath() 
    { 
        if(file_exists($this->_searchPath)) { 
            return true; 
        } else { 
            return false; 
        } 
    } 
     
    /** 
     * Determines if path is a file or directory 
     * @return bool 
     */ 
    function searchPathIsFile() 
    { 
        // check for trailing slash 
        if(substr($this->_searchPath, -1, 1)=='/' || 
           substr($this->_searchPath, -1, 1)=='\\') { 
           return false; 
        } else { 
           return true; 
        } 
    } 
     
    /** 
     * Searches given file for search term 
     * @param string $file the file path 
     * @return void 
     */ 
    function searchFileForString($file) 
    { 
        // open file to an array 
        $fileLines = file($file); 
         
        // loop through lines and look for search term 
        $lineNumber = 1; 
        foreach($fileLines as $line) { 
            $searchCount = substr_count($line, $this->_searchString); 
            if($searchCount > 0) { 
                // log result 
                $this->addResult($file, $line, $lineNumber, $searchCount); 
            } 
            $lineNumber++; 
        } 
    } 
     
    /** 
     * Adds result to the result array 
     * @param string $lineContents the line itself 
     * @param int $lineNumber the file line number 
     * @param int $searchCount the number of occurances of the search term 
     * @return void 
     */ 
    function addResult($filePath, $lineContents, $lineNumber, $searchCount) 
    { 
        $this->_searchResults[] = array('filePath' => $filePath, 
                                        'lineContents' => $lineContents, 
                                        'lineNumber' => $lineNumber, 
                                        'searchCount' => $searchCount); 
    } 
     
    /** 
     * Takes a given string (usually a line from search results) 
     * and highlights the search term 
     * @param string $string the string containing the search term(s) 
     * @return string 
     */ 
    function highlightSearchTerm($string) 
    { 
        return str_replace($this->_searchString, 
                           '<strong>'.$this->_searchString.'</strong>', 
                           $string); 
    } 
     
    /** 
     * Recursively scan a folder and sub folders for search term 
     * @param string path to the directory to search 
     * @return void 
     */ 
    function scanDirectoryForString($dir) 
    { 
        $subDirs = array(); 
        $dirFiles = array(); 
         
        $dh = opendir($dir); 
        while(($node = readdir($dh)) !== false) { 
            // ignore . and .. nodes 
            if(!($node=='.' || $node=='..')) { 
                if(is_dir($dir.$node)) { 
                    $subDirs[] = $dir.$node.'/'; 
                } else { 
                    $dirFiles[] = $dir.$node; 
                } 
            } 
        } 
         
        // loop through files and search for string 
        foreach($dirFiles as $file) { 
            $this->searchFileForString($file); 
        } 
         
        // if there are sub directories, scan them 
        if(count($subDirs) > 0) { 
            foreach($subDirs as $subDir) { 
                $this->scanDirectoryForString($subDir); 
            } 
        } 
    } 
     
    /** 
     * Run the search 
     * @return void 
     */ 
    function run() 
    { 
        // check path exists 
        if($this->isValidPath()) { 
         
            if($this->searchPathIsFile()) { 
                // run search on the file 
                $this->searchFileForString($this->_searchPath); 
            } else { 
                // scan directory contents for string 
                $this->scanDirectoryForString($this->_searchPath); 
            } 
          
         } else { 
          
            die('FileSystemStringSearch Error: File/Directory does not exist'); 
          
         } 
    } 
     
    //  ACCESSORS 
    function getResults() 
    { 
        return $this->_searchResults; 
    } 
     
    function getResultCount() 
    { 
        $count = 0; 
        foreach($this->_searchResults as $result) { 
            $count += $result['searchCount']; 
        } 
        return $count; 
    } 
     
    function getSearchPath() 
    { 
        return $this->_searchPath; 
    } 
     
    function getSearchString() 
    { 
        return $this->_searchString; 
    } 
}
?>