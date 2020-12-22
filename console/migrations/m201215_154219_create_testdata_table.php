<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%testdata}}`.
 */
class m201215_154219_create_testdata_table extends Migration
{ 
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        

        $this->createTable('{{%testdata}}', [
            'id' => $this->primaryKey(),
            'user' => $this->string()->notNull(),
            'session_counter' => $this->integer(),
            'session_cipher' => $this->string(),
            'request_string' => $this->string(),
            'response_string' => $this->text(),
            'data' => $this->string()]
            , $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%testdata}}');
    }
}
