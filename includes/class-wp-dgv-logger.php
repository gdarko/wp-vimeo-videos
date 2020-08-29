<?php

/**
 * Class responsible for logging data
 *
 * @since      1.0.0
 * @package    WP_DGV
 * @subpackage WP_DGV/includes
 * @copyright     Darko Gjorgjijoski <info@codeverve.com>
 * @license    GPLv2
 */
class WP_DGV_Logger
{

    /**
     * The log dir
     * @var string|false
     */
    private $log_dir = null;

    /**
     * WP_DGV_Logger constructor.
     */
    public function __construct()
    {
        $this->setup_log_dir();
        $this->protect_log_dir();
    }

    /**
     * Wrapper for writing the interactions to /wp-content/uploads/ file
     *
     * @param        $message
     * @param  string  $tag
     * @param  string  $filename
     *
     * @return bool
     */
    public function log($message, $tag = '', $filename = "debug.log")
    {

        if ( ! file_exists($this->log_dir)) {
            return false;
        }
        $log_file_path = trailingslashit($this->log_dir).$filename;
        if (file_exists($log_file_path) && filesize($log_file_path) > 10485760) {
            @unlink($log_file_path);
        }
        $is_object = false;
        if ( ! is_string($message) && ! is_numeric($message)) {
            ob_start();
            $this->dump($message);
            $message = ob_get_clean();
            $is_object = true;
        }

        if(!empty($tag)) {
            if($is_object) {
                $message = $tag . "\n" . $message;
            } else {
                $message = $tag.": ".$message;
            }
        }

        $this->writeln($log_file_path, $message);

        return true;
    }

    /**
     * Return the log dir
     */
    public function get_log_dir()
    {
        return $this->log_dir;
    }

    /**
     * Return the log path
     */
    private function setup_log_dir()
    {
        $this->log_dir = wvv_get_tmp_dir();
    }

    /**
     * Return the log dir
     *
     * @param  bool  $noindex
     */
    private function protect_log_dir($noindex = true)
    {

        $dir = $this->log_dir;

        if ( ! is_dir($dir)) {
            @mkdir($dir);
        }
        if (is_dir($dir)) {
            $index_path = $dir.DIRECTORY_SEPARATOR.'index.html';
            if ( ! file_exists($index_path)) {
                @touch($index_path);
            }
        }
        if ($noindex) {
            $htaccess_path = $dir.DIRECTORY_SEPARATOR.'.htaccess';
            if ( ! file_exists($htaccess_path)) {
                $contents = '<IfModule headers_module>
Header set X-Robots-Tag "noindex"
</IfModule>';
                $this->writeln($htaccess_path, $contents);
            }
        }
    }

    /**
     * Used to write contents into file provided by parameters
     *
     * @param $file string
     * @param $contents string
     * @param  string  $force_flag
     */
    private function writeln($file, $contents, $force_flag = '')
    {
        if (file_exists($file)) {
            $flag = $force_flag !== '' ? $force_flag : 'a';
            $fp   = fopen($file, $flag);
            fwrite($fp, $contents."\n");
        } else {
            $flag = $force_flag !== '' ? $force_flag : 'w';
            $fp   = fopen($file, $flag);
            fwrite($fp, $contents."\n");
        }
        fclose($fp);
    }

    /**
     * Dump data
     *
     * @param $data
     */
    private function dump($data)
    {
        $prev = ini_get('xdebug.overload_var_dump');
        if ( ! empty($prev)) {
            ini_set("xdebug.overload_var_dump", "off");
        }
        var_dump($data);
        if ( ! empty($prev)) {
            ini_set("xdebug.overload_var_dump", $prev);
        }
    }

}
