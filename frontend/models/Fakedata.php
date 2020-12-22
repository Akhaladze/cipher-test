<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "fakedata".
 *
 * @property int $id
 * @property int|null $type
 * @property int $worker
 * @property string $payload
 * @property string|null $signature
 * @property int|null $signok
 * @property int|null $signcheck
 * @property string|null $status
 */
class Fakedata extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fakedata';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'signok', 'signcheck', 'worker'], 'integer'],
            [['payload'], 'required'],
            [['payload', 'signature'], 'string'],
            [['status'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'worker' => 'Worker',
            'payload' => 'Payload',
            'signature' => 'Signature',
            'signok' => 'Signok',
            'signcheck' => 'Signcheck',
            'status' => 'Status',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FakedataQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FakedataQuery(get_called_class());
    }
}
