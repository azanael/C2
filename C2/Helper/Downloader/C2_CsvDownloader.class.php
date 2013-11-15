<?php
require_once 'C2_Downloader.class.php';

class C2_CsvDownloader extends C2_Downloader
{
    /**
     * $array[0] = array('data1', 'data2', ...);
     * $array[1] = array('data10', 'data11', ...);
     *
     * @param array $data
     * @throws InvalidArgumentException
     */
    public static function download(array $data, $fileName)
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data is empty.');
        }
        $str = '';
        foreach ($data as $values) {
            $line = '';
            foreach ($values as $value) {
                $line .= '"' . str_replace('"', '\"', $value) . '",';
            }
            $str .= rtrim($line, ',') . PHP_EOL;
        }
        $str = mb_convert_encoding($str, 'SJIS-win');
        self::csvDownload($str, $fileName);
    }
}