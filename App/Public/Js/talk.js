/**
 * Created by wt on 2017/4/2 0002.
 */
$(function(){
    /**
     *发布框输入效果
     */
    $('.send_write textarea').focus(function () {
        //获取焦点时改变边框背景
        $('.ta_right').css('backgroundPosition', '0 -50px');
        //转入文字时
        $(this).css('borderColor', '#FFB941').keyup(function () {
            var content = $(this).val();
            //调用check函数取得当前字数
            var lengths = check(content);
            if (lengths[0] > 0) {//当前有输入内容时改变发布按钮背景
                $('.send_btn').css('backgroundPosition', '-133px -50px');
            } else {//内容为空时发布按钮背景归位
                $('.send_btn').css('backgroundPosition', '-50px -50px');
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
        //失去焦点时边框背景归位
    }).blur(function () {
        $(this).css('borderColor', '#CCCCCC');
        $('.ta_right').css('backgroundPosition', '0 -69px');
    });
    //内容提交时处理
    $('form[name=talk]').submit(function () {
        var cons = $('textarea', this);
        if (cons.val() == '') {//内容为空时闪烁输入框
            var timeOut = 0;
            var glint = setInterval(function () {
                if (timeOut % 2 == 0) {
                    cons.css('background','#249ff1');
                } else {
                    cons.css('background','#fff');
                }
                timeOut++;
                if (timeOut > 7) {
                    clearInterval(glint);
                    cons.focus();
                }
            }, 100);
            return false;
        }
    });
    //发布按钮hover样式
    $('input.send_btn').hover(function(){
        $(this).addClass('send_btn_hover').removeClass('send_btn');
    },function(){
        $(this).addClass('send_btn').removeClass('send_btn_hover');
    });
    //删除样式
    $('.say_box').mouseover(function(){$(this).find('.delsay').show();}).mouseout(function(){$(this).find('.delsay').hide()});
    $('.delsay').click(function(){var del=confirm('确认要删除该条说说吗?');if(!del){return false;}});
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
