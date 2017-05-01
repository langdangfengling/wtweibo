/**
 * Created by wt on 2017/4/30 0030.
 */
/*
*发布文章*
*/
$(function() {
   var editor;
   KindEditor.ready(function (K) {
      editor = K.create('#editor_content', {
         allowFileManager: true, //true或false，true时显示浏览服务器图片功能。
         shadowMode: true,
      });
      K('input[name=getHtml]').click(function (e) {
         alert(editor.html());
      });
      K('input[name=isEmpty]').click(function (e) {
         alert(editor.isEmpty());
      });
      K('input[name=getText]').click(function (e) {
         alert(editor.text());
      });
      K('input[name=selectedHtml]').click(function (e) {
         alert(editor.selectedHtml());
      });
      K('input[name=setHtml]').click(function (e) {
         editor.html('<h3>Hello KindEditor</h3>');
      });
      K('input[name=setText]').click(function (e) {
         editor.text('<h3>Hello KindEditor</h3>');
      });
      K('input[name=insertHtml]').click(function (e) {
         editor.insertHtml('<strong>插入HTML</strong>');
      });
      K('input[name=appendHtml]').click(function (e) {
         editor.appendHtml('<strong>添加HTML</strong>');
      });
      K('input[name=clear]').click(function (e) {
         editor.html('');
      });
   });

   $('.fast_send').click(function () {
      //发布文章弹出框居中显示
      var left = ($(window).width() - $('#subArticle').width()) / 2;
      var top = $(document).scrollTop() + ($(window).height() - $('#subArticle').height()) / 2;
      var obj = $('#subArticle').show().css({
         'left': left,
         'top': top,
      });
      createBg('article_bg');
      drag(obj, obj.find('#dialog_head'));
   });

   //关闭文章发布框
   $('.close').click(function () {
      var obj = $('#subArticle');
      obj.hide();
      $('#article_bg').remove();
   });

//异步添加文章分类
   $('input.add').click(function () {
      var name = $(this).parent().prev().find('input.ain').val();
      var selectObj = $(this).parent().prev().prev().find("select[name='gid']");
      //var index=selectObj.selectedIndex;//获取当前选中序号
      //alert(name);
      $.post(addArticleGroup, {name: name}, function (data) {
         //console.log(data);
         if (data.status) {
            //第一种选中写法
            var str = '';
            str += '<option value="' + data.gid + '" selected="selected" style="background:#f7f7f7;color:#999999;">' + name + '</option>';
            selectObj.append(str);
            //第二种选中写法
            //selectObj.options[index]=new Option(name,data.gid);
            //selectObj.options[index].selected=true;
            successInfo('添加文章分类成功!');
         } else {
           errorInfo('添加文章分类失败!');
         }
      }, 'json');
   });

   //文章表单提交验证
  $('#sub').bind('click',function(){
     if($("input[name='title']").val()==""){
       noticInfo('文章标题不能为空');
        return false;
     }
     if($("input[name='title']").val().length>45){
        noticInfo('文章标题过长');
        return false;
     }
     if($("select[name='gid']").val()==null){
        noticInfo('请选择文章所属分类');
        return false;
     }
     if(editor.html()=="" || editor.text()==""){
        noticInfo('请输写你要发表的文章内容！');
        return false;
     }
  });
//消息提示框效果函数
   function successInfo(msg) {
      jSuccess(msg, {
         VerticalPosition: 'center',
         HorizontalPosition: 'center'
      });
   }
   //autoHide	是否自动隐藏提示条	true
   //clickOverlay	是否单击遮罩层才关闭提示条	false
   //MinWidth	最小宽度	200
   //TimeShown	显示时间：毫秒	1500
   //ShowTimeEffect	显示到页面上所需时间：毫秒	200
   //HideTimeEffect	从页面上消失所需时间：毫秒	200
   //LongTrip	当提示条显示和隐藏时的位移	15
   //HorizontalPosition	水平位置:left, center, right	right
   //VerticalPosition	垂直位置：top, center, bottom	bottom
   //ShowOverlay	是否显示遮罩层	true
   //ColorOverlay	设置遮罩层的颜色	#000
   //OpacityOverlay	设置遮罩层的透明度	0.3
   function noticInfo(msg) {
      jNotify(msg);
   }

   function errorInfo(msg) {
      jError(msg);
   }

 function treeInfo(){
      jSuccess("操作成功，2秒后显示下一个提示框!!", {
         TimeShown: 2000,
         onClosed: function () {
            jNotify("注意：点击这里显示下一个提示框", {
               VerticalPosition: 'top',
               autoHide: false,
               onClosed: function () {
                  jError("出错啦! 演示结束,<br /> 请点击背景层关闭提示框。", {
                     clickOverlay: true,
                     autoHide: false,
                     HorizontalPosition: 'left'
                  });
               }
            });
         }
      });
   }
});