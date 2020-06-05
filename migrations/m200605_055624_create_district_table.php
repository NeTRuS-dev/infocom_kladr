<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%district}}`.
 */
class m200605_055624_create_district_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%district}}', [
            'id' => $this->primaryKey(),
            'NAME'=>$this->string(100),
            'SOCR'=>$this->string(100),
            'CODE'=>$this->string(100),
            'INDEX'=>$this->string(100),
            'GNINMB'=>$this->string(100),
            'UNO'=>$this->string(100),
            'OCATD'=>$this->string(100),
        ]);
        $this->createIndex(
            'idx-district-CODE',
            'district',
            'CODE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-district-CODE',
            'district'
        );
        $this->dropTable('{{%district}}');
    }
}
