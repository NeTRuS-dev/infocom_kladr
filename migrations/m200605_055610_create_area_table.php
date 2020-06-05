<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%area}}`.
 */
class m200605_055610_create_area_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%area}}', [
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
            'idx-area-CODE',
            'area',
            'CODE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-area-CODE',
            'area'
        );
        $this->dropTable('{{%area}}');
    }
}
