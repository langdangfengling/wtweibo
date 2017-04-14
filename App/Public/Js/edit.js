/**
 * Created by Administrator on 2016/11/13 0013.
 */
$(function(){
    //修改资料选卡
    $('#sel-edit li').click(function(){
        //获取当前jQuery对象的索引
        var index=$(this).index();
        $(this).addClass('edit-cur').siblings().removeClass('edit-cur');
        $('.form').hide().eq(index).show();//先所有的form隐藏，点击谁谁出现
    });

//城市联动
    var province='';
    $.each(city,function(k,v){
        //显示省份
        province += '<option value="'+ v.name+'" index="'+k+'">'+ v.name+'</option>';
    });
    //显示城市，郊区,当省份变了相应的城区也要出现在下拉表中
    $('#province').append(province).change(function(){
        //有两种情况，一种未选省份，一种选择了
        var option="";
        if($('#province').val()==""){
            option +='<option value="">请选择</option>';
        }else{
            var index=$(':selected',this).attr('index');
            var data=city[index].child; //json对象
            for(var i=0;i<data.length;i++){
                option += '<option value="'+ data[i]+'" index="'+i+'">'+data[i]+'</option>';
            }
            $("select[name='city']").html(option);
        }
    });
    //地址默认选项
    var value=address.split(' ');
    $("#province").val(value[0]);
    //alert(value);
    $.each(city,function(k,v){
        if(v.name==value[0]){
            var str="";
            for(var i in v.child){
                str +='<option value="'+ v.child[i]+'"';
                if(v.child[i]==value[1]){
                    str += 'selected="selected"';
                }
                str += '>'+v.child[i]+'</option>';
            }
            $("select[name='city']").html(str);
        }
    });


    //星座默认选项,需要该用户数据库中保存的什么星座
    $("select[name='night']").val(constellation);

    //图片上传，使用uploadify插件
    $('#face').uploadify({
        swf:PUBLIC+"uploadify.swf",//载入uploadify插件核心文件
        uploader:uploadUrl,//php处理脚本地址
        width:120,//上传按钮宽度
        height:30,//上传按钮高度
        buttonImage:PUBLIC+'browse-btn.png',//上传按钮背景图片
        fileTypeDesc:'Image File',//选择文件提示文字
        fileTypeExts:'*.jpg;*.jpeg;*.gif;*.png',//允许选择文件的类型
        formData:{'session_id':sid},//请求php脚本时携带的数据
        //上传成功后的回调函数
        onUploadSuccess:function(file,data,response){
            //console.log(data);
            //将服务器传过来的json字符串转换成json对象
            eval("var object = "+data);
            //console.log(object);
            if(object.status==1) {
                var thumb=object.path.thumb;
                var thumb230 = object.path.thumb230;
                var thumb80 = object.path.thumb80;
                var thumb60 = object.path.thumb60;
                $('input[name=thumb]').val(thumb);
                $('input[name=face230]').val(thumb230);
                $('input[name=face80]').val(thumb80);
                $('input[name=face60]').val(thumb60);
            }else{alert(object.msg)}
        }
    });

    //使用volidate 插件验证用户密码修改表单
    //自定义验证规则
    jQuery.validator.addMethod('user',function(value,element){
        var tel=/^[a-zA-Z][\w]{4,16}$/;
        return this.optional(element) || tel.test(value);
    },"请使用字母开头，4-16位字母，数字，下划线'_'");
    $('form[name=editPwd]').validate({
        errorElement:'span',
        success:function(label){
            label.addClass('success');
        },
        rules:{
            old:{
                required:true,
            },
            new:{
                required:true,
                user:true,
            },
            newed:{
                required:true,
                equalTo:'#new',
            }
        },
        messages:{
            old:{
                required:'密码不能为空',
            },
            new:{
                required:'请填写您的新密码',
            },
            newed:{
                required:'请再次确认您的密码',
                equalTo:'两次填写的密码不一致',
            }
        }
    })
})