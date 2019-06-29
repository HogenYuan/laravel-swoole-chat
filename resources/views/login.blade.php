<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hogen聊天室--登录</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/css/util.css">
    <link rel="stylesheet" type="text/css" href="/css/main.css">
    <script src="/vue.js"></script>
     <!-- 引入样式 -->
    <link rel="stylesheet" href="/css/element.css">
    <!-- 引入组件库 -->
    <script src="/js/element.js"></script>
</head>

<body>
 <div id="login_app" v-cloak >
<div class="dowebok limiter" >
    <div class="container-login100" style="background-image: url('images/img-01.jpg');" >
        <div class="wrap-login100 p-t-190 p-b-30" style="margin-top:-150px">
            <form class="login100-form validate-form">
                <div class="login100-form-avatar">
                    <img src="images/avatar-01.jpg" alt="AVATAR">
                </div>

                <span class="login100-form-title p-t-20 p-b-45">Hogen聊天室</span>

                <div class="wrap-input100 validate-input m-b-10" data-validate="请输入用户名">
                    <input class="input100" type="text" name="nickname" v-model="nickname" placeholder="昵称" autocomplete="off">
                     <span class="focus-input100"></span>
                       <span class="symbol-input100">
                        <i class="fa fa-user"></i>
                    </span>
                </div>
                 <el-radio-group v-model="sid" style="margin-bottom:10px;font-size:20px;">
                        <el-radio :label="1">广东服务器</el-radio>
                        <el-radio :label="2">上海服务器</el-radio>
                        <el-radio :label="3">北京服务器</el-radio>
                    </el-radio-group>
                   

                {{-- <div class="wrap-input100 validate-input m-b-10" data-validate="请输入密码">
                    <input class="input100" type="password" name="pass" placeholder="密码">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="fa fa-lock"></i>
                    </span>
                </div> --}}
                <div class="container-login1-form-btn  ">
                    <a class="login1-form-btn" @click="enter()">进入</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script src="/vendor/jquery/jquery-1.12.4.min.js"></script>
<script src="/js/main.js"></script>
<script>
   var login_app = new Vue({
        el: "#login_app",
        data: {
            nickname:'',
            sid:1
        },
        methods:{
            enter(){
                if(this.nickname == '' || this.nickname == '系统'){
                    alert('请输入正确的昵称');
                }else{
                    var url = "/"+this.sid+"/"+this.nickname;
                    console.log(url);
                    location.href = url;
                }
            },
        }
    })
</script>
</body>
</html>