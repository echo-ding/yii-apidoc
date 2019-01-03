<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<script src="/js/common/jsbeautify.js" type="text/javascript"></script>

<div class="container-fluid" style="padding:0;">
<div class="row">
	<div class="col-md-4">
		<h3>路由：<?php echo Html::encode($route); ?></h3>
		<form role="form">
		  <?php if ($model->params): ?>
		  <?php foreach ($model->params as $param): ?>
		  <div class="form-group">
		    <label>
		    	<?php echo $param['name']; ?>
		    </label>
		    <input type="text" class="form-control" name="<?php echo trim($param['name'], '$'); ?>" value="<?php echo $model->getParamDefaultValue(trim($param['name'], '$')); ?>">
		  </div>
		  <?php endforeach; ?>
		  <?php else: ?>
		  <div class="form-group">无参数</div>
		  <?php endif; ?>
		  <button id="submit-btn" type="button" class="btn btn-primary" data-loading-text="提交中..." autocomplete="off">提交</button>
		</form>
	</div>
	<div class="col-md-8" role="main">
		<h3>请求返回:</h3>
		<pre id="response">Empty.</pre>
	</div>
</div>
</div>
<script type="text/javascript">
$(function(){
	$('#submit-btn').click(function(){
		var btn = $(this).button('loading');
		var data = {};
		$('.form-control').each(function(){
			if ($(this).val() != '') {
				data[$(this).attr('name')] = $(this).val();
			}
		});
		$.ajax({
			url: '<?php echo $debugUrl; ?>',
			type: '<?php echo $model->method; ?>',
			data: data,
			success: function(retData) {
				btn.button('reset');
				if (typeof retData === 'string' && retData.indexOf('content="text/html;') != -1) {
					var url = '<?php echo $debugUrl; ?>?';
					for (key in data) {
						url += key + '=' + data[key] + '&';
					}
					window.open(url);
					$('#response').html('该接口是返回html页面，请允许浏览器弹出新页面或自行在浏览器调试');
				} else {
					if(typeof retData == 'object')
					{
						retData = JSON.stringify(retData);
					}
					var formatText = js_beautify(retData, 4, ' ');
					$('#response').html(formatText);
				}
			},
			error: function(retData) {
				btn.button('reset');
				alert('发生错误');
			}
		});
	});

});
</script>
