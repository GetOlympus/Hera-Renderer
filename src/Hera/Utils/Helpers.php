<?php

namespace GetOlympus\Hera\Utils;

/**
 * Helpers controller
 *
 * @package    OlympusHeraRenderer
 * @subpackage Utils
 * @author     Achraf Chouk <achrafchouk@gmail.com>
 * @since      0.0.2
 *
 */

class Helpers
{
    /**
     * Copy a file contents from this internal assets folder to the public dist Olympus assets folder.
     *
     * @param  string  $sourcePath
     * @param  string  $targetPath
     * @param  string  $filename
     * @param  bool    $symlink
     */
    public static function copyFile($sourcePath, $targetPath, $filename, $symlink = false) : void
    {
        // Check paths
        if ($sourcePath === $targetPath) {
            return;
        }

        $targetFilepath = rtrim($targetPath, S).S.$filename;

        // Check if file exists and create it
        if (file_exists($targetFilepath)) {
            return;
        }

        // Build new contents
        $sourceFilepath = rtrim($sourcePath, S).S.$filename;

        // Check the old file to copy its contents
        if (file_exists($sourceFilepath)) {
            $copy = $symlink ? symlink($sourceFilepath, $targetFilepath) : copy($sourceFilepath, $targetFilepath);
        } else {
            self::filePutContents(
                $targetFilepath,
                '',
                'This file has been auto-generated by the Zeus package without any content'
            );
        }
    }

    /**
     * Helper function to create a file in a target path with its contents.
     *
     * @param  string  $filepath
     * @param  string  $contents
     * @param  string  $message
     * @param  bool    $usedate
     */
    public static function filePutContents($filepath, $contents, $message, $usedate = true) : void
    {
        // Check date on suffix
        $suffix = $usedate ? ' on '.date('l jS \of F Y h:i:s A') : '';

        // Update contents
        $contents = !empty($contents) ? $contents."\n" : $contents;

        // Copy file contents
        file_put_contents($filepath, "/**\n * ".$message.$suffix.".\n */\n\n".$contents);
    }
}
