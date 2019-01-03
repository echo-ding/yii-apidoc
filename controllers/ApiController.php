<?php
namespace ibunao\apidoc\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use ibunao\apidoc\models\ActionModel;
use ibunao\apidoc\models\DocumentApi;

class ApiController extends Controller
{
	# 不使用公共模板
	public $layout = false;
	
	public function getRequest()
	{
		return \Yii::$app->getRequest();
	}
	
	public function getResponse()
	{
		return \Yii::$app->getResponse();
	}

	/**
	 * 文档首页
	 */
	public function actionIndex()
	{
		$action = $this->request->get('action');
		$navItems = [];
		$currentAction = null;
		$debugRoute = $debugUrl = '';
		# 获取配置数据
		$configs = Yii::$app->params['apiList'];
		foreach ($configs as $config) {
			$items = [];
			$rf = new \ReflectionClass($config['class']);
			# 获取公共方法
			$methods = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				if (strpos($method->name, 'action') === false || $method->name == 'actions') {
					continue;
				}
				$actionModel = new ActionModel($method);
				
				if($actionModel->getTitle() == $method->name) {
					continue;
				}
				$active = false;
				if ($action) {
					list($class, $actionName) = explode('::', $action);
					if ($class == $config['class'] && $actionName == $method->name) {
						$currentAction = $actionModel;
						$debugRoute = $actionModel->getRoute();

						$debugUrl = $this->module->debugHost . '/' . $debugRoute;
					
						$active = true;
					}
				}
				$items[] = [
					'label' => $actionModel->getTitle(),
					'url' => Url::to(['', 'action' => "{$config['class']}::{$method->name}"]),
					'active' => $active,
				];
			}
			$navItems[] = [
				'label' => $config['label'],
				'url' => '#',
				'items' => $items
			];
		}
		if ($currentAction) {
			$api = DocumentApi::findOne(['name' => $action]);
			$api || $api = new DocumentApi();
			$currentAction->data = [
				'response' => $api->response,
				'desc' => $api->desc,
			];
		}
		
		return $this->render('api', [
			'action' => $action, 
			'navItems' => $navItems,
			'model' => $currentAction,
			'debugRoute' => $debugRoute,
			'debugUrl' => $debugUrl,
		]);
	}
	
	/**
	 * 保存接口文档信息
	 */
	public function actionSave($action)
	{
		$this->response->format = Response::FORMAT_JSON;
		$model = DocumentApi::findOne(['name' => $action]);
		if (!$model) {
			$model = new DocumentApi();
		}
		if ($model->load($this->request->post()) && $model->validate()) {
			$model->name = $action;
			if ($model->save()) {
				return ['result' => true];
			}
		}
		return ['result' => false];
	}
	
}