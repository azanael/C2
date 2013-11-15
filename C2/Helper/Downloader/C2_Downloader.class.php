<?php

class C2_Downloader
{
    protected static function fileDownload($filePath, $fileName, $fileMime)
    {
        if (!@is_file($filePath)) {
            throw new Exception('File is not in system.');
        }
        
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=''$fileName");
        header("Content-Type: $fileMime");
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        header('Content-Length: ' . @filesize($filePath));
        
        ob_clean();
        flush();
        @readfile($filePath);
        exit;
    }
    
    protected static function csvDownload($string, $fileName, $charset = 'SJIS-win')
    {
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Type: text/csv; charset=' . $charset);
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        ob_clean();
        flush();
        print($string);
        exit;
    }
}