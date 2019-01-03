<?php

namespace ibunao\apidoc\models;

use yii\base\Component;

class ActionModel extends Component
{
	private $_rfMethod;
	
	/**
	 * 接口方法名,	如果有action进行去除
	 */
	private $_name;
	
	/**
	 * 接口名称,中文注释
	 */
	private $_title;
	
	/**
	 * 请求方法
	 */
	private $_method;
	
	/**
	 * 接口参数
	 */
	private $_params = [];
	
	/**
	 * 接口参数默认值
	 */
	private $_paramsDefaultValues = [];
	
	/**
	 * 路由
	 */
	private $_route;
	
	/**
	 * 接口作者
	 */
	private $_author;
	
	/**
	 * 简介
	 */
	private $_uses;
	
	/**
	 * 数据库中保存的扩展内容
	 */
	public $data;
	
	public function __construct(\ReflectionMethod $method)
	{
		# 保存方法的反射对象
		$this->_rfMethod = $method;
		parent::__construct([]);
	}

	public function init()
	{
		# 获取方法的名字
		$this->_name = $this->_rfMethod->name;
		# 获取方法的参数,返回一组 ReflectionParameter 对象表示每一参数 
		$params = $this->_rfMethod->getParameters();
		foreach ($params as $p) {
			# 判断参数是否有默认的值,有返回true
			if ($p->isDefaultValueAvailable()) {
				# 存储默认的参数名和参数值
				$this->_paramsDefaultValues[$p->getName()] = $p->getDefaultValue();
			}
		}
		# 获取方法的注释
		$comment = $this->_rfMethod->getDocComment();
		/**
		 * 获取注释中方法的参数
		 */
		# 匹配全局的参数备注	匹配规则 `/@param 任意个任意的空白符 (任意个换行符意外的字符) 换行符/`
		# 匹配结果放进$matches中
		# $matches[0] 数组中保存完整模式的所有匹配
		# $matches[1] 数组中保存第一个子组的所有匹配
		if (preg_match_all('/@param\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			foreach ($matches[1] as $match) {
				# 通过正则表达式分隔给定字符串,取前三个
				$info = preg_split("/[\s]+/", $match, 3);
				$param = [
					'type' => isset($info[0]) ? $info[0] : '',
					'name' => isset($info[1]) ? $info[1] : '',
					'desc' => isset($info[2]) ? $info[2] : '',
				];
				$this->_params[] = $param;
			}
		}
		/**
		 * 获取注释中的方法名
		 */
		# 匹配一次或零次
		if (preg_match('/@name\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			# 去除两端的空白字符
			# " " (ASCII 32 (0x20))，普通空格符。
			#  "\t" (ASCII 9 (0x09))，制表符。
			#  "\n" (ASCII 10 (0x0A))，换行符。
			#  "\r" (ASCII 13 (0x0D))，回车符。
			#  "\0" (ASCII 0 (0x00))，空字节符。
			#  "\x0B" (ASCII 11 (0x0B))，垂直制表符。
			$this->_title = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_title = $this->_rfMethod->name;
		}
		/**
		 * 获取注释中的请求方法，默认为get
		 */
		if (preg_match('/@method\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			$this->_method = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_method = 'GET';
		}
		/**
		 * 获取注释中的作者
		 */
		if (preg_match('/@author\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			$this->_author = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_author = '';
		}
		/**
		 * 获取注释中的简介
		 */
		if (preg_match('/@uses\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			$this->_uses = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_uses = '';
		}
		/**
		 * 获取模块名 将BeginRun变成begin-run
		 */
		# 通过\将通过反射方法获取的类全名(包括命名空间)进行分割
		$ms = explode("\\", $this->_rfMethod->class);
		# 模块名
		$moduleName = null;
		for ($i=0; $i < count($ms); $i++) { 
			# 等于modules或module 并且 这两个不是在最后一位
			if(($ms[$i] == "modules" || $ms[$i] == "module") && $i < count($ms) - 1) {
				//将大写字母转换为  -小写 
				$moduleName = trim(preg_replace_callback('/([A-Z])/', function($matches){
					return '-' . strtolower($matches[0]);
				}, $ms[$i+1]), '-');
				break;
			}
		}
		/**
		 * 获取类名
		 */
		$className = $ms[count($ms) - 1];
		$controllerId = trim(preg_replace_callback('/([A-Z])/', function($matches){
			return '-' . strtolower($matches[0]);
		}, substr($className, 0, strlen($className) - 10)), '-');
		/**
		 * 获取方法名部分 	去除action部分
		 */
		$actionId = trim(preg_replace_callback('/([A-Z])/', function($matches){
			return '-' . strtolower($matches[0]);
		}, substr($this->_name, 6)), '-');
		if(empty($moduleName)) {
			$this->_route = "{$controllerId}/{$actionId}";
		} else {
			$this->_route = "{$moduleName}/{$controllerId}/{$actionId}";
		}
	}
	/**
	 * 根据参数返回参数的默认值
	 */
	public function getParamDefaultValue($paramName)
	{
		return isset($this->_paramsDefaultValues[$paramName]) ? $this->_paramsDefaultValues[$paramName] : '';
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function getTitle()
	{
		return $this->_title;
	}
	
	public function getMethod()
	{
		return strtoupper($this->_method);
	}
	
	public function getParams()
	{
		return $this->_params;
	}

	public function getParamsDefaultValues()
	{
		return $this->_paramsDefaultValues;
	}
	
	public function getRoute()
	{
		return $this->_route;
	}
	
	public function getAuthor()
	{
		return $this->_author;
	}
	
	public function getUses()
	{
		return $this->_uses;
	}
}