<?php
/**
 * Created by:
 * User: mdewi
 * Date: 9-5-2017
 * Time: 23:34
 */

namespace FileHandler;


/**
 * Class FileHandler
 * @package FileHandler
 *
 * Omschrijving:
 *
 */
class FileHandler {

    private static $regex_s = array(
                    "/",
                    "\\",
                    );


    /**
     * FileHandler constructor.
     */
//    function __construct()
//    {
//
//    }
//
//    function __destruct()
//    {
//
//    }

    /**
     * Functie: checkFileSize
     * @param $file Pad naar bestand.
     * @return integer
     */
    public static function Size($file)
    {
        return filesize($file);
    }

    /**
     * Deze functie zorgt ervoor dat een string ofwel
     * directory in dit geval, gefilterd word zodat
     * hij de juiste / of \ heeft.
     *
     * Functie: ds
     * @param $path
     * @return bool
     */
    public function ds($path)
    {
        $n_path = str_replace(array('\\','/','\\\\','//'), DIRECTORY_SEPARATOR, $path);
        return $n_path;
    }

	/**
     * Functie: folder_exist
     * @param $folder
     * @return bool
     */
    function folder_exist($folder)
    {
        $path = realpath($folder);
        return ($path !== false AND is_dir($path)) ? $path : false;
    }

	/**
     * Functie: scanDirectories
     * @param $rootDir
     * @param array $allData
     * @return array
     */
    function scanDirectories($rootDir, $allData=array())
    {
        // set filenames invisible if you want
        $invisibleFileNames = array(".", "..", ".htaccess", ".htpasswd");
        // run through content of root directory
        $dirContent = scandir($rootDir);
        foreach($dirContent as $key => $content) {
            // filter all files not accessible
            $path = $rootDir.'/'.$content;
            if(!in_array($content, $invisibleFileNames)) {
                // if content is file & readable, add to array
                if(is_file($path) && is_readable($path)) {
                    // save file name with path
                    $allData[] = $path;
                    // if content is a directory and readable, add path and name
                }elseif(is_dir($path) && is_readable($path)) {
                    // recursive callback to open new directory
                    $allData = scanDirectories($path, $allData);
                }
            }
        }
        return $allData;
    }
}