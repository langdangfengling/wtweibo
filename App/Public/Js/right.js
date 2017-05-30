/**
 * Created by wt on 2017/5/28 0028.
 */
$(function() {
    //登录用户文章推荐，最新文章，随机文章
//   默认显示最火的文章
    function hotArticle() {
        var ulParent = $('.bd').find('ul');
        var uid=$('.hd').find("li[theme='hot']").attr('uid');
        $.post(getArticle, {str: 'hot',uid:uid}, function (data) {
            //console.log(data);
            if (data != 'false') {
                ulParent.html(data);
            }
        }, 'html');
    }

    hotArticle();
    $('.lanmubox li').bind({
        click: function () {
            $(this).addClass('on').siblings().removeClass('on');
            var str = $(this).attr('theme');
            var uid=$(this).attr('uid');
            //alert(str);
            var ulParent = $(this).parents('.hd').next().find('ul');
            $.post(getArticle, {str: str,uid:uid}, function (data) {
                //console.log(data);
                if (data != 'false') {
                    ulParent.html(data);
                }
            }, 'html');
        }
    });
});