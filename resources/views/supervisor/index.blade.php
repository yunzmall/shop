@extends('layouts.base')
<style>
.panel{
    margin-bottom:10px!important;
    padding-left: 20px;
    border-radius: 10px;
}   
.panel .active a {
	background-color: #29ba9c!important;
	border-radius: 18px!important;
    color:#fff;
}
.panel a{
    border:none!important;
    background-color:#fff!important;
}
    .content{
    background: #eff3f6;
    padding: 10px!important;
}
    .text {
        font-size: 14px;
    }

    .host {
        font-size: 20px;
    }

    .item {
        margin-bottom: 18px;
    }

    .process {
        margin-left: 20px;
    }

    .item>span{
        display: inline-block;
    }
    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }
    .clearfix:after {
        clear: both
    }


    .box-card {
        width: 100%;
        margin-top:20px;
        float:left;
    }

    .box-log {
        width: 48%;
        margin-top:20px;
        float:right;
    }
</style>
@section('content')
    <div class="w1200 m0a">
        <div class="rightlist">

            @include('layouts.newTabs')
            <div id="app">

                <el-card class="box-card">
                    <div slot="header" class="clearfix">
                        <span style="float: right;">
                <el-button size="small" v-if="stopAllState" disabled type="info"><i style="" class="el-icon-loading"></i>停止进程中...</el-button>
                <el-button size="small" v-else @click="stopAll" type="info">停止所有进程<i style="" class="el-icon-caret-right el-icon--right"></i></el-button>
                <el-button  size="small" v-if="startAllState" disabled type="success"><i style="" class="el-icon-loading"></i>启动进程中...</el-button>
                <el-button size="small" v-else  @click="startAll" type="success">启动所有进程<i style="" class="el-icon-caret-right el-icon--right"></i></el-button>
                <el-button size="small" v-if="restartState" disabled type="primary"><i style="" class="el-icon-loading"></i>重启中...</el-button>
                <el-button size="small" v-else @click="restart" type="primary">重启<i style="" class="el-icon-caret-right el-icon--right"></i></el-button>
                </span>
                    </div>
                    <div v-for="(process, hostname) in list" class="host item">
                        <el-span style="width:35%" v-if="state">ip地址：${hostname}</el-span>
                        <el-tag v-if="state[hostname].val.statecode == 1" type="success">运行中</el-tag>
                        <el-tag v-else-if="state[hostname].val.statecode == 2"><i style="" class="el-icon-loading"></i>加载中...</el-tag>
                        <el-tag v-else type="danger">未运行</el-tag>
                        <div v-for="(supervisor,key) in process.val" class="text item process">
                            <span style="width:35%">${ supervisor.name }</span>
                            <span style="width:22%">
                        <el-button round v-if="supervisor.statename == 'RUNNING'" type="text" size="small">已启动<i style="" class="el-icon-circle-check-outline el-icon--right"></i></el-button>
                        <el-button round v-else type="danger" size="small">已停止<i style="" class="el-icon-circle-close-outline el-icon--right"></i></el-button>
                    </span>
                            <span style="width:42%; float:right;text-align:right;">
                        <el-button @click="stop(supervisor,hostname)" v-if="supervisor.statename == 'RUNNING'" type="info" size="small">停止<i style="" class="el-icon-close el-icon--right"></i></el-button>
                        <el-button @click="start(supervisor,hostname)" v-else type="success" size="small">启动<i style="" class="el-icon-caret-right el-icon--right"></i></el-button>
                        <el-button v-if="!supervisor.cstate" @click="showlog(supervisor,key,hostname)" type="info" size="small">日志<i style="" class="el-icon-search el-icon--right"></i></el-button>
                        <el-button v-else disabled type="primary" size="small"><i style="" class="el-icon-loading"></i>加载中...</el-button>
                    </span>
                        </div>
                    </div>
                </el-card>

                <el-card class="box-log" v-if="log">
                    <div slot="header" class="clearfix">
                        <span>日志查看(${ currentProcess.name })</span>
                        <span style="float: right;">
                <el-button size="small" @click="clearLog" type="info">清除日志<i style="" class="el-icon-caret-right el-icon--right"></i></el-button>
                <el-button  size="small" @click="reloadLog" type="success">重载<i style="" class="el-icon-caret-right el-icon--right"></i></el-button>
                </span>
                    </div>
                    <div  class="text item" style="height:800px;overflow:auto">
                        ${ log[0] || '还没有日志哦' }
                    </div>
                </el-card>
            </div>
        </div>
    </div>
    @include('public.admin.mylink')
@endsection

