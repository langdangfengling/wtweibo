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
         //指定宽度为600px，第一张小图宽不超过则显示原图，第二张大图宽超过则将宽缩放为600px
         //uploadJson : 'kindeditor/php/upload_json.php',
         fileManagerJson : 'kindeditor/php/file_manager_json.php',
         allowFileManager : true, ////true或false，true时显示浏览服务器图片功能。
         filterMode : false, //HTML特殊代码过滤
         afterBlur: function(){ this.sync(); }, //编辑器失去焦点(blur)时执行的回调函数（将编辑器的HTML数据同步到textarea）
         afterUpload : function(url, data, name) { //上传文件后执行的回调函数，必须为3个参数
            if(name=="image" || name=="multiimage"){ //单个和批量上传图片时
               var img = new Image(); img.src = url;
               img.onload = function(){ //图片必须加载完成才能获取尺寸
                  if(img.width>600) editor.html(editor.html().replace('<img src="' + url + '"','<img src="' + url + '" width="600"'))
               }
            }
         }
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


    /**
     * 文章评论
     */
    //获取焦点清空默认内容 '发布评论'
    $('textarea.textarea_comment').live({
       focus:function(){
          if($(this).val() == '发布评论'){
             $(this).val('').css({'color':'#333333'});
          }
          //转入文字时
          $(this).css('borderColor','#FFB941').keyup(function () {
             var content = $(this).val();
             //调用check函数取得当前字数
             var lengths = check(content);
             if (lengths[0] > 0) {//当前有输入内容时改变发布按钮背景
                $('.btn').css('backgroundColor', '#ff8400');
             } else {//内容为空时发布按钮背景归位
                $('.btn').css('backgroundColor', '#cbcbcb');
             };
             //最大允许输入140字个
             if (lengths[0] >= 140) {
                $(this).val(content.substring(0, Math.ceil(lengths[1])));
             }
             var num = 140 - Math.ceil(lengths[0]);
             var msg = num < 0 ? 0 : num;
             //当前字数同步到显示提示
             $('#send_num').html(msg);
          });
       },
       blur:function(){
          $(this).css('borderColor','#dcdcdc');
          if($(this).val() == ""){
             $(this).val('发布评论').css({'color':'#ccc'});
          }
       },
    });

   //评论回复按钮 隐藏 显现
   $(".comment_blockquote").live(
       {mouseenter:function() {
      $(".comment_action_sub").css({
         visibility: "hidden"
      });
      $(this).find(".comment_action_sub").css({
         visibility: "visible"
      });
   }},
   {mouseleave:function() {
      $(".comment_action_sub").css({
         visibility: "hidden"
      });
      $(this).find(".comment_action_sub").css({
         visibility: "hidden"
      });
   }});
   //主评论回复
   $('.comment_action a.reply').live('click',function(){
       //回复框
      $(this).toggle(function(){
         $(this).parents('.comment_conWrap').next().show();
         $(this).parents('li').siblings().find('.reply_area').hide();
         $('.reply_area_sub').hide();
      },function(){
         $(this).parents('.comment_conWrap').next().hide();
      }).trigger('click');;
   });
  //子评论回复
   $('.comment_action_sub a.reply').live('click',function(){
      //回复框
      $(this).toggle(function(){
         $(this).parents('.comment_conWrap').next().show();
         $(this).parents('blockquote').siblings().find('.reply_area_sub').hide();
         $('.reply_area').hide()
      },function(){
         $(this).parents('.comment_conWrap').next().hide();
      }).trigger('click');;
   });
   //点击异步提交评论 包括评论回复，两者区别就是评论fid为0，回复fid>0
   //onclick="subcomment('{$id}', '{$mtype}', '{$row.id}', '{$row2.id}')"
   $('.btn_subGrey').live('click',function(){
      //回复框
      var replyObj=$(this).parent().parent();
      var textarea_content=$(this).parent().prev().val();
      //alert(textarea_content);
      var fid=replyObj.attr('fid');
      alert(fid);
      var ulParent=$('#comment_wrap').find('ul');
      var divParent=$('#blockquote_wrap');
      if(textarea_content=='发布评论' || textarea_content =="" ){
         noticInfo('请说点什么吧!');
         //获取焦点
         $(this).parent().prev().focus();
         return false;
      }
      //异步提交
      var aid=$(this).parents('#news').attr('aid');
      //alert(aid);
      $.post(sendComment,{aid:aid,content:textarea_content,fid:fid},function(data){
         if(data != 'false'){
            if(fid === '0') {
               ulParent.prepend(data);
               successInfo('评论成功!该文章评论数+<strong>1</strong>');
            }else{
               console.log(data);
               divParent.prepend(data);
               replyObj.hide();
               successInfo('评论成功!该文章评论数+<strong>1</strong>');
            }
         }else{
            errorInfo('评论失败，请稍后再试试吧!')
         }
      },'html');
   });

