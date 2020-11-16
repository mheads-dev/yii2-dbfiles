<?php

use yii\db\Migration;

/**
 * Class m190319_025425_file_storage_init
 */
class m201116_044252_file_storage_init extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable(
			'{{%file}}',
			[
				'id'            => $this->primaryKey(11),
				'subdir'        => $this->string(255)->null(),
				'name'          => $this->string(255)->null(),
				'original_name' => $this->string(255)->null(),
				'group_name'    => $this->string(50)->null(),
				'height'        => $this->integer(18)->null(),
				'width'         => $this->integer(18)->null(),
				'file_size'     => $this->bigInteger(20)->null(),
				'content_type'  => $this->string(255)->null(),
				'description'   => $this->string(255)->null(),
				'updated_at'    => $this->integer(11)->null(),
				'created_at'    => $this->integer(11)->null(),
			]
		);

		$this->createIndex('subdir', '{{%file}}', 'subdir');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable('{{%file}}');
	}
}
