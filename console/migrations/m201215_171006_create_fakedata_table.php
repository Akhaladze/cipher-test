<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%fakedata}}`.
 */
class m201215_171006_create_fakedata_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        

        $this->createTable('{{%fakedata}}', [
            'id' => $this->primaryKey(),
            'type' => $this->integer(),
            'worker' => $this->integer(),
            'payload' => $this->text(),
            'signature' => $this->string(),
            'signok' => $this->integer(),
            'signcheck' => $this->integer(),
            'status' => $this->string()]
            ,$tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%fakedata}}');
    }
}
