<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yusuf Koç
 * Site: http://ysfkc.com
 * Date: 22.06.2011
 * Time: 17:27
 */
 
class Upload
{
    public static $remove_spaces = true;
    public static $directory = 'upload';
    public static $size = '100M';
    public static $type = array('jpg', 'gif', 'png','jpeg');

    public function valid(array $file)
    {
        return (isset($file['name']) && isset($file['tmp_name']) && isset($file['size']) && isset($file['type']) && isset($file['error']));
    }

    public static function check_directory($directory)
    {
        if (is_dir(realpath($directory)) && is_writable($directory)) {
            return true;
        }

        return false;
    }

    public static function check_type(array $file, array $allowed)
    {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        return in_array($file_ext, $allowed);
    }

    public static function size(array $file, $allowed_size)
    {
        $allowed_size = strtoupper($allowed_size);

        if (!preg_match('#[0-9]++[GMKB]#', $allowed_size)) {
            return false;
        }

        switch (substr($allowed_size, -1)) {
            case 'G': $allowed_size = intval($allowed_size) * pow(1024, 3);
                break;
            case 'M': $allowed_size = intval($allowed_size) * pow(1024, 2);
                break;
            case 'K': $allowed_size = intval($allowed_size) * pow(1024, 1);
                break;
            case 'B': $allowed_size = intval($allowed_size);
                break;
        }

        return ($file['size'] <= $allowed_size);
    }

    public static function save(array $file, $config = null)
    {
        if (Upload::valid($file) === false) {
            return false;
        }

        if (isset($config['filename'])) {
            $fileName = $config['filename'];
        } else {
            $fileName = null;
        }

        $fileExt  = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
        if (is_null($fileName)) {
            $fileName = str_replace('.'.$fileExt, '_'.uniqid().'.'.$fileExt, $file['name']);
        } else {
            $fileName = $fileName.'.'.$fileExt;
        }

        if (isset($config['remove_spaces'])) {
            $remove_spaces = $config['remove_spaces'];
        } else {
            $remove_spaces = Upload::$remove_spaces;
        }

        if ($remove_spaces) {
            $fileName = preg_replace('#\s+#', '_', strtolower(Text::replace_tr($fileName)));
        }

        if (isset($config['directory'])) {
            $directory = $config['directory'];
        } else {
            $directory = Upload::$directory;
        }

        if (isset($config['type'])) {
            $type = $config['type'];
        } else {
            $type = Upload::$type;
        }

        if (isset($config['size'])) {
            $size = $config['size'];
        } else {
            $size = Upload::$size;
        }

        if (Upload::check_type($file, $type) === false) {
            return false;
        }

        if (Upload::size($file, $size) === false) {
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        if (!is_dir($directory)) {
            mkdir($directory,0777);
            chmod($directory,0777);
        }

        if (Upload::check_directory($directory) === false) {
            return false;
        }

        $filePath = realpath($directory) . DIRECTORY_SEPARATOR . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath) === false) {
            return false;
        }

        return array(
            'status' => true,
            'fileName' => $fileName,
            'path' => $filePath,
            'ext' => pathinfo($filePath, PATHINFO_EXTENSION)
        );
    }
}
