# yii2-dbfiles

## Базовая конфигурация

Необходимо выполнить миграцию:

`yii migrate --migrationPath=@mheads/dbfiles/migrations`

После миграции просто измените конфигурацию приложения следующим образом:

```php
return [
    'components' => [
        ...
		'dbFileStorage' => [
			'class' => 'mheads\dbfiles\FileStorage',
			'basePath' => '@frontend/web/upload',
			'host' => $params['public_host'],
			// Полный нобор свойств и их описания 
			// можно посмотреть в классе
			// mheads\dbfiles\FileStorage
		]
		...
    ],
];
```

## Использование 

Сохранение любого файла:
```php
$uploadedFile = yii\web\UploadedFile::getInstanceByName('file');
$file = Yii::$app->dbFileStorage->saveFile($uploadedFile, [
	'group_name'  => 'docs',
	'description' => 'File description',
]);
if(!$file->hasErrors())
{
	echo $file->id;
	echo $file->url;
	echo $file->path;
}
```

Сохранение изображения:
```php
$oldFileId = 2;
$uploadedImage = yii\web\UploadedFile::getInstanceByName('image');
$image = Yii::$app->dbFileStorage->saveFile($uploadedImage, [
	'deleteFileId' => $oldFileId,
	'group_name'   => 'images',
	'description'  => 'Image description',
]);
if(!$image->hasErrors())
{
	echo $image->id;
	echo $image->url;
	echo $image->path;
}
```

Получение файла:
```php
$file = Yii::$app->dbFileStorage->getFile(123);
echo $file->id;
echo $file->url;
echo $file->path;
```

Удаление файла:
```php
Yii::$app->dbFileStorage->deleteFile(123);
```