<?php
class AmazonS3Gateway
{
    private $s3;
    private $logger;
    
    public function __construct()
    {
        $this->s3 = new AmazonS3();
        global $s3_config;
        if ($s3_config === null) {
            $this->s3->set_region(AmazonS3::REGION_TOKYO);
        } else {
            $this->s3->set_region($s3_config['region']);
        }
    }
    
    /**
     * S3サーバに画像ファイルをアップロードする
     *
     * @param string $path
     * @param string $destPath
     * @return boolean true/false
     */
    public function upload($path, $destPath, $isS3Copy = false)
    {
        global $s3_config;
        $imageSize = @getimagesize($path);
        if ($imageSize === false) {
            return false;
        }
        if ($imageSize['mime'] !== 'image/jpeg' && $imageSize['mime'] !== 'image/png' && $imageSize['mime'] !== 'image/gif') {
            return false;
        }
        $parseUrl = parse_url($path);
        $source_filename = ltrim($parseUrl['path'], '/' . $s3_config['bucket']);
        $dest_filename = ltrim($destPath, '/');
        
        if ($isS3Copy === true) {
            $response = $this->s3->copy_object(array( // $source
                    'bucket' => $s3_config['bucket'],
                    'filename' => $source_filename
                ), array( // $dest
                    'bucket' => $s3_config['bucket'],
                    'filename' => $dest_filename
                ), array( // $opt
                    'acl' => AmazonS3::ACL_PUBLIC
                )
            );
        } else {
            $response = $this->s3->create_object(
                strtolower($s3_config['bucket']),
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
        global $s3_config;
        $response = $this->s3->delete_objects(
            $s3_config['bucket'],
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