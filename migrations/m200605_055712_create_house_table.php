<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%house}}`.
 */
class m200605_055712_create_house_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%house}}', [
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
            'idx-house-CODE',
            'house',
            'CODE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-house-CODE',
            'house'
        );
        $this->dropTable('{{%house}}');
    }
}
