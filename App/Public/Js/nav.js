//var obj=null;
//var As=document.getElementById('nav').getElementsByTagName('a');
//obj = As[0];
//for(i=1;i<As.length;i++)
//{if(window.location.href.indexOf(As[i].href)>=0)
//obj=As[i];}
//obj.id='nava'
/**
 * 头部导航
 */
$(function () {
    /**
     * 头部选项移入效果
     */
        //左侧选项
    $('.top_left li').hover(function () {
        $(this).addClass('cur_bg');
    }, function () {
        $(this).removeClass('cur_bg');
    });
    //选中时固定背景样式
    $('.top_left li').click(function(){
        $(this).addClass('cur_bg1').siblings().removeClass('cur_bg1');
    })
    //用户名
    $('.user').hover(function () {
        $(this).addClass('cur_bg');
    }, function () {
        $(this).removeClass('cur_bg');
    });
    //快速发微博按钮
    $('.top_right li:eq(0)').hover(function () {
        $(this).addClass('cur_bg');
    }, function () {
        $(this).removeClass('cur_bg');
    });
    $('.fast_send').click(function () {
        //调至首页
        $('.send_write textarea').focus();
        $('.backToTop').click();
    });

    /**
     * 头部右侧下拉选项
     */
    $('.selector').hover(function () {
        var objClass = $('i',this).attr('class');
        //console.log(objClass);
        $('i', this).removeClass(objClass).addClass(objClass + '-cur');
        $(this).css({  //改变背景色
            'width': '36px',
            'backgroundColor': '#FFFFFF',
            'borderLeft': '1px solid #CCCCCC',
            'borderRight': '1px solid #CCCCCC'
        }).find('ul').show();
    }, function () {
        var objClass = $('i', this).attr('class');
        $('i', this).removeClass(objClass).addClass(objClass.replace('-cur', ''));
        $(this).css({  //还原背景
            'width': '38px',
            'background': 'none',
            'border': 'none'
        }).find('ul').hide();
    });
    $('.selector li').hover(function () {  //下拉项添加效果
        $(this).css('background', '#DCDCDC');
    }, function () {
        $(this).css('background', 'none');
    });

    /**
     * 用户模板设置
     */
    $('.edit_tpl').toggle(function(){
        var left=($(window).width()-$('#model').width())/2;
        var top=$(document).scrollTop()+($(window).height()-$('#model').height())/2;
        var obj = $('#model').appendTo('body').show().css({
            'left':left,
            'top':top,
        });
        createBg('edit_tpl_bg');
        drag(obj,obj.find('.model_head'));
    },function(){
        $('#model').hide();
    });

    //选中模板并添加选中样式
    $('#model ul li').click(function(){
        $(this).addClass('theme-cur').siblings().removeClass('theme-cur');
    });

    //异步提交选中模板数据
    $('span.model_save').click(function(){
        //获取修改的模板数据
        var tpl=$('.theme-cur').attr('theme');
        if(tpl==null){
            alert('请选择一套模板!');
        }else {
            $.post(editTpl, {tpl: tpl}, function (data) {
                console.log(data);
                if (!data) {
                    alert('设置失败，请重试！');
                } else {
                    document.location.reload();
                }
            }, 'json');
        }
    });
    //取消设置模板
    $('span.model_cancel').click(function(){
        $('#model').hide();
        $('#edit_tpl_bg').remove();
    });
    /**
     * 头部搜索框
     */
        //移入时改变背景
    $('#sech_text').hover(function () {
        $(this).css('backgroundPosition', '-237px -5px');
        $('#sech_sub').css('backgroundPosition', '-443px -5px');
    }, function () {
        if ($(this).val() == '搜索微博、找人') {
            $(this).css('backgroundPosition', '0 -5px');
            $('#sech_sub').css('backgroundPosition', '-206px -5px');
        }
        //获得焦点时清空默认文字
    }).focus(function () {
        if ($(this).val() == '搜索微博、找人') {
            $(this).val('');
        }
        //失去焦点时
    }).blur(function () {
        //添加默认文字
        if ($(this).val() == '') {
            $(this).val('搜索微博、找人')
        }
        //恢复原背景
        $(this).css('backgroundPosition', '0 -5px');
        $('#sech_sub').css('backgroundPosition', '-206px -5px');
    });
    $('#sech_sub').hover(function () {
        $(this).css('backgroundPosition', '-443px -5px');
        $('#sech_text').css('backgroundPosition', '-237px -5px');
    }, function () {
        $(this).css('backgroundPosition', '-206px -5px');
        $('#sech_text').css('backgroundPosition', '0 -5px');
    });

    /**
     * 搜索框
     */
        //移入时改变背景
    $('#sech-cons').focus(function () {
        if ($(this).val() == '搜索微博、找人') {
            $(this).val('');
        }
        ;
        //失去焦点时
    }).blur(function () {
        //添加默认文字
        if ($(this).val() == '') {
            $(this).val('搜索微博、找人')
        }
        ;
    });

    ////搜索切换
    //$('.search-type').click(function(){
    //    $('.cur').removeClass('cur');
    //    $(this).addClass('cur');
    //    $('form[name=search]').attr('action',$(this).attr('url'));
    //});
    /**
     * 返回顶部功能
     */

        //创建好友分组
    $('#create_group').click(function(){
        //让添加分组div显示时居中显示
        var groupLeft=($(window).width()-$('#add-group').width())/2;
        var groupTop=$(document).scrollTop()+($(window).height()-$('#add-group').height())/2;
        //console.log(groupLeft);
        //console.log(groupTop);
        var gpObj=$('#add-group').show().css({
            'left': groupLeft,
            'top' : groupTop,
        });
        createBg('group-bg');
        drag(gpObj,gpObj.find('.group_head'));
    });
    //异步创建分组
    $('.add-group-sub').click(function(){
        var groupName=$('#gp-name').val();
        if(groupName!=''){
            $.post(addGroup,{name:groupName},function(data){
                //data为服务器返回的信息
                //alert(data);
                if(data.status) {
                    showTips(data.msg);//showTips()创建分组成功效果}
                    $('#add-group').hide();
                    $('#group-bg').remove();
                }else{
                    alert(data.msg);
                }
            },'json');
        }
    })

    //关闭添加分组框
    $('.group-cencle').click(function(){
        $('#add-group').hide();
        $('#group-bg').remove();
    });

    /**
     * 好友关注
     */
    $('.add-fl').click(function(){
        var width=($(window).width()-$('#follow').width())/2;
        var height=$(document).scrollTop()+($(window).height()-$('#follow').height())/2;
        var flObj=$('#follow').appendTo('body').css({
            'top' : height,
            'left' : width,
        }).show();
        createBg('follow-bg');
        drag(flObj,flObj.find('.follow_head'));
        $('input[name=follow]').val($(this).attr('uid'));
    });
    //异步关注好友，并添加到分组中
    $('.add-follow-sub').click(function(){
        //console.log('111');
        //这里要传过去两个数据，分组gid 和要关注的用户uid
        var gid=$('select[name=gid]').val();
        var uid=$('input[name=follow]').val();
        //console.log(gid);
        //console.log(uid);
        $.post(addFollow,{'gid':gid,'follow':uid},function(data){
            if(data.status){
                var element='<dt>√&nbsp;已关注</dt> <dd class="del-follow" uid="{$v.uid}" type="1">移除</dd>';
                $('.add-fl[uid='+uid+']').removeClass('add-fl').parent().html(element);
                $('#follow').hide();
                $('#follow-bg').remove();
            }else{
                alert(data.msg);
            }
        },'json');
    });
    //关闭关注框
    $('.follow-cencle').click(function(){
        $('#follow').hide();
        $('#follow-bg').remove();
    })
    //移除关注与粉丝
    $('.del-follow').click(function(){
        //移除是需要传递移除用户的id，
        var data={//json对象
            uid:$(this).attr('uid'),
            type:$(this).attr('type'),
        };
        //alert(data['uid']);
        var isDel=confirm('确认移除?');
        var obj=$(this).parents('li');
        if(isDel){
            $.post(delFollow,data,function(data){
                if(data){
                    obj.slideUp('slow',function(){//slideup通过高度变化（向上减小）来动态地隐藏所有匹配的元素，在隐藏完成后可选地触发一个回调函数。
                        obj.remove();
                    })
                }else{
                    alert('移除失败，请重试....');
                }
            },'json');
        }
    });



    /**
     * 相片上传框处理
     */
    $(".sz-photo").click(function(){
        //让添加分组div显示时居中显示
        var photoLeft=($(window).width()-$('#c-photo').width())/2;
        var photoTop=$(document).scrollTop()+($(window).height()-$('#c-photo').height())/2;
        //console.log(groupLeft);
        //console.log(groupTop);
        var photoObj=$('#c-photo').show().css({
            'left': photoLeft,
            'top' : photoTop,
        });
        createBg('c-photo-bg');
        drag(photoObj,photoObj.find('.dialog_head'));
        //从某个相册中直接进入 上传照片 也就是photo模板中
        //上传框需获取相册信息
        var bitch=$('#c-photo').find('.pitch p');
        var img=$(this).parents('.photo_tool').prev().prev().find('img').attr('src');
        var name=$(this).parents('.photo_tool').prev().find('.album-name a').html();
        var aid=$(this).parents('.photo_head').attr('aid');
        var str="";
        //如果是未定义的，那么就说明是从相册模板中的上传照片按钮中进来的
        if(img && name && aid) {
            str += '<img src="' + img + '" width="40" height="30" /><span aid="' + aid + '">' + name + '</span><span class="icon"></span>';
            bitch.html(str);
        }

    });
    //取消 关闭相片上传框
    $('.close').click(function(){
        var obj=$(this).parents('#c-photo');
        obj.hide();
        $('#c-photo-bg').remove();
    });
    /**
     * 选择需要导入相片的相册
     */
    $("div.pitch").click(function() {
        var obj1 = $(this).next();
        var P = $(this).find('p');
        if (obj1.css('display') === 'none') {
            $(this).next().show();
            P.css({background: '#51ADFF'});
        } else {
            obj1.hide();
            P.css({background: ''});
        }
    });
    $("div.choice ul li").live('click',function(){
        var str=$(this).html();
        //console.log(str);
        str = str + '<span class="icon"></span>';
        $(this).parents('.choice').prev().find('p').html(str).css({background:''});
        $(this).parents('.choice').hide();
    })
    // 相片上传框中 异步创建相册
    $('div.create_album_1').live('click',function(){
        //alert(111);
        //让添加分组div显示时居中显示
        var photoLeft=($(window).width()-$('#c-album-2').width())/2;
        var photoTop=$(document).scrollTop()+($(window).height()-$('#c-album-2').height())/2;
        //console.log(groupLeft);
        //console.log(groupTop);
        var photoObj=$('#c-album-2').show().css({
            'left': photoLeft,
            'top' : photoTop,
        });
        createBg('ablum-bg');
        drag(photoObj,photoObj.find('.album-head'));
    });

    $('.add-album-2').click(function(){
        var album=$(this).parents('#c-album-2');
        var obj=album.find("input[name='name']");
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
        var P=$('#c-photo').find('.pitch p');
        var choice=$('#c-photo').find('div.choice');
        //异步提交
        $.post(addAlbum,{name:pname,depict:pdepict},function(data){
            if(data.status) {
                //创建成功后让其选中要上传的相册
                var str='';
                str += '<p>';
                str += '<img src="'+PUBLIC+'/cover.png"/><span aid="'+data.aid+'">'+pname+'</span><span class="icon"></span></p>';
                //console.log(str);
                P.html(str);
                P.css({background:'',});
                choice.hide();
                album.hide();
                $('#ablum-bg').remove();
            }else{alert(data.msg)}
        },'json');
    });

    //关闭添加相册框
    $('.ablum-cencle').click(function(){
        var obj=$(this).parents('#c-album');
        obj.find("input[name='name']").val('');
        obj.find('textarea').val('');
        $('#c-album').hide();
        $('#ablum-bg').remove();
    });

    //$('.sz-photo-button').click(function(){
    //   $(this).hide();
    //    $()
    //});

    //上传相片过程处理
    //整个过程就是将两个input file对象进行整合到一个数组中， 而存放file对象的必须是全局变量

    var fileList1=[];
    var fileList2=[];
    var delParent;
    var defaults = {
        fileType         : ["jpg","png","bmp","jpeg"],   // 上传文件的类型
        fileSize         : 1024 * 1024 * 10                  // 上传文件的大小 10M
    };
    /*点击上传按钮*/
    $(".file1").change(function(){
        var szbtn=$(this).parents('.sz-photo-button');
        szbtn.hide();
        szbtn.prev().show();
        szbtn.next().show();
        var idFile = $(this).attr("id");
        //alert(idFile);
        var file = document.getElementById(idFile);
        var imgContainer = szbtn.prev().find('.z_photo'); //存放图片的父亲元素
        //fileList.push();
        //console.log(fileList);
        fileList1=list(file.files);
        console.log(fileList1);
        ////遍历得到的图片文件
        var numUp = imgContainer.find(".up-section").length;
        var totalNum = numUp + fileList1.length;  //总的数量
        if(fileList1.length > 5 || totalNum > 5 ){
            alert("上传图片数目不可以超过5个，请重新选择");  //一次选择上传超过5个 或者是已经上传和这次上传的到的总数也不可以超过5个
        }
        else if(numUp < 5){
            pview(imgContainer,fileList1);
        setTimeout(function(){
            $(".up-section").removeClass("loading");
            $(".up-img").removeClass("up-opcity");
        },450);
        numUp = imgContainer.find(".up-section").length;
        if(numUp >= 5){
            $(this).parent().hide();
        }
        }
    });
    //继续添加上传图片(点击添加)
   $('.file2').change(function(){
       var imgContainer = $(this).parents('.z_photo'); //存放图片的父亲元素
       var file =document.getElementById('fileId2');
       fileList2=list(file.files);
       console.log(fileList2);

       ////遍历得到的图片文件
       var numUp = imgContainer.find(".up-section").length;
       var totalNum = numUp + fileList2.length;  //总的数量
       if(fileList2.length > 5 || totalNum > 5 ){
           alert("上传图片数目不可以超过5个，请重新选择");  //一次选择上传超过5个 或者是已经上传和这次上传的到的总数也不可以超过5个
       }
       else if(numUp < 5){
           pview(imgContainer,fileList2);
           setTimeout(function(){
               $(".up-section").removeClass("loading");
               $(".up-img").removeClass("up-opcity");
           },450);
           numUp = imgContainer.find(".up-section").length;
           if(numUp >= 5){
               $(this).parent().hide();
           }
       }
   });

    //图片预览
    function pview(imgContainer,fileList){
        var imgSrc=[];
        fileList = validateUp(fileList);
        for(var i = 0;i<fileList.length;i++) {
            var imgUrl = window.URL.createObjectURL(fileList[i]);
            imgSrc.push(imgUrl);
            //console.log(imgSrc);
            var $section = $("<section class='up-section fl loading'>");
            imgContainer.prepend($section);
            var $span = $("<span class='up-span'>");
            $span.appendTo($section);

            var $img0 = $("<img class='close-upimg'>").on("click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                $(".works-mask").show();
                delParent = $(this).parent();
            });
            var a7_png = PUBLIC + '/a7.png';
            $img0.attr("src", a7_png).appendTo($section);
            var $img = $("<img class='up-img up-opcity'>");
            $img.attr("src", imgSrc[i]);
            $img.appendTo($section);
            var $p = $("<p class='img-name-p'>");
            $p.html(fileList[i].name).appendTo($section);
            var $input = $("<input id='taglocation' name='taglocation' value='' type='hidden'>");
            $input.appendTo($section);
            var $input2 = $("<input id='tags' name='tags' value='' type='hidden'/>");
            $input2.appendTo($section);
        }
    }
    //将两次加入的文件对象合成存入一个数组中
    function list(flist){
        var arr=new Array();
        for(var k=0;k<flist.length;k++){
            //for(var n=0;n<flist[k].length;n++){
               arr.push(flist[k]);
            //}
        }
        return arr;
    }
    //开始异步上传
    $('.sz_send').click(function() {
        var fd = new FormData();
        //将两次添加的file对象重合到一个数组里
       var fileList=new Array();
        fileList=fileList1.concat(fileList2);

        //fileList=$.extend({},fileList1,fileList2);
        //var reader=new FileReader();
        //reader.readAsDataURL(fileList1[0])

        //console.log(fileList);
        for(var i=0;i<fileList.length;i++){
            fd.append('f'+i, fileList[i]);
        }

        //console.log(fd);
        $.ajax({
            type: 'POST',
            url: sendPhoto,
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {
                console.log(data);
            }
        });
    });
    //删除照片
    $(".z_photo").delegate(".close-upimg","click",function(){
        $(".works-mask").show();
        delParent = $(this).parent();
    });

    $(".wsdel-ok").click(function(){
        $(".works-mask").hide();
        //var numUp = delParent.siblings().length;
        //if(numUp < 6){
        //    delParent.parent().find(".z_file").show();
        //}
        //delParent.remove();
    });

    $(".wsdel-no").click(function(){
        $(".works-mask").hide();
    });

    function validateUp(files){
        var arrFiles = [];//替换的文件数组
        for(var i = 0, file; file = files[i]; i++){
            //获取文件上传的后缀名
            var newStr = file.name.split("").reverse().join("");
            if(newStr.split(".")[0] != null){
                var type = newStr.split(".")[0].split("").reverse().join("");
                //console.log(type+"===type===");
                if(jQuery.inArray(type, defaults.fileType) > -1){
                    // 类型符合，可以上传
                    if (file.size >= defaults.fileSize) {
                        alert(file.size);
                        alert('您这个"'+ file.name +'"文件大小过大');
                    } else {
                        // 在这里需要判断当前所有文件中
                        arrFiles.push(file);
                    }
                }else{
                    alert('您这个"'+ file.name +'"上传类型不符合');
                }
            }else{
                alert('您这个"'+ file.name +'"没有类型, 无法识别');
            }
        }
        return arrFiles;
    }

    //每次页面加载的时候运行
    //get_msg(getMsgUrl);
    //news({
    //	"type":1,
    //	"total":2,
    //});

});
//异步轮询函数
function get_msg(url){
    $.getJSON(url,function(data){//getJSON 使用 AJAX 请求来获得 JSON 数据，并输出结果： jQuery.getJSON(url,data,success(data,status,xhr)) url，请求的服务地址，data:请求服务器携带的数据，sunccess 请求成功执行的函数
        //console.log(data);
        if(data.status){//有消息推送
            news({
                'type':data.type,
                'total':data.total,
            })
        }
        setTimeout(function(){//设定每5S就自调用一次，查询是否有消息推送
            get_msg(url)
        },5000);
    });
}

