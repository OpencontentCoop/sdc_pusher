<?php

use Opencontent\Sensor\Api\Values\Post\Field;

class SdcBinarySerializer
{
    /**
     * @param Field\Image|Field\File $file
     * @return array
     */
    public function serialize(Field $file): array
    {
        SensorSdcPusher::debug("Serialize file $file->fileName");
        $upload = [
            'name' => $file->fileName,
            'original_filename' => $file->fileName,
            'size' => $file->size,
            'protocol_required' => false,
            'mime_type' => $file->mimeType,
        ];
        $upload['_handler'] = $this->getClusterFileHandler($file->apiUrl);
        $upload['_source'] = $file;
        return $upload;
    }

    private function getClusterFileHandler($apiUrl): eZClusterFileHandlerInterface
    {
        $parts = explode('/', $apiUrl);
        $attributeIdentifier = $parts[3];
        $fileName = base64_decode($parts[4]);
        [$id, $version, $language] = explode('-', $attributeIdentifier, 3);
        $attribute = eZContentObjectAttribute::fetch($id, $version, $language);

        if ($attribute instanceof eZContentObjectAttribute) {
            if ($attribute->attribute('data_type_string') == OCMultiBinaryType::DATA_TYPE_STRING) {
                $fileInfo = OCMultiBinaryType::storedSingleFileInformation($attribute, $fileName);
            } else {
                $contentObject = $attribute->object();
                $fileInfo = $attribute->storedFileInformation(
                    $contentObject,
                    $contentObject->attribute('current_version'),
                    $attribute->attribute('language_code')
                );
            }
            return eZClusterFileHandler::instance($fileInfo['filepath']);
        }

        throw new Exception("File $apiUrl not found");
    }
}