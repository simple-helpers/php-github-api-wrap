<?php
/**
 * Moves files into directories with suitable names.
 * Able to respect date and time rules.
 *
 * PHP version 5.4
 *
 * @category Class
 * @package  SimpleHelpers
 * @author   barantaran <yourchev@gmail.com>
 * @license  http://www.gnu.org/licenses/lgpl-3.0.en.html LGPL3
 * @link     http://github.com/simple-helpers/
 */

namespace SimpleHelpersPHP;

/**
 * Moves files into directories with suitable names.
 * Able to respect date and time rules.
 *
 * @category Class
 * @package  SimpleHelpers
 * @author   barantaran <yourchev@gmail.com>
 * @license  http://www.gnu.org/licenses/lgpl-3.0.en.html LGPL3
 * @link     http://github.com/simple-helpers/
 */

class FileMover
{
    /**
     * Directory containig subject files
     *
     * @var sourceDir
     */
    private $_sourceDir;

    /**
     * Array with rules of file moving
     *
     * @var suitablePath
     */
    private $_suitablePaths;

    /**
     * Available dirs
     *
     * @var dirs
     */
    private $_dirs;

    /**
     * Target files names
     *
     * @var files
     */
    private $_files;

    /**
     * Logger callback
     *
     * @var logger
     */
    private $_logger;

    /**
    * Construct FileMover providing it with initial data
    *
    *  @param string   $sourceDir     source directory name
    *  @param array    $suitablePaths file moving rules
    *  @param callable $logger        logger callback
    *
    *  @return void
    */
    function __construct( $sourceDir, $suitablePaths, $logger=null )
    {
        $ret="g";
        $this->_sourceDir = $sourceDir;
        $this->_suitablePaths = $suitablePaths;
        $this->_logger = $logger;
        if (!$this->_cacheDir()) {
            return false;
        }
    }

    /**
     * Exam source directory
     *
     * @return boolean
     */
    private function _cacheDir()
    {
        $directory = new \DirectoryIterator($this->_sourceDir);
        foreach ( $directory as $fileInfo ) {
            if (!$fileInfo->isDot()) {
                    $pathName = $fileInfo->getPathname();
                if ($fileInfo->isDir()) {
                    $this->_dirs[]["name"] = $fileInfo->getFilename();
                    $lastKey = count($this->_dirs)-1;
                    $this->_dirs[$lastKey]["path-name"] = $pathName;
                } else {
                    $this->_files[]["name"] = $fileInfo->getFilename();
                    $lastKey = count($this->_files)-1;
                    $this->_files[$lastKey]["path-name"] = $pathName;
                    $this->_files[$lastKey]["base-name"] = pathinfo(
                        $pathName,
                        PATHINFO_FILENAME
                    );
                    $this->_files[$lastKey]["m-name"] = $fileInfo->getMTime();
                }
            }
        }
        return true;
    }

    /**
     * Move file from source to destination
     *
     * @param string $source      source filename
     * @param string $destination file name
     *
     * @return boolean
     * */
    private function _move( $source, $destination )
    {
        if (file_exists($source)) {
            if (is_callable($this->_logger)) {
                $this->_logger("trying to move {$source} to {$destination}", "INFO");
            }
            if (copy($source, $destination)) {
                unlink($source);
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Return files discovered
     *
     * @return array
     * */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * Return dirs discovered
     *
     * @return array
     * */
    public function getDirs()
    {
        return $this->_dirs;
    }

    /**
     * Move files into directories if filename matches part of directory name
     *
     * @return boolean
     * */
    public function moveMatches()
    {
        foreach ($this->_files as $fileData) {
            foreach ($this->_dirs as $dirData) {
                if (strstr($dirData["name"], $fileData["base-name"])) {
                    $this->_move(
                        $fileData["path-name"],
                        $dirData["path-name"].DIRECTORY_SEPARATOR.$fileData["name"]
                    );
                }
            }
        }
        return true;
    }

    /**
     * Move files into specified directories if file is older then particular
     * time
     *
     * @param int $time time in seconds
     *
     * @return boolean
     * */
    public function moveOlderThan($time)
    {
        foreach ($this->_files as $fileData) {
            foreach ($this->_dirs as $dirData) {
            }
        }
        return true;
    }
}
?>
