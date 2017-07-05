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
      //alert(name);
      var selectObj = $(this).parent().prev().prev().find("select[name='gid']");
      if(name == ""){
         noticInfo('请写入你要添加的类别!');
         return false;
      }
      //var index=selectObj.selectedIndex;//获取当前选中序号
      //alert(name);
      $.post(addArticleGroup, {name: name}, function (data) {
         console.log(data);
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
    //快速发布，发布框获取焦点
    $('li.comment,div.info b').click(function(){
       $('#comment_send').find('textarea').focus().css({
          color:'#333333',
          borderColor:'#FFB941',
       })
    });

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
         //评论框需清空
         $(this).parents('.comment_conWrap').next().find('textarea').val('');
      }).trigger('click');;
   });
  //子评论回复
   $('.comment_action_sub a.reply').live('click',function(){
      //回复框
      $(this).toggle(function(){
         //评论框显现并获得焦点
         $(this).parents('.comment_conWrap').next().show();
         //隐藏已打开的评论框
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
      var textareaObj=$(this).parent().prev();
      var textarea_content=textareaObj.val();
      //alert(textarea_content);
      var fid=replyObj.attr('fid');
      //alert(fid);
      var ulParent=$('#comment_wrap').find('ul');
      //var divParent=$('#blockquote_wrap'); //这里不能这样写，必须指定当前回复的blockquote_wrap parent元素
      var divParent=$(this).parents('.blockquote_wrap');
      if(textarea_content=='发布评论' || textarea_content =="" ){
         noticInfo('请说点什么吧!');
         //获取焦点
         $(this).parent().prev().focus();
         return false;
      }
      //异步提交
      var articel_aid=$(this).parents('#news').attr('aid');
      var manager_aid=$(this).parents('.comment_conBox').find('a').attr('aid');
      //文章发布者
      var uid=$(this).parents('#news').attr('uid');
      //alert(manager_aid);
      var aid=articel_aid?articel_aid:manager_aid;
      $.post(sendComment,{aid:aid,content:textarea_content,fid:fid,uid:uid},function(data){
         //console.log(data);
         if(data != 'false'){
            if(data == -1){
               replyObj.hide();
               $('#phiz').hide();
               textareaObj.val('');
               successInfo('回复成功!');
            }
            if(fid === '0' && data != -1) {
               ulParent.prepend(data);
               //隐藏表情框
               $('#phiz').hide();
               //评论框内容清空
               textareaObj.val('');
               successInfo('评论成功!该文章评论数+<strong>1</strong>');
            }
            if(fid !== '0' && data != -1){
               //console.log(data);
               divParent.prepend(data);
               replyObj.hide();
               $('#phiz').hide();
               textareaObj.val('');
               successInfo('已回复!');
            }
         }else{
            errorInfo('评论失败，请稍后再试试吧!')
         }
      },'html');
   });
//评论异步删除
   $('a.del_comment').bind({
      mouseout:function(){
         $(this).css('color','darkgrey');
      },
      mouseover:function(){
         $(this).css('color','red');
      },
      click:function(){
         var id=$(this).attr('id');
         var fid=$(this).attr('fid');
         //alert(fid);
         var isdel=confirm('确定删除?');
         var obj=$(this).parents('.comment_list');
         if(isdel){
            $.post(delComment,{id:id,fid:fid},function(data){
               if(data){
               obj.slideUp('slow',function(){
                  obj.remove();
               });
               }else{
                  errorInfo('删除失败!');
               }
            },'json');
         }
      },

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
    /**
     * 文章收藏
     */
   $('li.keep').click(function(){
      var article_uid=$(this).parents('#news').attr('uid');
      if(uid == article_uid) {
         noticInfo('不能收藏自己文章');
         return;
      }
         var aid = $(this).attr('aid');
         $.post(keep, {aid: aid}, function (data) {
            if (data.status == 1) {
               noticInfo(data.msg + '该文章收藏数+<strong>1</strong>');
            } else if (data.status == -1) {
               noticInfo(data.msg);
            } else {
               noticInfo(data.msg);
            }
         }, 'json');
   });
  //取消收藏
   $('span.keep_cancel').hover(function(){
      $(this).css('color','red');
   },function(){
      $(this).css('color','darkgrey');
   });
   $('span.keep_cancel').click(function(){
         var isk=confirm('确定取消收藏吗?');
         var obj=$(this).parents('.wz');
         var kid=$(this).attr('kid');
          var aid=$(this).attr('aid');
      if(isk){
         $.post(keepCancel,{kid:kid,aid:aid},function(data){
            if(data){
               noticInfo('取消收藏成功!');
               obj.slideUp('slow',function(){
                  obj.remove();
               })
            }else{
               noticInfo('取消收藏失败');
            }
         },'json');
      }
   });
   /**
    * 文章删除
    */
   $('li.delete_a').click(function(){
      var isdel=confirm('确认删除该篇文章?');
      if(isdel){
         return true;
      }else{
         return false;
      }
   });

   /**
    * 文章转发
    */
   var aid='';
   $('li.turn').click(function(){
      var article_uid=$(this).parents('#news').attr('uid');
      if(uid == article_uid) {
         noticInfo('不能转发自己文章');
         return;//return 具有阻止函数的运行并不是 return false 还是ture
      }

         aid = $(this).attr('aid');
      alert(aid);
         var isturn = confirm("确定转发该篇文章吗?");
         if (isturn) {
            var left = ($(window).width() - $('#alter').width()) / 2;
            var top = $(document).scrollTop() + ($(window).height() - $('#alter').height()) / 2;
            $('#turn').show().css({left: left, top: top});
            createBg('turn_bg');
         }
   });
   //转发确定
  $('span.turn-sure').click(function(){
     //alert(111);
     //获取分类id
     var gid=$('#turn').find('.name').val();
     //alert(aid);
     if(gid){
        $.post(turn,{aid:aid,gid:gid},function(data){
           //console.log(data);
           if(data){
              noticInfo('恭喜,转载成功!');
              $('#turn').hide();
              $('#turn_bg').remove();

           }else{
              noticInfo('转载失败!');
           }
        },'json');
     }
  });
   //关闭
   $('span.turn-cencle').click(function(){
      $('#turn').hide();
      $('#turn_bg').remove();
   });

    /**
     * User/index页面的js处理
     */
     //文章的异步删除
   $('span.del_article').bind('click',function(){
       //alert(111);
        var aid=$(this).attr('aid');
       var isdel=confirm('确认删除该篇文章?');
      var ParentObj=$(this).parents('.wz');
      if(isdel){
         $.post(delArticle,{aid:aid},function(data){
            if(data){
                 noticInfo('删除成功!');
               ParentObj.slideUp('slow',function(){
                  ParentObj.remove();
               });
            }else{
               noticInfo('删除失败！');
            }
         },'json');
      }
   });

   //文章修改分类
   //原先文章分类id
   var gid='';
   $('.alter').click(function(){
      var left=($(window).width()-$('#alter').width())/2;
      var top=$(document).scrollTop()+($(window).height()-$('#alter').height())/2;
      $('#alter').show().css({left:left,top:top});
      createBg('alter_bg');
      //获取需要修改的文章id
      var aid=$(this).attr('aid');
      gid=$(this).attr('gid');
      //alert(aid);
      //alert(gid);
      $('#alter').find('.name').val(gid);
      $('#alter').find('input.aid').val(aid);

   });
   //文章修改分类提交
   $("form[name='form']").submit(function(){
      //修改后的分类id
      var agid=$('#alter').find('.name').val();
      //console.log(agid);
      if(agid == gid){
         $('#alter').hide();
         $('#alter_bg').remove();
         return false;
      }
      $('#alter').find('input.gid').val(agid);

   });
   //关闭文章修改框
   $('span.alter-cencle').click(function(){
      $('#alter').hide();
      $('#alter_bg').remove();
   });

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
    * 用户私信
    */
   $('span.send').click(function(){
      //让私信框居中显示
      var letterLeft=($(window).width()-$('#letter').width())/2;
      var letterTop=$(document).scrollTop()+($(window).height()-$('#letter').height())/2;
      var letterObj=$('#letter').show().css({
         left:letterLeft,
         top:letterTop,
      });
      createBg('letter-bg');
      drag(letterObj,letterObj.find('.letter_head'));
   });

   //关闭私信弹出框
   $('span.letter-cencle').click(function(){
      $('#letter').hide();
      $('#letter-bg').remove();
   });
   //私信回复
   $('.l-reply').click(function(){
      var uname=$(this).parent('.tright').prev().find('a').html();
      var letterLeft=($(window).width()-$('#letter').width())/2;
      var letterTop=$(document).scrollTop()+($(window).height()-$('#letter').height())/2;
      var letterObj=$('#letter').show().css({
         left:letterLeft,
         top:letterTop,
      }).find("input[name='name']").val(uname);
      createBg('letter-bg');
      drag(letterObj,letterObj.find('.letter_head'));
   });
   //异步删除私信
   $('span.del-letter').click(function(){
      var lid=$(this).attr('lid');
      var del=confirm('确定删除该条私信');
      var lobj=$(this).parents('dl')
      if(del){
         $.post(delLetter,{lid:lid},function(data){
            if (data) {
               lobj.slideUp('slow',function(){
                  lobj.remove();
               })
            }else{
               alert('删除失败，请重试！');
            }
         },'json')
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
/**
 * 创建全屏透明背景层
 * @param id
 */
function createBg(id){
   $('<div id="'+id+'"></div>').appendTo('body').css({//appendTo把所有匹配的元素追加到另一个指定的元素元素集合中。
      'width':$(document).width(),
      'height':$(document).height(),
      'position':'absolute',
      'top' : 0,
      'left': 0,
      'z-index':2,//层级权限
      'opacity':0.3,//透明度
      'filter':'Alpha(Opacity=30)',
      'backgroundColor' : '#000'
   });
}
/**
 * 元素拖拽
 * @param obj 拖拽的对象
 * @param element 触发拖拽的对象
 */
function drag (obj, element) {
   var DX, DY, moving;
   element.mousedown(function (event) {
      DX = event.pageX - parseInt(obj.css('left'));	//鼠标距离事件源宽度
      DY = event.pageY - parseInt(obj.css('top'));	//鼠标距离事件源高度
      moving = true;	//记录拖拽状态
   });
   $(document).mousemove(function (event) {
      if (!moving) return;
      var OX = event.pageX, OY = event.pageY;	//移动时鼠标当前 X、Y 位置
      var	OW = obj.outerWidth(), OH = obj.outerHeight();	//拖拽对象宽、高
      var DW = $(window).width(), DH = $('body').height();  //页面宽、高
      var left, top;	//计算定位宽、高
      left = OX - DX < 0 ? 0 : OX - DX > DW - OW ? DW - OW : OX - DX;
      top = OY - DY < 0 ? 0 : OY - DY > DH - OH ? DH - OH : OY - DY;
      obj.css({
         'left' : left + 'px',
         'top' : top + 'px'
      });
   }).mouseup(function () {
      moving = false;	//鼠标抬起消取拖拽状态
   });
}