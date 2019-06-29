<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Hogen聊天室</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link type="text/css" rel="stylesheet" href="/style.css">
		<meta name="Description" content="" />
		<meta name="Keywords" content="" />
		<meta name="Author" content="Longbill" />
        <script src="/jquery.min.js"></script>
        <script src="/vue.js"></script>
       <!-- 引入样式 -->
        <link rel="stylesheet" href="/css/element.css">
        <!-- 引入组件库 -->
        <script src="/js/element.js"></script>
    </head>
    <body scroll="no" style="background-color:#808080">
    <div id="chat_app" v-cloak >
        <div id="header">
        	<div id="header_content">
        		<table cellpadding="0" cellspacing="0" border="0">
        			<tr>
        				<td><span style="margin-left:20px;"><h1>@{{sname}}(id:@{{sid}})</h1></span><br><div>nickname:@{{nickname}}</div></td>
        				<td> <div>
                            <el-radio-group v-model="select_server" size="small" @change="server_change()">
                                <el-radio-button label="1">广东服务器</el-radio-button>
                                <el-radio-button label="2">上海服务器</el-radio-button>
                                <el-radio-button label="3">北京服务器</el-radio-button>
                            </el-radio-group>
                        </div></td>
    	       			<td style="text-align:right" valign="middle">
    	       				在线人数：@{{count}}<b id="online_num"></b>
    	       			</td>
    	       		</tr>
    	       	</table>
    	    </div>
        </div> 
        <div id="main">
        	<div id="chat">
        		<div id="chat_window" style="height: 500px;width:80%;float:left;">
                    <div v-for="(talk,index) in talk_window">
                          <span v-if="talk.nickname == '系统'" style="color:#969696;size:6px">@{{talk.nickname}} : @{{talk.content}}</span>
                          <span v-else style="size:10px">@{{talk.nickname}}(fd:@{{talk.fd}}) : @{{talk.content}}</span>
                          <br>
                    </div>
                </div>
                <div id="chat_window" style="height: 500px;width:10%;float:left;">
                    <h1>用户</h1>
                    <div v-for="(nickname,fd) in user_window" >
                        <span>@{{nickname}}(fd:@{{fd}})</span><br>
                    </div>
                </div>
        		<div id="chat_bottom">
        			<div id="disconnectwrapper" @click="send()">发送</div>
	        			<textarea  v-model="content" style="margin: 0px 0px 0px 10px; height: 68px; width: 1430px;"></textarea>
        			<div id="sendwrapper" style="margin-right:70px;width:210px;"@click="close()">断开</div> 
        		</div>
        	</div>
        </div>
    </div>
    </body>
</html>

<script>
   var ws = new WebSocket("ws://{{$host}}:9501");
   var userdataApp = new Vue({
        el: "#chat_app",
        data: {
            content:'',
            nickname:'{{request('nickname')}}',
            sid:'{{request('sid')}}',
            sname:"广东服务器",
            talk_window:{
            },
            server:{
                1:"广东服务器",
                2:"北京服务器",
                3:"上海服务器"
            },
            select_server:'{{request('sid')}}',
            count:0,
            m_count:0,
        },
        created:function(){
            if(this.nickname == "系统"){
                alert('请不要取非法名字');
                location.href = "/";
            }else{
                this.websocket();
            }
        },
        methods:{
            websocket(){
                var self = this;
                ws.onopen = function(evt) {
                    //获取用户列表
                    var open = {
                        'nickname':self.nickname,
                        'type':'open',
                        'sid':self.sid
                    }
                    open = JSON.stringify(open)
                    ws.send(open) 
                    //用户接入
                    var connect = {
                        'nickname':self.nickname,
                        'type':'connect',
                        'sid':self.sid
                    }
                    connect = JSON.stringify(connect)
                    ws.send(connect) 
                };
                ws.onerror = function(evt, e) {
                    console.log('Error occured: ' + evt.data);
                };
                ws.onmessage = function(evt) {
                    var data = JSON.parse(evt.data);
                    if(data.type == "talk"){
                        self.set_window(data.nickname,data.content,data.fd);
                    }else if(data.type == 'connect'){
                        self.set_window('系统',data.nickname+" 进入了聊天室");
                        Vue.set(self.user_window,[data.fd],data.nickname);
                        self.count = data.count;
                    }else if(data.type == 'close'){
                        self.set_window('系统',data.nickname+" 离开聊天室");
                        Vue.delete(self.user_window,[data.fd]);
                        self.count = data.count;
                    }else if(data.type == 'open'){
                        self.user_window = JSON.parse(data.users);
                    }
                };
            },
            set_window(nickname,content,$fd=0){
                Vue.set(this.talk_window,this.m_count,{
                    'fd':$fd,
                    'nickname':nickname,
                    'content':content
                })
                this.m_count++;
            },
            close(){
                ws.close();
                this.set_window('系统',"你已经离开聊天室");
                this.m_count--;
                this.count--;
            },
            send(){
                if(this.content == ''){
                    alert('请勿发送空消息!');
                }else{
                    var talk = {
                        'content':this.content,
                        'nickname':this.nickname,
                        'type':'talk',
                        'sid':this.sid
                    }
                    ws.send(JSON.stringify(talk));
                }
            },
            server_change(){
                ws.close();
                this.set_window('系统',"你已经离开聊天室");
                this.m_count--;
                location.href = "/"+this.select_server+"/"+this.nickname;
            },
        }
    })
</script>