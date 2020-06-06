<?php

use yii\db\Migration;

/**
 * Class m200606_155656_create_house_index
 */
class m200606_155656_create_house_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex(
            'idx-house-CODE',
            'house'
        );
        $this->createIndex(
            'idx-house-CODE-NAME',
            'house',
            ['CODE', 'NAME']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-house-CODE-NAME',
            'house'
        );
        $this->createIndex(
            'idx-house-CODE',
            'house',
            'CODE'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200606_155656_create_house_index cannot be reverted.\n";

        return false;
    }
    */
}
