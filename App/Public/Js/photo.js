/**
 * Created by wt on 2017/4/9 0009.
 * 相片js
 */
$(function(){
    //alert(111);
   //相片删除处理
    $(".photo_content ul li").hover(function(){
        $(this).find('.delp').show();
        $(this).find('.photo_name').css({borderColor:'#F7FF1A'});
    },function(){
        $(this).find('delp').hide();
        $(this).find('.delp').hide();
    });

    $('.delp').click(function(event){
        event.stopPropagation();//阻止冒泡行为
        event.preventDefault();//阻止浏览器默认行为
        var delli=$(this).parent();
        var pid=$(this).attr('pid');
        var isdel=confirm('确定删除该相片吗？');
        if(isdel) {
            $.post(delPhoto, {pid: pid}, function (data) {
                if (data) {
                    delli.fadeOut('slow', function () {
                        delli.remove();
                    });
                }
            }, 'json');
        }
    });

//放大查看相片

    var index=''; //该li元素下标
    var liObj=$('.photo_content ul li');
    var length=liObj.length;
$(".photo_content ul li").click(function(){
    var imgObj=$(this).find('img');
    index=$(this).index();
    //alert(index);
    var src1=imgObj.attr('src');
    var alt1=imgObj.attr('alt');
    //背景层
   $('.tips').show();
    $('.bgimg').show();
    //var src2=src1.substr(-17,17);
    var src2=src1.replace('thumb_170','');
    //alert(src2);
    $('.fimg').attr('src',src2)
    $('.fimg').attr('alt',alt1)
});
    //点击除图片外的地方隐藏背景层
 $('.tips').click(function(){
     $(this).hide();
     $('.bgimg').hide();
 });
    //点击右键
    $("#btn-r").click(function(){
        index++;
        //alert(index);
        //console.log(liObj);
        //console.log(liObj.eq(2));
        var nsrc=liObj.eq(index).find('img').attr('src');
        
        //alert(nsrc);
        nsrc=nsrc.replace('thumb_170','');
        if(index==length-1){
            //alert(111);
            index=-1;}
        $(".fimg").attr("src",nsrc);
    });
  //点击左键
    $('#btn-l').click(function(){
       index--;
        var psrc=liObj.eq(index).find('img').attr('src');
        //alert(nsrc);
        psrc=psrc.replace('thumb_170','');
        if(index==0){
            index=length;}
        $(".fimg").attr("src",psrc);
    });

});
