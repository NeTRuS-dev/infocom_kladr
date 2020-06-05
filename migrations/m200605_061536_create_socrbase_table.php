<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%socrbase}}`.
 */
class m200605_061536_create_socrbase_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%socrbase}}', [
            'id' => $this->primaryKey(),
            'LEVEL' => $this->string(100),
            'SCNAME' => $this->string(100),
            'SOCRNAME' => $this->string(100),
            'KOD_T_ST' => $this->string(100),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%socrbase}}');
    }
}
