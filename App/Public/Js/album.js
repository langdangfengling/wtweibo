/**
 * Created by wt on 2017/4/3 0003.
 * 相册
 */
$(function(){
    /*创建相册*/
$('.create-album').click(function(){
    //让添加分组div显示时居中显示
    var photoLeft=($(window).width()-$('#c-album').width())/2;
    var photoTop=$(document).scrollTop()+($(window).height()-$('#c-album').height())/2;
    //console.log(photoLeft);
    //console.log(photoTop);
    var photoObj=$('#c-album').show().css({
        'left': photoLeft,
        'top' : photoTop,
    });
    createBg('ablum-bg');
    drag(photoObj,photoObj.find('.album-head'));
});
    //异步创建相册
    $('.add-album').click(function(){
        var album=$(this).parents('#c-album');
        var obj=album.find("input[name='name']");
        var ulobj=album.prev().prev().find('.album_content ul');
        var albumInfo=album.prev().prev().find('.album_content1');
        var pname=obj.val();
        var pdepict=album.find("textarea").val();
       //为空时背景闪烁
        if(pname == ""){
            var timeOut = 0;
            var glint = setInterval(function () {
                if (timeOut % 2 == 0) {
                   obj.css('background','red');
                } else {
                    obj.css('background','#fff');
                }
                timeOut++;
                if (timeOut > 7) {
                    clearInterval(glint);
                    obj.focus();
                }
            }, 100);
            return false;
        }
        if(pname.length>45){alert('相册名称过长，请重新输入');obj.focus();return false;}
        //异步提交
        $.post(addAlbum,{name:pname,depict:pdepict},function(data){
            if(data.status){
                var str='';
                str += ' <li><div class="cover">';
                str += '<a href="http://localhost/wtweibo/index.php/User/photo/aid/'+data.aid+'.html" >';
                str += '<img src="'+PUBLIC+'/cover.png" width="160" height="150" /></a>';
                str += '<div class="set"><img src="'+PUBLIC+'/xiala2.png" width="20" height="20"/></div>';
                str += '<div class="set_con">';
                str += '<div><img src="'+PUBLIC+'/tubiao/options.png"/><p class="edit_album" aid="'+data.aid+'">编辑</p></div>';
                str += '<div><img src="'+PUBLIC+'/tubiao/reply.png"/><p>制作封面</p></div>';
                str += '<div><img src="'+PUBLIC+'/tubiao/stop.png"/><p class="edit_album" aid="'+data.aid+'">删除</p></div>';
                str += '</div></div>';
                str += '<div class="name"><sapn>'+pname+'</sapn></div></li>';
                ulobj.prepend(str);
                album.hide();
                $('#ablum-bg').remove();
                albumInfo.remove();
            }else{
                noticInfo(data.msg);
            }
        },'json');
    });

//相册操作按钮
    $('.album_content ul li').live({
        'mouseenter' : function(){
            $(this).find('.set').show();},
         'mouseleave' : function() {
                $(this).find('.set').hide();
                var obj = $(this).find('.set_con');
                if (obj.css('display') == 'block') {
                    obj.hide();
                }
            }
    });

//相册操作 编辑 删除 修改封面
    $('.set').live('click',function(e){
        //var e=e || window.e;
       $(this).toggle(function(){
           $(this).addClass('setclick');
           var obj=$(this).next();
           obj.slideDown('fast',function(){
           obj.show();});
           },
               function(){
               $(this).removeClass('setclick');
               var obj=$(this).next();
               obj.slideUp('fast',function(){
                   obj.hide();});
               }).trigger('click');//防止第一次点击无效
        //e.stopPropagation();//阻止冒泡事件
    });
   /*
   异步编辑相册
    */
    $('.edit_album').live('click',function() {
        //弹出编辑相册框 与创建相册框共用
        //让添加分组div显示时居中显示
        var album = $('#edit-album');
        var photoLeft = ($(window).width() - album.width()) / 2;
        var photoTop = $(document).scrollTop() + ($(window).height() - album.height()) / 2;
        //console.log(groupLeft);
        //console.log(groupTop);
        album.show().css({
            'left': photoLeft,
            'top': photoTop,
        });
        createBg('edit-album-bg');
        drag(album, album.find('.album-head'));
        album.find('.album-head span').html('编辑相册');
        var aid = $(this).attr('aid');
        var aname = album.find("input[name='name']");
        var depict = album.find('textarea');
        $('#edit-album form').find("input[name='aid']").val(aid);
        //异步获取需要编辑的相册信息
        $.post(getAlbum, {aid: aid}, function (data) {
            if (data != 'false') {
                aname.val(data.name);
                depict.val(data.describe);
            }
        }, 'json');
    });
    //保存更新相册信息
    $('#edit-album form').submit(function(){
        var name=$(this).find("input[name='name']").val();
        var depict=$(this).find("textarea").val();
        var aid1=$(this).find("input[name='aid']").val();
        if(name == ""){
            return false;
        }
        $(this).find("input[name='name1']").val(name);
        $(this).find("input[name='depict']").val(depict);
    });

//取消 关闭相册编辑框
    $('.edit-album-cencle').click(function(){
        var obj=$(this).parents('#edit-album');
        obj.find("input[name='name']").val('');
        obj.find('textarea').val('');
        $('#edit-album').hide();
        $('#edit-album-bg').remove();
    });

/*
异步删除相册
 */
$('.del_album').live('click',function(){
      var obj=$(this).parents('li');
     var aid=$(this).attr('aid');
    var del=confirm('确认删除该相册吗?');
    if(del){
        $.post(delAlbum,{aid:aid},function(data){
            //console.log(data);
            if(data){
                obj.fadeOut('slow',function(){
                    obj.remove();
                });
            }else{
                alert('删除失败!');
            }
        },'json');
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
/************效果函数******************/
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
        DX = event.pageX - parseInt(obj.css('left'));   //鼠标距离事件源宽度
        DY = event.pageY - parseInt(obj.css('top'));    //鼠标距离事件源高度
        moving = true;  //记录拖拽状态
    });
    $(document).mousemove(function (event) {
        if (!moving) return;
        var OX = event.pageX, OY = event.pageY; //移动时鼠标当前 X、Y 位置
        var OW = obj.outerWidth(), OH = obj.outerHeight();  //拖拽对象宽、高
        var DW = $(window).width(), DH = $('body').height();  //页面宽、高
        var left, top;  //计算定位宽、高
        left = OX - DX < 0 ? 0 : OX - DX > DW - OW ? DW - OW : OX - DX;
        top = OY - DY < 0 ? 0 : OY - DY > DH - OH ? DH - OH : OY - DY;
        obj.css({
            'left' : left + 'px',
            'top' : top + 'px'
        });
    }).mouseup(function () {
        moving = false; //鼠标抬起消取拖拽状态
    });
}