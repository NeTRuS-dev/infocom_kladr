<?php

use yii\db\Migration;

/**
 * Class m200606_160742_create_district_index
 */
class m200606_160742_create_district_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex(
            'idx-district-CODE',
            'district'
        );
        $this->createIndex(
            'idx-district-CODE-SOCR',
            'district',
            ['CODE', 'SOCR']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-district-CODE-SOCR',
            'district'
        );
        $this->createIndex(
            'idx-district-CODE',
            'district',
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
        echo "m200606_160742_create_district_index cannot be reverted.\n";

        return false;
    }
    */
}