/************************效果函数**********************/
/**
 * 推送的消息
 * @param  {[type]} json {total:新消息的条数,type:（1：评论，2：私信，3：@我）}
 * @return {[type]}      [description]
 */
var flags=true;
function news(json){
    switch (json.type) {
        case 1:
            $('#news ul li.news_comment').show().find('a').html(json.total + '条评论!');
            break;
        case 2:
            $('#news ul li.news_letter').show().find('a').html(json.total + '条私信!');
            break;
        case 3:
            $('#news ul li.news_atme').show().find('a').html(json.total + '条@我!');
            break;
    }
    var obj = $('#news');
    var icon=obj.find('i');
    //下拉效果
    obj.show().find('li').hover(function () {
        $(this).css({'background': '#DCDCDC'});
    }, function () {
        $(this).css({'background': 'none'});
    }).click(function () {
        //停止闪动样式
        clearInterval(newsGlint);
    });
    if (flags) {
        flags=false;
        var newsGlint = setInterval(function () {
            icon.toggleClass('icon-news');// toggleClass 如果存在（不存在）就删除（添加）一个类。
        },500);
    }
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
/**
 * 分组名成添加成功效果
 * @param tips
 * @param time
 * @param height
 */
function showTips(tips,time,height){
    var windowWidth=$(window).width();
    height=height?height:$(window).height();
    time=time?time:1;
    var tipsDiv='<div class="tipsClass">'+tips+'</div>';
    $('body').append(tipsDiv);
    $('div.tipsClass').css({
        'top':height/2+'px',
        'left':(windowWidth/2)-100+'px',
        'position':'absolute',
        'padding':'3px,5px',
        'background':'#670768',
        'font-size':'14px',
        'text-align':'center',
        'width':'300px',
        'heith':'40px',
        'line-height':'40px',
        'color':'#fff',
        'font-weight':'blod',
        'opacity':'0.8'
    }).show();
    setTimeout(function(){
        $('div.tipsClass').animate({
            top:height/2-50+'px'
        },"slow").fadeOut();
    },time*1000);
}