
@inject('YunShop', '\YunShop')
<script>var require = { urlArgs: 'v={{date('YmdH')}}' };
  if(navigator.appName == 'Microsoft Internet Explorer'){
    if(navigator.userAgent.indexOf("MSIE 5.0")>0 || navigator.userAgent.indexOf("MSIE 6.0")>0 || navigator.userAgent.indexOf("MSIE 7.0")>0) {
      alert('您使用的 IE 浏览器版本过低, 推荐使用 Chrome 浏览器或 IE8 及以上版本浏览器.');
    }
  }
/*
  window.sysinfo = {
  'uniacid': '{{YunShop::app()->uniacid ?? ''}}',
  'acid': '{{YunShop::app()->acid ?? ''}}',
  'openid': '{{YunShop::app()->openid ?? ''}}',
  'uid': '{{YunShop::app()->uid ?? ''}}',
  'siteroot': './',
  //  'siteurl': '{{YunShop::app()->siteurl ?? ''}}',
    //'attachurl': '{{YunShop::app()->attachurl ?? ''}}',
   // 'attachurl_local': '{{YunShop::app()->attachurl_local ?? ''}}',
   // 'attachurl_remote': '{{YunShop::app()->attachurl_remote ?? ''}}',

  'cookie' : {'pre': '{{YunShop::app()->config['cookie']['pre'] ?? ''}}'}
  };
*/
</script>
<script src="./resource/js/app/util.js"></script>
<script src="./resource/js/require.js" ></script>
<script src="./resource/js/app/config.js" ></script>
<script src="{{static_url('resource/js/jquery-1.11.1.min.js')}}"></script>
<!--[if lt IE 9]>
<script src="{{static_url('resource/js/html5shiv.min.js')}}"></script>
<script src="{{static_url('resource/js/respond.min.js')}}"></script>
<![endif]-->