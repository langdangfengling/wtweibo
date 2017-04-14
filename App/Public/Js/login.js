$(function () {

    //jQuery Validate 表单验证

    /**
     * 添加验证方法
     * 以字母开头，5-17 字母、数字、下划线"_"
     */
    jQuery.validator.addMethod("user", function(value, element) {
        var tel = /^[a-zA-Z][\w]{4,16}$/;
        return this.optional(element) || (tel.test(value));
    }, " ");

    $('form[name=login]').validate({
        errorElement : 'span',
        success : function (label) {
            label.addClass('success');
        },
        rules : {
            account : {
                required : true,
                user : true
            },
            pwd : {
                required : true,
                user : true
            }
        },
        messages : {
            account : {
                required : ' '
            },
            pwd : {
                required : ' '
            }
        }
    });
    //异步请求更换用户图像
    $('input.username').focus(function(){
        //获取内容
        var content=$(this).val();
        //console.log(content);
        var picObj=$(this).parent().parent().prev();
        $.post(getPic,{account:content},function(data){
            //console.log(data);
            if(data.status){
                 picObj.find('img').attr('src',ROOT+data.pic);
            }else{
                picObj.find('img').attr('src',PUBLIC+'/Images/Login/avtar.png');
            }
        },'json');
    }).change(function(){
        //获取内容
        var content=$(this).val();
        //console.log(content);
        var picObj=$(this).parent().parent().prev();
        $.post(getPic,{account:content},function(data){
            //console.log(data);
            if(data.status){
                picObj.find('img').attr('src',ROOT+data.pic);
            }else{
                picObj.find('img').attr('src',PUBLIC+'/Images/Login/avtar.png');
        }
        },'json');
    })

});
