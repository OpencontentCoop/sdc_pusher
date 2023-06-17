<?php
// create table sdc_payload_qa as (select * from sdc_payload);
class SdcPayload extends eZPersistentObject
{
    public static function definition()
    {
        return [
            'fields' => [
                'id' => [
                    'name' => 'id',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true,
                ],
                'priority' => [
                    'name' => 'priority',
                    'datatype' => 'integer',
                    'default' => 0,
                    'required' => false,
                ],
                'type' => [
                    'name' => 'type',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => true,
                ],
                'payload' => [
                    'name' => 'payload',
                    'datatype' => 'string',
                    'default' => null,
                    'required' => false,
                ],
                'modified_at' => [
                    'name' => 'created_at',
                    'datatype' => 'integer',
                    'default' => time(),
                    'required' => false,
                ],
//                'executed_at' => [
//                    'name' => 'executed_at',
//                    'datatype' => 'integer',
//                    'default' => null,
//                    'required' => false,
//                ],
//                'result' => [
//                    'name' => 'result',
//                    'datatype' => 'string',
//                    'default' => null,
//                    'required' => false,
//                ],
//                'error' => [
//                    'name' => 'error',
//                    'datatype' => 'string',
//                    'default' => null,
//                    'required' => false,
//                ],
            ],
            'keys' => ['id'],
            'class_name' => 'SdcPayload',
            'name' => 'sdc_payload',
        ];
    }

    public static function createSchemaIfNeeded($id = null, $truncate = false, $drop = false): void
    {
        $db = eZDB::instance();
        eZDB::setErrorHandling(eZDB::ERROR_HANDLING_EXCEPTIONS);
        SensorSdcPusher::debug("Using db " . $db->DB, false);

        $tableQuery = "SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename  like 'sdc_payload';";
        $exists = array_fill_keys(array_column($db->arrayQuery($tableQuery), 'tablename'), true);
        if ($drop && isset($exists['sdc_payload'])) {
            SensorSdcPusher::debug('DROP sdc_payload', false);
            $db->query('DROP TABLE sdc_payload');
            unset($exists['sdc_payload']);
        }

        if (!isset($exists['sdc_payload'])) {
            SensorSdcPusher::debug('Create table sdc_payload', false);
            $tableCreateSql = "CREATE TABLE IF NOT EXISTS sdc_payload ( id varchar(255) NOT NULL default '', priority integer default 0, type text default '', payload text default '', modified_at integer, executed_at integer, result text, error text )";
            $db->query($tableCreateSql);
            $tableKeySql = "ALTER TABLE ONLY sdc_payload ADD CONSTRAINT sdc_payload_pkey PRIMARY KEY (id,type);";
            $db->query($tableKeySql);
        } else {
            SensorSdcPusher::debug('Table sdc_payload already exists', false);
            if ($truncate) {
                if ($id){
                    SensorSdcPusher::debug('Remove sdc_payload for id ' . $id, false);
                    $db->query("DELETE FROM sdc_payload WHERE id = '$id'");
                }else {
                    SensorSdcPusher::debug('Truncate sdc_payload', false);
                    $db->query('TRUNCATE sdc_payload');
                }
            }
        }
    }

    public static function fetch($id): ?SdcPayload
    {
        $item = eZPersistentObject::fetchObject(self::definition(), null, ['id' => $id]);
        return $item instanceof SdcPayload ? $item : null;
    }

    public static function fetchByIdAndType($id, $type): ?SdcPayload
    {
        $item = eZPersistentObject::fetchObject(self::definition(), null, ['id' => $id, 'type' => $type]);
        return $item instanceof SdcPayload ? $item : null;
    }

    public static function create(string $id, string $type, array $payload, int $priority = 0): SdcPayload
    {
        $item = new SdcPayload();
        if (mb_strlen($id) > 255){
            $id = substr($id, 0, 255);
        }
        $item->setAttribute('id', $id);
        $item->setAttribute('priority', $priority);
        $item->setAttribute('type', $type);
        $item->setAttribute('payload', json_encode($payload));
        $item->setAttribute('modified_at', time());
        $item->store();

        return $item;
    }

    public function id()
    {
        return $this->attribute('id');
    }

    public function type()
    {
        return $this->attribute('type');
    }

    public function getPayload()
    {
        return json_decode($this->attribute('payload'), true);
    }
}