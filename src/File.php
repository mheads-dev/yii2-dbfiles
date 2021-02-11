<?php
/**
 * Created by PhpStorm.
 * User: Alexeenko Sergey Aleksandrovich
 * Email: sergei_alekseenk@list.ru
 * Company: http://machineheads.ru
 * @see https://github.com/mheads-dev/yii2-dbfiles
 * Date: 19.03.2019
 * Time: 10:13
 */

namespace mheads\dbfiles;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%file}}".
 *
 * @property int $id
 * @property int $is_private
 * @property string $subdir
 * @property string $name
 * @property string $original_name
 * @property string $group_name
 * @property int $height
 * @property int $width
 * @property string $file_size
 * @property string $content_type
 * @property string $description
 * @property int $created_at
 * @property int $updated_at
 *
 * @property UploadedFile $file - Write-only property
 * @property string|null $path - Read-only property
 * @property string|null $url - Read-only property
 */
class File extends \yii\db\ActiveRecord
{
	/** @var UploadedFile */
	protected $file;

	/** @var string|null */
	protected $path;

	/** @var string|null */
	protected $url;

	/** @var array|string */
	protected $validateExtensions;
	/** @var bool */
	protected $validateCheckExtensionByMimeType = true;
	/** @var array|string */
	protected $validateMimeTypes;
	/** @var int */
	protected $validateMinSize;
	/** @var int */
	protected $validateMaxSize;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%file}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		$rules = [
			['is_private', 'default', 'value' => 0],
			[['is_private', 'height', 'width', 'file_size', 'created_at', 'updated_at'], 'integer'],
			[['subdir', 'name', 'original_name', 'content_type', 'description'], 'string', 'max' => 255],
			[['group_name'], 'string', 'max' => 50],
			[
				'file',
				'required',
				'enableClientValidation' => false,
				'when'                   => function ($model) {
					/* @var $model $this */
					return $model->getIsNewRecord();
				},
			],
			'file' => ['file', 'file', 'skipOnEmpty' => true],
		];

		if($this->validateExtensions !== NULL)
		{
			$rules['file']['extensions'] = $this->validateExtensions;
		}
		if($this->validateMimeTypes !== NULL)
		{
			$rules['file']['mimeTypes'] = $this->validateMimeTypes;
		}
		if($this->validateMinSize !== NULL)
		{
			$rules['file']['minSize'] = $this->validateMinSize;
		}
		if($this->validateMaxSize !== NULL)
		{
			$rules['file']['maxSize'] = $this->validateMaxSize;
		}
		if($this->validateCheckExtensionByMimeType !== NULL)
		{
			$rules['file']['checkExtensionByMimeType'] = $this->validateCheckExtensionByMimeType;
		}

		return $rules;
	}

	public function behaviors()
	{
		return [
			TimestampBehavior::class,
		];
	}

	/**
	 * @param $file UploadedFile
	 */
	public function setFile($file)
	{
		$this->file = $file;
		$this->prepareFileProperties();
	}

	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return string|null
	 */
	public function getPath()
	{
		$fileStorage = $this->getFileStorage();
		if(!$this->subdir || !$this->name)
		{
			return NULL;
		}

		if(!$this->path)
		{
			$this->path = $fileStorage->getFileFullPath($this->subdir.'/'.$this->name, (bool)$this->is_private);
		}
		return $this->path;
	}

	public function getUrl()
	{
		if($this->is_private)
		{
			return NULL;
		}

		$fileStorage = $this->getFileStorage();
		if(!$this->subdir || !$this->name)
		{
			return NULL;
		}

		if(!$this->url)
		{
			$this->url = $fileStorage->getFileFullUrl($this->subdir.'/'.$this->name);
		}
		return $this->url;
	}

	/**
	 * @param $value array|string
	 */
	public function setValidateExtensions($value)
	{
		$this->validateExtensions = $value;
	}

	/**
	 * @param $value bool
	 */
	public function setValidateCheckExtensionByMimeType($value)
	{
		$this->validateCheckExtensionByMimeType = $value;
	}

	/**
	 * @param $value array|string
	 */
	public function setValidateMimeTypes($value)
	{
		$this->validateMimeTypes = $value;
	}

	/**
	 * @param $value int
	 */
	public function setValidateMinSize($value)
	{
		$this->validateMinSize = $value;
	}

	/**
	 * @param $value int
	 */
	public function setValidateMaxSize($value)
	{
		$this->validateMaxSize = $value;
	}

	public function save($runValidation = true, $attributeNames = NULL)
	{
		$fileStorage = $this->getFileStorage();

		if($runValidation && !$this->validate($attributeNames))
		{
			return false;
		}

		if($this->file)
		{
			try
			{
				if(!$this->getIsNewRecord())
				{
					$oldFilePath = $fileStorage->getFileFullPath(
						$this->getOldAttribute('subdir').'/'.$this->getOldAttribute('name'),
						(bool)$this->is_private
					);
				}

				$filePath = $this->getPath();
				$filePathDir = pathinfo($filePath, PATHINFO_DIRNAME);
				if(!is_dir($filePathDir))
				{
					FileHelper::createDirectory($filePathDir);
				}
				if(!$this->file->saveAs($filePath))
				{
					throw new \Exception();
				}
			}
			catch(\Exception $e)
			{
				$this->addError('file', 'failed to save file');
				return false;
			}
		}

		$isSaved = parent::save(false);
		if($isSaved)
		{
			if($this->file && !empty($oldFilePath))
			{
				FileHelper::unlink($oldFilePath);
			}
		}
		else
		{
			if($this->file && !empty($filePath))
			{
				FileHelper::unlink($filePath);
			}
		}
		unset($this->file);

		return $isSaved;
	}

	public function delete()
	{
		$oldFilePath = $this->getPath();
		$isDelete = parent::delete();
		if($isDelete)
		{
			try
			{
				FileHelper::unlink($oldFilePath);
			}
			catch(\Exception $e){}
		}

		return $isDelete;
	}

	protected function prepareFileProperties()
	{
		if($this->file)
		{
			$fileStorage = $this->getFileStorage();

			$this->group_name = strlen($this->group_name) > 0 ? $this->group_name:$fileStorage->defaultGroupDirName;
			$this->name = $fileStorage->normalizeBaseFileName($this->file->baseName).'.'.$this->file->extension;
			$this->subdir = $fileStorage->generateSubdir($this->name, $this->group_name, (bool)$this->is_private);
			$this->original_name = $this->file->baseName.'.'.$this->file->extension;
			$this->content_type = FileHelper::getMimeType($this->file->tempName);

			$imageInfo = getimagesize($this->file->tempName);
			if($imageInfo)
			{
				[$width, $height] = $imageInfo;

				$this->width = $width;
				$this->height = $height;
			}

			$this->file_size = $this->file->size;
		}
	}

	/**
	 * @return FileStorage
	 */
	public function getFileStorage()
	{
		return Yii::$app->dbFileStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id'            => 'ID',
			'subdir'        => 'Subdir',
			'name'          => 'Name',
			'original_name' => 'Original name',
			'group_name'    => 'Group name',
			'height'        => 'Heigh',
			'width'         => 'Width',
			'file_size'     => 'File size',
			'content_type'  => 'Content type',
			'description'   => 'Description',
			'created_at'    => 'Created at',
			'updated_at'    => 'Updated at',
		];
	}

	public function fields()
	{
		return [
			'url'       => function () {
				return $this->getUrl();
			},
			'file_name' => 'original_name',
			'file_size',
			'height',
			'width',
			'description',
		];
	}
}
