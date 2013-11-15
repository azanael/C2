<?php

class AmazonS3Gateway
{
    private $s3;
    private $bucket;
    
    public function __construct()
    {
        $this->s3 = new AmazonS3();
    }
    
    public function setRegion($region = AmazonS3::REGION_TOKYO)
    {
        $this->s3->set_region($region);
    }
    
    /**
     * S3サーバに画像ファイルをアップロードする
     *
     * @param string $path
     * @param string $destPath
     * @param boolean $isS3Copy S3同士のファイルコピーを行うかどうか
     * @return boolean true/false
     */
    public function upload($path, $destPath, $isS3Copy = false)
    {
        $imageSize = @getimagesize($path);
        if ($imageSize === false) {
            return false;
        }
        $parseUrl = parse_url($path);
        $source_filename = ltrim($parseUrl['path'], '/' . $this->bucket);
        $dest_filename = ltrim($destPath, '/');
        
        if ($isS3Copy === true) {
            $response = $this->s3->copy_object(array( // $source
                    'bucket' => $this->bucket,
                    'filename' => $source_filename
                ), array( // $dest
                    'bucket' => $this->bucket,
                    'filename' => $dest_filename
                ), array( // $opt
                    'acl' => AmazonS3::ACL_PUBLIC
                )
            );
        } else {
            $response = $this->s3->create_object(
                strtolower($this->bucket),
                ltrim($destPath, '/'),
                array(
                    'fileUpload' => $path,
                    'acl' => AmazonS3::ACL_PUBLIC,
                    'contentType' => $imageSize['mime']
                )
            );
        }
        if ($response->isOK() !== true) {
            return false;
        }
        return true;
    }
    
    /**
     * S3にアップロードされたファイルを削除する
     *
     * @param string $targetPath 'SOFOLDER/image.png'
     * @return unknown
     */
    public function delete($targetPath)
    {
        $response = $this->s3->delete_objects(
            $this->bucket,
            array(
                'objects' => array(array('key' => $targetPath))
            )
        );
        if ($response->isOK() !== true) {
            return false;
        }
        return $response;
    }
}