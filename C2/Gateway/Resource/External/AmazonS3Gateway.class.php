<?php
require_once dirname(__FILE__) . '/../../../Logger/C2_FileLogger.class.php';

class AmazonS3Gateway
{
    private $s3;
    private $config;
    
    public function __construct($config = null)
    {
        $this->s3 = new AmazonS3();
        $this->config = $config;
        if ($config === null) {
            $this->s3->set_region(AmazonS3::REGION_TOKYO);
        } else {
            $this->s3->set_region($config['region']);
        }
    }
    
    public function getError()
    {

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
        $imageSize = @getimagesize($path);
        if ($imageSize === false) {
            $this->logger->warn('Invalid image. path=' . $path);
            return false;
        }
        if ($imageSize['mime'] !== 'image/jpeg' && $imageSize['mime'] !== 'image/png' && $imageSize['mime'] !== 'image/gif') {
            $this->logger->warn('Invalid mime. path=' . $path . ' imageInfo=' . print_r($imageSize, true));
            return false;
        }
        
        if ($isS3Copy === true) {
            $parseUrl = parse_url($path);
            $sourceFilename = ltrim($parseUrl['path'], '/' . $this->config['bucket']);
            $destFilename = ltrim($destPath, '/');
            $response = $this->s3->copy_object(array( // $source
                    'bucket' => $this->config['bucket'],
                    'filename' => $sourceFilename
                ), array( // $dest
                    'bucket' => $this->config['bucket'],
                    'filename' => $destFilename
                ), array( // $opt
                    'acl' => AmazonS3::ACL_PUBLIC
                )
            );
        } else {
            $response = $this->s3->create_object(
                strtolower($this->config['bucket']),
                ltrim($destPath, '/'),
                array(
                    'fileUpload' => $path,
                    'acl' => AmazonS3::ACL_PUBLIC,
                    'contentType' => $imageSize['mime']
                )
            );
        }
        if ($response->isOK() !== true) {
            $this->logger->warn('create_object or copy_object returns false. path=' . $path . ' dest=' . $destPath . ' response = ' . print_r($response, true));
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
            $this->config['bucket'],
            array(
                'objects' => array(array('key' => $targetPath))
            )
        );
        if ($response->isOK() !== true) {
            $this->logger->warn('delete_objects returns false. targetPath=' . $targetPath . ' response = ' . print_r($response, true));
            return false;
        }
        return $response;
    }
}