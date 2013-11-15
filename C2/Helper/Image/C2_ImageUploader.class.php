<?php

class C2_ImageUploader
{
    /**
     * 画像ファイルをサーバへアップロードする
     *
     * production.phpのRESOURCE_SERVER_TYPEがs3に指定されている場合は
     * 自動的にS3サーバにアップロードする。
     * それ以外の場合は同じサーバにアップロードする。
     *
     * @param string $path アップロードする画像のフルパス。PHPのアップロード機構を使う場合は$_FILES['pict']['tmp_name']など
     * @param string $destPath アップロード先のディレクトリ。htdocsからのパスのみを記載する。ex.) /upload/article/image/100/sample.jpg
     * @return boolean true/false
     */
    public function upload($path, $destPath, $isS3Copy = false)
    {
        if (RESOURCE_SERVER_TYPE == 's3') {
            return $this->_s3($path, $destPath, $isS3Copy);
        }
        return $this->_local($path, $destPath);
    }
    
    /**
     * 画像ファイルをサーバから物理削除する
     *
     * production.phpのRESOURCE_SERVER_TYPEがs3に指定されている場合は
     * 自動的にS3サーバからファイルを削除する。
     *
     * @param string $destPath 削除したい画像ファイルのパス。htdocsからのパスのみを記載する。
     * @return boolean true/false
     */
    public function delete($destPath)
    {
        if (RESOURCE_SERVER_TYPE == 's3') {
            require_once 'Gateway/AmazonS3Gateway.class.php';
            $gateway = new AmazonS3Gateway();
            $r = $gateway->delete($destPath);
            if ($r === false) {
                CPOS_Logger::error('Failed to delete image file from S3. path=' . $destPath);
            }
            return $r;
        }
        $r = @unlink(PCROOT_DIR . $destPath);
        if ($r === false) {
            CPOS_Logger::warn('Failed to delete image file from local. path=' . PCROOT_DIR . $destPath);
        }
        return $r;
    }
    
    private function _local($path, $destPath)
    {
        if (!is_dir(dirname(PCROOT_DIR . $destPath))) {
            $r = @mkdir(dirname(PCROOT_DIR . $destPath), 0777, true);
            if ($r === false) {
                CPOS_Logger::crit('Failed to create dir. dir=' . dirname(PCROOT_DIR . $destPath));
                return false;
            }
        }
        if (is_uploaded_file($path)) {
            $r = @move_uploaded_file($path, PCROOT_DIR . $destPath);
            if ($r === false) {
                CPOS_Logger::error('Failed to move uploaded image file. path=' . $path . ' dest=' . PCROOT_DIR . $destPath);
            }
            return $r;
        }
        $r = @rename($path, PCROOT_DIR . $destPath);
        if ($r === false) {
            CPOS_Logger::error('Failed to rename local image file. path=' . $path . ' dest=' . PCROOT_DIR . $destPath);
        }
        return $r;
    }
    
    private function _s3($path, $destPath, $isS3Copy = false)
    {
        require_once 'Gateway/AmazonS3Gateway.class.php';
        $gateway = new AmazonS3Gateway();
        $r = $gateway->upload($path, $destPath, $isS3Copy);
        if ($r === false) {
            CPOS_Logger::error('Failed to upload image file to S3. path=' . $path . ' dest=' . $destPath);
            return false;
        }
        CPOS_Logger::info('S3: uploaded. ' . $destPath);
        @unlink($path);
        return $r;
    }
}