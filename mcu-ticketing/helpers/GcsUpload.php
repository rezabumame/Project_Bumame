<?php

class GcsUpload {

    public static function isEnabled() {
        return !empty(getenv('GCS_BUCKET'));
    }

    public static function upload($tempFilePath, $objectPath) {
        $bucketName = getenv('GCS_BUCKET');
        if (!$bucketName) {
            throw new RuntimeException('GCS_BUCKET is not set');
        }
        $storage = new \Google\Cloud\Storage\StorageClient();
        $bucket = $storage->bucket($bucketName);
        $bucket->upload(fopen($tempFilePath, 'r'), ['name' => $objectPath]);
        return $objectPath;
    }

    public static function getSignedUrl($objectPath, $expiresInMinutes = 15) {
        $bucketName = getenv('GCS_BUCKET');
        if (!$bucketName) {
            return null;
        }
        try {
            $storage = new \Google\Cloud\Storage\StorageClient();
            $bucket = $storage->bucket($bucketName);
            $object = $bucket->object($objectPath);
            return $object->signedUrl(new \DateTime('+' . $expiresInMinutes . ' minutes'));
        } catch (\Exception $e) {
            return null;
        }
    }
}
