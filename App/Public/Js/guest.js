/**
 * 留言板
 */

$(function() {
    var oUserName = $("#userName");
    var maxNum = 140;
    var content = "";//

    //禁止表单提交
    $('form[name=guest]').submit(function () {
        return false;
    });
    //EventUtil.addHandler(get.byTagName("form", oMsgBox)[0], "submit", function () {return false});
    //为留言按钮绑定发送事件
    $('#sendBtn').click(function () {
        content = $('#conBox').val();
        if (content == "") {
            alert("\u968f\u4fbf\u8bf4\u70b9\u4ec0\u4e48\u5427\uff01");
            $(this).focus();
        }
        else if (content.length > maxNum) {
            alert("\u4f60\u8f93\u5165\u7684\u5185\u5bb9\u5df2\u8d85\u51fa\u9650\u5236\uff0c\u8bf7\u68c0\u67e5\uff01");
            $(this).focus();
        }
        else {
            fnsend();
        }
    });

    //为Ctrl+Enter快捷键绑定发送事件
    $(window.document)[0].onkeyup = function (event) {
        var event = event || window.event;
        var keycode = event.which;
        if (keycode == 13 && event.ctrlKey) {
            fnsend();
        }
    }//13为 Ctrl+enter})

    //事件绑定, 判断字符输入
    $('#conBox').keyup(function () {
        content = $(this).val();
        confine();
    });
    $('#conBox').focus(function () {
        content = $(this).val();
        confine();
    }).change(function () {
        confine();
    });

    function confine() {
        var lengths = check(content);//调用check函数检查输入内容字数
        //最大允许输入140个字
        if (lengths[0] > 140) {
            $(this).val(content.substring(0, Math.ceil(lengths[1]))); //这里不是很明白
        }
        var num = 140 - Math.ceil(lengths[0]);
        var msg = num < 0 ? 0 : num;
        if (msg == 0) {
            $('.maxNum').css({color: 'red'});
        }
        //当前字数同步到显示提示
        $('.maxNum').html(msg);
    }

    //$('#conBox').change(function () {
    //    confine();
    //});

    //留言按钮鼠标划过样式
    $('#sendBtn').mouseenter(function () {
        $(this).addClass('hover')
    });
    //留言按钮鼠标离开样式
    $('#sendBtn').mouseleave(function () {
        $(this).removeClass('hover');
    });
    //异步发送留言函数
    function fnsend() {
        //收集表单信息
        //留言者
        var guest_uid = oUserName.val();
        //留言内容
        var guest_content = content;
        //guest_content = encodeURIComponent(guest_content);//对输入内容进行编码，以免浏览把特殊符号当做地址
        //console.log(guest_content);
        //console.log(guest_uid);
        $.ajax({
            type: 'post',
            url: guestUrl,
            dataType: 'json',
            data: {uid: guest_uid, content: guest_content},
            success: function (data) {
                if (data != 'false') {
                    //console.log(data);
                    //var oDate = new Date();
                    var img = data.face ? UPLOADS + data.face : PUBLIC + "face1.gif";
                    //console.log(img);
                    $('.list ul').prepend("<li><div class=\"userPic\"><img src=\"" + img + "\" width='50' height='50'></div>\
							 <div class=\"content\">\
							 	<div class=\"userName\"><a href=\"javascript:;\">" + data.username + "</a>:</div>\
								<div class=\"msgInfo\">" + content.replace(/<[^>]*>|&nbsp;/ig, "") + "</div>\
								<div class=\"times\"><span>" + data.guesttime + "</span><a class=\"del\" href=\"javascript:;\">\u5220\u9664</a></div>\
							 </div></li>");
                    //重置表单
                    $('form[name=guest]')[0].reset();
                } else {
                    alert('留言失败')
                }
            }
        });
    }

    /**
     * 异步留言删除
     */
    $('.list ul li .content').mouseenter(function () {
        $(this).find('a.del').show();
    }).mouseleave(function () {
        $(this).find('a.del').hide();
    });
    $('a.del').click(function () {
        var gid = $(this).attr('gid');
        var obj = $(this).parents('.g');
        var isDel = confirm('确认删除该条留言吗?');
        if (isDel) {
            $.post(delGuest, {gid: gid}, function (data) {
                if (data) {
                    //删除成功
                    obj.slideUp('slow', function () {
                        obj.remove();
                    });
                    window.setTimeout('location.reload()',3000);//让当前页面停留5S刷新
                }
            }, 'json');
        } else {
            return false;
        }
    });
    /**
     * 回复框处理
     */
        //点击"回复"
    $('a.reply').click(function () {
        //隐藏其他回复框
        $('div.reply').hide();
        //回复框显现
        $(this).parents('li.g').find('.reply').show().find('textarea').addClass('fou').focus();
        //想对我说点什么输入框隐藏
        $(this).parents('li').find('div.state').hide();
    });

    // 我也说点什么 输入框
    $('input.reply').bind({
        mouseover: function () {
            $(this).addClass('hov');
        },
        mouseout: function () {
            $(this).removeClass('hov');
        },
        focus: function () {
            //隐藏其他回复框
            $('div.reply').hide();
            //隐藏我想输入框，回复框显现
            //我也说点什么显现
            $('div.state').show();
            $(this).val("").parent().hide().next().show().find('textarea').addClass('fou').focus();


        },
    });
    //回复内容计数
    var content1="";
    $('div.reply textarea').bind({
        focus:function(){
            content1=$(this).val();
            var lengths=check(content1);
            if(lengths[0]>200){
                noticInfo('你的输入内容已超出200字！');
            }
            var msg=Math.ceil(lengths[0]);
            $(this).next().find('span.n').html(msg);
        },
        keyup:function(){ content1=$(this).val();
            var lengths=check(content1);
            if(lengths[0]>200){
                noticInfo('你的输入内容已超出200字！');
            }
            var msg=Math.ceil(lengths[0]);
            $(this).next().find('span.n').html(msg);
        },
      });

    //留言回复内容发送
    $('input.sub_btn').click(function(){
        //获取数据
        var reply_content=content1;//回复内容
        //alert(content1);
        var gid=$(this).parents("li.g").attr('gid');
        var obj=$(this).parents('.reply');
        var objul=obj.prev().prev();
        if(reply_content == ""){
            noticInfo("请说点什么吧！")
            $(this).parent().prev().focus();
            return false;
        }
        var lengths=check(reply_content);
        if(lengths[0]>200){
            noticInfo('你的输入内容已超出200字！');
            return false;
        }
        //异步
        $.post(REPLYURL,{content:reply_content,gid:gid},function(data){
            //console.log(data);
             if(data.status){
                 //console.log(data);
                 obj.find('textarea').val("");
                 var img=data.face60?UPLOADS+data.face60:PUBLIC+"/face1.gif";
                 var str='';
                str += '<li>';
                str += '<div class="userPic"><img src="'+img+'" width="35" height="35"/></div>';
                str += '<div class="rcontent">';
                str += '<div><span uid="'+data.uid+'"><a href="" target="_Blank">'+data.username+'</a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="reply_content">'+data.content+'</span></div>';
                str += '<div class="times"><span>'+data.time;
                str += '</div></li>';
                 //alert(111);
                 objul.prepend(str);
             }else{alert(data.msg)}
        },'json'
        );
    });
    //回复取消发送
    $('input.cancel').click(function(){
        var obj=$(this).parents('.reply')
        obj.hide().prev().show().find('input.reply').removeClass('hov').val("我也说点什么......");
        //textarea清空
        obj.find('textarea').val("");
    });
    //异步删除留言回复内容
    $('a.delreply').live('click',function(){
        var rid=$(this).attr('rid');
        var obj=$(this).parents('.r');
        var isdel=confirm('确认要删除该条回复?');
        if(isdel){
            $.post(delReply,{rid:rid},function(data){
                if(data){
                    obj.slideUp('slow',function(){
                        obj.remove();
                    });
                }else{alert('删除回复记录失败....');}
            },'json');
        }
    });
    $('a.delreply').hover(function(){$(this).css({color:'red'})},function(){$(this).css({color:'grey'})});

    //留言内容回复计数
    $('.phiz').click(function(){
        $('#phiz').show();
    });
    /**
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



