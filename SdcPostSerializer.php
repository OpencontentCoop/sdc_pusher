<?php

use Opencontent\Sensor\Api\Values\Post;

class SdcPostSerializer
{
    public static function serializaPdfDirectory($post)
    {
        return hash_hmac('sha256', $post->id, eZSolr::installationID());
    }

    public function serialize(Post $post, array $userData, array $images, array $files, string $serviceId = "inefficiencies", $pdfFileRelativePath = null): array
    {
//        $mapMicroMacro = SensorSdcPusher::$categories;
//        $mapMicroMacroHash = array_combine(
//            array_column($mapMicroMacro, 'label'),
//            array_column($mapMicroMacro, 'value')
//        );
//        $microMacro = null;
//        if (count($post->categories) > 0){
//            $category = $post->categories[0];
//            if ($category->parent == 0){
//                $microMacro = "{$category->name}: {$category->name}";
//            }else{
//                $parentCategory = OpenPaSensorRepository::instance()->getCategoryService()->loadCategory($category->parent);
//                $microMacro = "{$parentCategory->name}: {$category->name}";
//            }
//        }

        $missing = [
            'submitted_at' => $post->published->format('c'),
            'modified_at' => $post->modified->format('c'),
            'sensor_category' => count($post->categories) > 0 ? $post->categories[0]->name : null,
            'sensor_area' => count($post->areas) > 0 ? $post->areas[0]->name : null,
//            'micromacrocategory' => $microMacro,
            'id_v3' => $post->id,
            'uuid_v3' => $post->uuid,
        ];
        if (!empty($pdfFileRelativePath)){
           $missing['pdf_link'] = 'https://archivio-segnalazioni.comune.genova.it' . $pdfFileRelativePath;
        }

        $data = [
            "service" => $serviceId,
            "status" => 2000,
            'created_at' => $post->published->format('c'),
            "data" => [
                "applicant" => [
                    "data" => [
                        "email_address" => $userData['email'],
                        "phone_number" => $userData['cellulare'],
                        "completename" => [
                            "data" => [
                                "name" => $userData['nome'],
                                "surname" => $userData['cognome'],
                            ]
                        ],
                        "fiscal_code" => [
                            "data" => [
                                "fiscal_code" => $userData['codice_fiscale'],
                            ]
                        ],
                        "person_identifier" => $userData['codice_fiscale'],
                    ]
                ],
//                "type" => "70cbba61-47e4-4d85-98bf-03e4817cf272",
                "details" => $post->description,
                "subject" => $post->id . ' - ' . $post->subject,
                "meta" => $missing,
                "sequential_id" => $post->id,
            ],
        ];

//        $strict = false;
//        if ($microMacro && isset($mapMicroMacroHash[$microMacro])){
//            $setMm = true;
//            if ($strict && !isset($mapMicroMacroHash[$microMacro])){
//                $setMm = false;
//            }
//            if ($setMm) {
//                $data['data']["micromacrocategory"] = [
//                    "label" => $microMacro,
//                    "value" => $mapMicroMacroHash[$microMacro] ?? ''
//                ];
//            }
//        }

        $data["data"]["images"] = $images;
        $data["data"]["docs"] = $files;

        if ($post->geoLocation instanceof Post\Field\GeoLocation
            && $post->geoLocation->latitude != 0
            && $post->geoLocation->longitude != 0){

            $nominatim = json_decode(
                eZHTTPTool::getDataByURL(
                    "https://nominatim.openstreetmap.org/reverse?lat={$post->geoLocation->latitude}&lon={$post->geoLocation->longitude}&format=json",
                    false,
                    "Mozilla/5.0 (Linux; Android 11; Pixel 5 Build/RQ3A.210805.001.A1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/92.0.4515.159 Mobile Safari/537.36"
                ),
                true
            );

            $addressDisplayName = $post->geoLocation->address;

            $address = $nominatim['address'] ?? [];
            if (isset($address['road']) && isset($address['house_number'])){
                if (strpos($address['road'], ' ' . $address['house_number']) === false) {
                    $address['road'] = $address['road'] . ' ' . $address['house_number'] . ' Genova';
                }
            }
            $address['country'] = 'Italia';
            $address['state'] = 'Italia';
            $address['country_code'] = 'it';
            $address['county'] = 'Genoa';
            $address['city'] = 'Genova';

//            if (isset($post->meta->{'DESVIA'}) && isset(isset($post->meta->{'TESTO'}))){
//                $addressDisplayName = $road = $post->meta->{'DESVIA'} . ' ' . $post->meta->{'TESTO'};
//                $address['country'] = 'Italia';
//                $address['state'] = 'Italia';
//                $address['country_code'] = 'it';
//                $address['county'] = 'Genoa';
//                $address['road'] = $road;
//                $address['city'] = 'Genova';
//                $address['name'] = $road;
//            }

            if (stripos($addressDisplayName, 'genova') === false){
                $addressDisplayName .= ' Genova';
            }

            $data["data"]["address"] = [
                "lat" => $post->geoLocation->latitude,
                "lon" => $post->geoLocation->longitude,
                "display_name" => $addressDisplayName,
                "address" => $address,
            ];
        }

        if (!empty($userData['id'])){
            $data['user'] = $userData['id'];
        }

        return $data;
    }
}