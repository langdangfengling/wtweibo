/**
 * 注册页面Js文件
 * Created by wt on 2016/11/12 0012.
 */
//点击验证码图片进行更换
$(function() {
    var verifyUrl=$('#verify_img').attr('src');
    $('#verify_img').click(function () {
        //$(this).attr('src',"{:U('verify','"+Math.random()+"')}" );//不行
      $(this).attr('src',verifyUrl+'/'+Math.random());
    });

//jQuery Validate 表单验证
/**
 * 添加验证方法
 * 以字母开头，5-17字母、数字、下划线"_"
 */
jQuery.validator.addMethod('user',function(value,element){
    var tel=/^[a-zA-Z][\w]{4,16}$/;
    return this.optional(element) || (tel.test(value));
},'以字母开头，5-17字母、数字、下划线"_"')
    $('form[name=register]').validate({
        errorElement:'span',
        success:function(label){
            label.addClass('success');
        },
        rules:{
             account:{
                 required:true,
                 user:true,
                 remote:{//ajax异步请求
                     url:checkAccount,//请求地址
                     type:'post',//请求方式
                     dataType:'json',//服务器返回数据格式
                     data:{//请求的传递数据
                         account:function(){//json数据格式，这里account的值为匿名函数返回值
                             return $('#account').val();
                         }
                     }
                 }
             },
            pwd:{
                required:true,
                user:true,
            },
            pwded:{
                required:true,
                equalTo:"#pwd",
            },
            uname:{
                required:true,
                rangelength:[4,16],
                remote:{
                    url:checkUname,
                    type:'post',
                    dataType:'json',
                    data:{
                        uname:function(){
                            return $('#uname').val();
                        }
                    }
                }
            },
            verify:{
                required:true,
                remote:{
                    url:checkVerify,
                    type:'post',
                    dataType:'json',
                    data:{
                        verify:function(){
                            return $('#verify').val();
                        }
                    }
                }
            }
        },
        messages:{
            account:{
                required:'账号不能为空',
                remote:'该账户已被注册，请更换',
            },
            pwd:{
                required:'密码不能为空',
            },
            pwded:{
                required:'请再次输入您的密码',
                equalTo:'两次输入的密码不一致',
            },
            uname:{
                required:'请输入您的昵称',
                rangelength:'请输入4-16位字符',
                remote:'改昵称已被占用，请进行更换'
            },
            verify:{
                required:'请输入验证码',
                remote:'验证码错误，请重新输入',
            }
        }
    });
});