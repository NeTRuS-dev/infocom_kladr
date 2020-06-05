<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%city}}`.
 */
class m200605_055650_create_city_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%city}}', [
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
            'idx-city-CODE',
            'city',
            'CODE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-city-CODE',
            'city'
        );
        $this->dropTable('{{%city}}');
    }
}
