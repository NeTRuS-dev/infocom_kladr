<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%street}}`.
 */
class m200605_055702_create_street_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%street}}', [
            'id' => $this->primaryKey(),
            'NAME' => $this->string(100),
            'SOCR' => $this->string(100),
            'CODE' => $this->string(100),
            'INDEX' => $this->string(100),
            'GNINMB' => $this->string(100),
            'UNO' => $this->string(100),
            'OCATD' => $this->string(100),
        ]);
        $this->createIndex(
            'idx-street-CODE',
            'street',
            'CODE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-street-CODE',
            'street'
        );
        $this->dropTable('{{%street}}');
    }
}