//评论异步分页显示
   $('#page span').live('click',function(){
      $(this).addClass('cur1').siblings().removeClass('cur1');
      //获取当前页
      var page = $('#page span.cur1').attr('page');
      //alert(page);
      //获取文章id
      var aid = $(this).parents('#news').attr('aid');
      $.get(getComments, {page: page, aid: aid}, function (data) {
         if (data != 'false') {
            //console.log(data);
            $("#comment_wrap").html(data);
         }
      }, 'html');
   });

   ////评论回复异步提交
   //$('a.reply').live('click',function(){
   //
   //   var fid=$(this).parent().parent().attr('fid');
   //   alert(fid);
   //})

   //$(".smileBox").find("a").click(function() {
   //   var textarea_id = $("#smileBoxOuter").attr("data-id");
   //   var textarea_obj = $("#reply_" + textarea_id).find("textarea");
   //   var textarea_val = textarea_obj.val();
   //   if (textarea_val == "发布评论") {
   //      textarea_obj.val("")
   //   }
   //   var title = "[" + $(this).attr("title") + "]";
   //   textarea_obj.val(textarea_obj.val() + title).focus();
   //   $("#smileBoxOuter").hide()
   //});
   /*
    * 表情处理
    * 以原生js添加添加点击事件，不走jQuery队列事件机制
    */
   var phiz=$('.phiz');
   for(var i=0;i<phiz.length;i++){//jQuery对象转换成DOM对象,通过[index]来得到相应的DOM对象
      phiz[i].onclick=function(){
         //定位表情框到相对应的位置
         $('#phiz').show().css({
            'left':$(this).offset().left,
            'top':$(this).offset().top+$(this).height()+5,
         });
         //为每个表情添加点击事件
         var phizimg=$('#phiz img');
         var sign=this.getAttribute('sign');
         for(var n=0;n<phizimg.length;n++){
            phizimg[n].onclick=function(){
               var content=$('textarea[sign='+sign+']');
               var title=$(this).attr('title');
               content.val(content.val()+'['+title+']');
            }
         }
      }
   }
//关闭表情框
   $('span.close').hover(function(){
      $(this).css('backgroundPosition','-100px -200px');
   },function(){
      $(this).css('backgrounndPoistion','-75px -200px');
   }).click(function(){
      //$(this).parent().parent().hide();
      $('#phiz').hide();
      //if($('#turn').css('display')=='none'){
      //    $('#opacity_bg').remove();
      //}
      //if($('#model').css('display')=='none'){
      //    $('#edit_tpl_bg').remove();
      //}
   });

   /**
    * 文章转发框处理
    */
   //$('.turn').click(function(){
   //   //获取需要转发微博的相关数据，并赋值到转发框
   //   var orgObj=$(this).parents('.wb_tool').prev();
   //   var author=orgObj.find('.author').html();
   //   var content=orgObj.find('.content p').html();
   //   //获取原微博的id
   //   var id=$(this).attr('id');
   //   //如果要转发的微博不是原微博而也是转发的微博，那么就获取它转发的原始微博id，转发始终是转发最原先的那篇微博
   //   var tid=$(this).attr('tid')?$(this).attr('tid'):0;
   //   var cons='';
   //   if(tid) {
   //      var author = orgObj.find('.author a').html();
   //      cons =replace_weibo( '//@' + author + ':' + content);
   //      //alert(cons);
   //      author = $.trim(orgObj.find('.turn_name').html());
   //      content = orgObj.find('.turn_cons p').html();
   //   }
   //   $('form[name=turn] p').html(author+'::'+content);
   //   $('.turn-cname').html(author);
   //   $('form[name=turn] textarea').val(cons);
   //   //提取原微博id
   //   $('form[name=turn] input[name=id]').val(id);
   //   //提取转发微博id1
   //   $('form[name=turn] input[name=tid]').val(tid);
   //
   //   //隐藏表情框
   //   $('#phiz').hide();
   //   //点击转发创建透明背景层
   //   createBg('opacity_bg');
   //   //定位转发框居中
   //   var turnLeft=($(window).width()-$('#turn').width())/2;
   //   var turnTop=$(document).scrollTop()+($(window).height()-$('#turn').height())/2;
   //   $('#turn').css({
   //      'left':turnLeft,
   //      'top':turnTop,
   //   }).fadeIn().find('textarea').focus(function(){
   //      var content=$(this).val();
   //      var lengths=check(content);//调用check函数检查输入内容字数
   //      //最大允许输入140个字
   //      if(lengths[0]>140){
   //         $(this).val(content.substring(0,Math.ceil(lengths[1]))); //这里不是很明白
   //      }
   //      var num=140-Math.ceil(length[0]);
   //      var msg=num<0?0:num;
   //      //当前字数同步到显示提示
   //      $('$turn_num').html(msg);
   //   }).focus().blur(function(){
   //      $(this).css('borderColor','#CCCCCC');//失去焦点时还原边框颜色
   //   });
   //});
   //drag($('#turn'),$('.turn_text'));//拖拽转发框










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
/**
 * 统计字数
 * @param  字符串
 * @return 数组[当前字数, 最大字数]
 */
function check (str) {
   var num = [0, 140];
   for (var i=0; i<str.length; i++) { //charCode返回一个整数，代表指定位置上字符的 Unicode 编码。
      //字符串不是中文时
      if (str.charCodeAt(i) >= 0 && str.charCodeAt(i) <= 255){
         num[0] = num[0] + 0.5;//当前字数增加0.5个
         num[1] = num[1] + 0.5;//最大输入字数增加0.5个
      } else {//字符串是中文时
         num[0]++;//当前字数增加1个
      }
   }
   return num;
}