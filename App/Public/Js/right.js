/**
 * Created by wt on 2017/5/28 0028.
 */
$(function() {
    //文章推荐，最新文章，随机文章
//   默认显示最火的文章
    function hotArticle() {
        var ulParent = $('.bd').find('ul');
        $.post(getArticle, {str: 'hot'}, function (data) {
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
            //alert(str);
            var ulParent = $(this).parents('.hd').next().find('ul');
            $.post(getArticle, {str: str}, function (data) {
                //console.log(data);
                if (data != 'false') {
                    ulParent.html(data);
                }
            }, 'html');
        }
    });
});