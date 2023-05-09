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
            'email' =>  strtolower($user->email),
            'codice_fiscale' =>  strtoupper($user->fiscalCode),
        ];

        if (SensorSdcPusher::isDevMode()) {
            $data['nome'] = 'Luca';
            $data['cognome'] = 'Realdi';
            $data['email'] = 'lr@opencontent.it';
            $data['codice_fiscale'] = 'RLDLCU77T05G224F';
        }

        $data['_source'] = $user;
        
        return $data;
    }
}