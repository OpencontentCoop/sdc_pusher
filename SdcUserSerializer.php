<?php

use Opencontent\Sensor\Api\Values\User;

class SdcUserSerializer
{
    public function serialize(User $user): array
    {
        SensorSdcPusher::debug("Serialize user $user->id");
        $data = [
            'nome' =>  $user->firstName,
            'cognome' =>  $user->lastName,
            'cellulare' =>  $user->phone,
            'telefono' =>  $user->phone,
            'email' =>  strtolower($user->email),
            'codice_fiscale' =>  strtoupper($user->fiscalCode),
        ];

        if (SensorSdcPusher::isDevMode()) {
            $data['nome'] = 'Jonas';
            $data['cognome'] = 'Smith';
            $data['email'] = 'jonas.smith@email.de';
            $data['codice_fiscale'] = 'SMTJNS99C01XXXXK';
        }

        $data['_source'] = $user;
        
        return $data;
    }
}