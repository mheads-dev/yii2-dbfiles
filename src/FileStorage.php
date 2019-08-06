<?php
/**
 * Created by PhpStorm.
 * User: Alexeenko Sergey Aleksandrovich
 * Email: sergei_alekseenk@list.ru
 * Company: http://machineheads.ru
 * @see https://github.com/mheads-dev/yii2-dbfiles
 * Date: 19.03.2019
 * Time: 9:10
 */

namespace mheads\dbfiles;

use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

class FileStorage extends Component
{
	/** @var string - базовый путь к папке для хранения файлов */
	public $basePath = '@webroot/upload';

	/** @var string - базовый url к файлу */
	public $baseUrl = '@web/upload';

	/** @var string - папка для хранения групп файлов по умолчанию */
	public $defaultGroupDirName = 'common';

	/** @var string - Домен сайта используется при генерации URL файла */
	public $host;

	/** @var bool - Включить протокол https. Используется при генерации URL файла */
	public $isHttps = false;

	public function normalizeBaseFileName($baseFileName)
	{
		return Inflector::slug($baseFileName);
	}

	public function init()
	{
		$this->basePath = rtrim($this->basePath, '/');
		$this->baseUrl = rtrim($this->baseUrl, '/');
		$this->defaultGroupDirName = trim($this->defaultGroupDirName, '/');
	}

	/**
	 * @param $relativePath string
	 * @return string
	 */
	public function getFileFullPath($relativePath)
	{
		return FileHelper::normalizePath(Yii::getAlias($this->basePath).'/'.ltrim($relativePath, '/'));
	}

	/**
	 * @param $relativePath string
	 * @return string
	 */
	public function getFileFullUrl($relativePath)
	{
		$url = Yii::getAlias($this->baseUrl).'/'.ltrim($relativePath, '/');

		if($this->host) $url = ($this->isHttps ? 'https':'http').'://'.$this->host.$url;
		return $url;
	}

	/**
	 * @param string $fileName
	 * @param string $groupDirName
	 *
	 * @return string
	 */
	public function generateSubdir($fileName, $groupDirName = NULL)
	{
		if(strlen($groupDirName) <= 0) $groupDirName = $this->defaultGroupDirName;
		$groupDirName = trim($groupDirName, '/');
		$basePath = Yii::getAlias($this->basePath);

		$i = 0;
		$dirAdd = '';
		while(true)
		{
			if($i < 25)
			{
				$dirAdd = substr(md5(uniqid("", true)), 0, 3);
			}
			elseif($i < 50)
			{
				$dirAdd = substr(md5(mt_rand()), 0, 3)."/".substr(md5(mt_rand()), 0, 3);
			}
			else
			{
				$dirAdd = substr(md5(mt_rand()), 0, 3)."/".md5(mt_rand());
				break;
			}

			if(!file_exists(FileHelper::normalizePath($basePath.'/'.$groupDirName.'/'.$dirAdd.'/'.$fileName)))
			{
				break;
			}
			$i++;
		}

		return $groupDirName.'/'.$dirAdd;
	}

	/**
	 * @param int $id
	 * @return File|null
	 */
	public function getFile($id)
	{
		return File::findOne((int)$id);
	}

	/**
	 * @param int $id
	 * @return ImageFile|null
	 */
	public function getImageFile($id)
	{
		return ImageFile::findOne((int)$id);
	}

	/**
	 * @param $file UploadedFile
	 * @param $params array - deleteFileId: int, fileType: string(image|file), description: string, group_name: string, validate...
	 * @return File|ImageFile
	 */
	public function saveFile(UploadedFile $file, $params = [])
	{
		/** @var $modelClass File|ImageFile */
		$modelClass = $params['fileType'] == 'image' ? ImageFile::class:File::class;

		if(!empty($deleteFileId = intval($params['deleteFileId'])))
		{
			$deleteModel = $modelClass::findOne($deleteFileId);
		}

		/** @var $model File|ImageFile */
		$model = new $modelClass();

		if(isset($params['description'])) $model->description = $params['description'];
		if(isset($params['group_name'])) $model->group_name = $params['group_name'];
		if(isset($params['validateExtensions'])) $model->setValidateExtensions($params['validateExtensions']);
		if(isset($params['validateMimeTypes'])) $model->setValidateMimeTypes($params['validateMimeTypes']);
		if(isset($params['validateMinSize'])) $model->setValidateMinSize($params['validateMinSize']);
		if(isset($params['validateMaxSize'])) $model->setValidateMaxSize($params['validateMaxSize']);
		if(isset($params['validateMinWidth'])) $model->setValidateMinWidth($params['validateMinWidth']);
		if(isset($params['validateMaxWidth'])) $model->setValidateMaxWidth($params['validateMaxWidth']);
		if(isset($params['validateMinHeight'])) $model->setValidateMinHeight($params['validateMinHeight']);
		if(isset($params['validateMaxHeight'])) $model->setValidateMaxHeight($params['validateMaxHeight']);

		$model->file = $file;

		if($model->save())
		{
			if(!empty($deleteModel)) $deleteModel->delete();
		}

		return $model;
	}

	/**
	 * @param $file UploadedFile
	 * @param $params array - deleteFileId: int, fileType: string(image|file), description: string, group_name: string, validate...
	 * @return File|ImageFile
	 */
	public function saveImageFile(UploadedFile $file, $params = [])
	{
		$params['fileType'] = 'image';
		return $this->saveFile($file, $params);
	}

	/**
	 * @param int $fileId
	 * @return bool
	 */
	public function deleteFile($fileId)
	{
		$model = File::findOne(intval($fileId));
		return $model ? $model->delete():false;
	}
}