@section('js')
    <script>
        new Vue({
            el: '#app',
            delimiters: ['${', '}'],
            data: {
                list:[],
                log:'',
                state:[],
                currentProcess:'',
                currentHostname:'',
                currentIndex:0,
                lodingState: false,
                stopAllState: false,
                startAllState: false,
                restartState: false,
                logIndex:'',
            },

            methods: {
                processlist () {
                    console.log('processlist');

                    var that = this;
                    let url = "{!! yzWebUrl("supervisord.supervisord.process") !!}";
                    //console.log(url);
                    axios.get(url)
                        .then(function (response) {
                            console.log('response1231234:', response.data);
                            that.list = response.data.process;
                            console.log(that.list);
                            that.state = response.data.state;
                            console.log('that.state:', that.state);
                        })
                        .catch(function (error) {
                            console.log('error:', error);
                            //that.$Message.error('提交失败啦');
                        });
                },
                stopAll () {
                    console.log('stopAll');

                    var that = this;
                    this.$confirm('确定要重启所有进程?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        that.stopAllState = true;
                        let url = "{!! yzWebUrl("supervisord.supervisord.stopAll") !!}";
                        //console.log(url);
                        axios.get(url)
                            .then(function (response) {
                                console.log('response:', response.data);
                                    that.$message({
                                        message: '已停止所有进程',
                                        type: 'success'
                                    });
                                    that.stopAllState = false;
                                    that.processlist();
                                })
                                //that.$Message.success('提交成功啦');
                            .catch(function (error) {
                                console.log('error:', error);
                                //that.$Message.error('提交失败啦');
                            });

                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消'
                        });
                    });


                },

                startAll () {
                    console.log('startAll');

                    var that = this;
                    let url = "{!! yzWebUrl("supervisord.supervisord.startAll") !!}";
                    //console.log(url);
                    this.startAllState = true;
                    console.log('this.startAllState', this.startAllState);
                    axios.get(url)
                        .then(function (response) {
                            console.log('response:', response.data);
                                that.$message({
                                    message: '已启动所有进程',
                                    type: 'success'
                                });
                                that.startAllState = false;
                                console.log('this.startAllState', that.startAllState);

                                that.processlist();
                            //that.$Message.success('提交成功啦');

                        })
                        .catch(function (error) {
                            console.log('error:', error);
                            //that.$Message.error('提交失败啦');
                        });
                },

                restart () {
                    console.log('restart');

                    var that = this;
                    this.$confirm('确认重启?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        //todo
                        that.restartState = true;

                        let url = "{!! yzWebUrl("supervisord.supervisord.restart") !!}";
                        //console.log(url);
                        axios.get(url)
                            .then(function (response) {
                                console.log('restart response:', response.data);
                                    that.$message({
                                        message: '重启中,请稍后',
                                        type: 'info'
                                    });
                                    console.log('restart message:', '完毕');
                                    setTimeout(function(){
                                        that.processlist();
                                        that.$message({
                                            message: '重启完毕',
                                            type: 'success'
                                        });
                                        console.log('restart message:', 'list完毕');
                                    }, 5000);
                                    that.restartState = false;
                                //that.$Message.success('提交成功啦');

                            })
                            .catch(function (error) {
                                console.log('error:', error);
                                //that.$Message.error('提交失败啦');
                            });
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消重启'
                        });
                    });
                    return;

                },

                showlog (process, index=0 ,hostname = '') {
                    var that = this;
                    that.list[hostname].val[index].cstate = true;
                    let url = "{!! yzWebUrl("supervisord.supervisord.showlog") !!}"+"&hostname="+hostname+"&process="+process.group + ":" + process.name;
                    console.log(url);
                    axios.get(url)
                        .then(function (response) {
                            console.log('response:', response.data);
                            if (response.data.errno == 0) {
                                that.log = response.data.val;
                                that.currentProcess = process;
                                that.currentHostname = hostname;
                                that.currentIndex = index;
                                that.list[hostname].val[index].cstate = false;

                            } else {
                                that.list[hostname].val[index].cstate = false;
                                that.$message.error('错了哦,' + response.data.errstr);
                            }
                            //that.$Message.success('提交成功啦');

                        })
                        .catch(function (error) {
                            console.log('error:', error);
                            //that.$Message.error('提交失败啦');
                        });
                },

                clearLog () {
                    var that = this;

                    //停止状态清除无效
                    if (this.currentProcess.state == 0) {
                        this.$message.error('进程必须启动状态才能清除哦');
                        return false;
                    }
                    let url = "{!! yzWebUrl("supervisord.supervisord.clearlog") !!}"+"&hostname="+this.currentHostname+"&process="+this.currentProcess.group + ":" + this.currentProcess.name;
                    console.log(url);
                    axios.get(url)
                        .then(function (response) {
                            //console.log('response:', response.data);
                            if (response.data.errno == 0) {
                                that.$message({
                                    message: '日志已清除',
                                    type: 'success'
                                });
                                that.reloadLog();
                            } else {
                                that.$message.error('错了哦,' + response.data.errstr);
                            }
                            //that.$Message.success('提交成功啦');

                        })
                        .catch(function (error) {
                            console.log('error:', error);
                            //that.$Message.error('提交失败啦');
                        });
                },

                reloadLog () {
                    this.showlog(this.currentProcess,this.currentIndex,this.currentHostname);
                },

                stop (process,hostname) {
                    console.log('stop');

                    var that = this;
                    let url = "{!! yzWebUrl("supervisord.supervisord.stop") !!}"+"&hostname="+hostname+"&process="+process.group + ":" + process.name;
                    //console.log(url);
                    axios.get(url)
                        .then(function (response) {
                            console.log('response:', response.data);
                            if (response.data.val) {
                                that.$message({
                                    message: '进程已停止',
                                    type: 'success'
                                });
                                that.processlist();
                            } else {
                                that.$message.error('错了哦,' + response.data.errstr);
                            }
                            //that.$Message.success('提交成功啦');

                        })
                        .catch(function (error) {
                            console.log('error:', error);
                            //that.$Message.error('提交失败啦');
                        });
                },

                start (process,hostname) {
                    console.log('start');

                    var that = this;
                    let url = "{!! yzWebUrl("supervisord.supervisord.start") !!}"+"&hostname="+hostname+"&process="+process.group + ":" + process.name;
                    //console.log(url);
                    axios.get(url)
                        .then(function (response) {
                            //console.log('response:', response.data);
                            if (response.data.val) {
                                that.$message({
                                    message: '进程已启动',
                                    type: 'success'
                                });
                                that.processlist();
                            } else {
                                that.$message.error('错了哦,' + response.data.errstr);
                            }
                            //that.$Message.success('提交成功啦');

                        })
                        .catch(function (error) {
                            console.log('error:', error);
                            //that.$Message.error('提交失败啦');
                        });
                }
            },
            mounted () {
                console.log('mounted');

                this.processlist();
            }
        })
    </script>
@endsection('js')
