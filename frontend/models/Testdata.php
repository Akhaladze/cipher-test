<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "testdata".
 *
 * @property string $user
 * @property int $session_counter
 * @property string|null $session_cipher
 * @property string|null $request_string
 * @property string|null $response_string
 * @property string|null $data
 * @property int $id
 */
class Testdata extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'testdata';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user', 'session_counter'], 'required'],
            [['session_counter'], 'integer'],
            [['request_string', 'response_string'], 'string'],
            [['data'], 'safe'],
            [['user', 'session_cipher'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user' => 'User',
            'session_counter' => 'Session Counter',
            'session_cipher' => 'Session Cipher',
            'request_string' => 'Request String',
            'response_string' => 'Response String',
            'data' => 'Data',
            'id' => 'ID',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TestdataQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TestdataQuery(get_called_class());
    }
}
