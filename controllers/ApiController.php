<?php
namespace ibunao\apidoc\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use ibunao\apidoc\models\ActionModel;
use ibunao\apidoc\models\DocumentApi;

/**
 * Document controller
 */
class ApiController extends Controller
{
	# 不使用公共模板
	public $layout = false;
	
	/**
	 * 获得请求对象
	 */
	public function getRequest()
	{
		return \Yii::$app->getRequest();
	}
	
	/**
	 * 获得返回对象
	 */
	public function getResponse()
	{
		return \Yii::$app->getResponse();
	}

	/**
	 * 文档首页
	 */
	public function actionIndex()
	{
		# 获取要访问的地址参数
		# 例如下面的示例
		# ?action=credit\controllers\TestController::actionValidateMobile
		$action = $this->request->get('action');
		$navItems = [];
		$currentAction = null;
		$debugRoute = $debugUrl = '';
		# 获取配置数据
		$configs = Yii::$app->params['apiList'];
		foreach ($configs as $config) {
			$items = [];
			/**
			 * 使用反射获取类中的信息
			 */
			$rf = new \ReflectionClass($config['class']);
			# 获取公共方法
			$methods = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				# 判断是否是action开头的动作
				if (strpos($method->name, 'action') === false || $method->name == 'actions') {
					continue;
				}
				# 通过传入的反射方法获取一些需要的数据
				$actionModel = new ActionModel($method);
				# 如果没有备注方法名,title和方法名name一样
				# 如果注释中没有 @name 则不会展示这个方法  
				if($actionModel->getTitle() == $method->name) {
					continue;
				}
				# 是否是当前访问的
				$active = false;
				# 如果是接收参数, 获取调试接口
				//例如?action=credit\modules\degree\controllers\CollectController::actionLogin
				if ($action) {
					list($class, $actionName) = explode('::', $action);
					# 如果url获取的路由信息 匹配 获取路由信息
					if ($class == $config['class'] && $actionName == $method->name) {
						$currentAction = $actionModel;
						# 路由地址
						$debugRoute = $actionModel->getRoute();

						$debugUrl = $this->module->debugHost . '/' . $debugRoute;
					
						$active = true;
					}
				}
				# 子列表
				$items[] = [
					'label' => $actionModel->getTitle(),//备注的方法名
					'url' => Url::to(['', 'action' => "{$config['class']}::{$method->name}"]),//生成,例如action=credit\modules\degree\controllers\CollectController::actionLogin
					'active' => $active,
				];
			}
			# 列表
			$navItems[] = [
				'label' => $config['label'],
				'url' => '#',
				'items' => $items
			];
		}
		// 获取保存的数据
		if ($currentAction) {
			$api = DocumentApi::findOne(['name' => $action]);
			$api || $api = new DocumentApi();//这个貌似不太对,也没有用.
			$currentAction->data = [
				'response' => $api->response,
				'desc' => $api->desc,
			];
		}
		
		return $this->render('api', [
			'action' => $action, # 当前请求action参数
			'navItems' => $navItems,# 父列表
			'model' => $currentAction,# 点击的子列表
			'debugRoute' => $debugRoute,# 路由地址
			'debugUrl' => $debugUrl,# 请求地址
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