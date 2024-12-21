<template>
    <div class="lb-default-content">
        <lb-tips :title='tips'></lb-tips>
        <div class="card">
          <!-- 效果展示 -->
          <div class="card-show"></div>
          <!-- 内容设置 -->
          <div class='card-form'>
            <div class="title">编辑名片默认内容</div>
            <el-form :model="ruleForm"  :rules="rules" ref="ruleForm" label-position='right' label-width="120px" size="mini">
              <el-collapse v-model="activeNames" @change="handleChange">
                <el-collapse-item title="名片样式" name="1">
                  <div>与现实生活一致：与现实生活的流程、逻辑保持一致，遵循用户习惯的语言和概念；</div>
                  <div>在界面中一致：所有的元素和结构需保持一致，比如：设计样式、图标和文本、元素的位置等。</div>
                </el-collapse-item>
                <el-collapse-item title="名片信息" name="2">
                  <el-form-item label="员工姓名" prop="name">
                      <el-input v-model="ruleForm.name"></el-input>
                  </el-form-item>
                  <el-form-item label="头像" prop="avatar">
                      <el-upload
                        class="avatar-uploader"
                        action="https://jsonplaceholder.typicode.com/posts/"
                        :show-file-list="false"
                        :on-success="handleAvatarSuccess"
                        :before-upload="beforeAvatarUpload">
                        <img v-if="ruleForm.avatar" :src="ruleForm.avatar" class="avatar">
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload>
                  </el-form-item>
                  <el-form-item label="手机号" prop="tel">
                      <el-input v-model="ruleForm.tel"></el-input>
                  </el-form-item>
                  <el-form-item label="微信号" prop="weNum">
                      <el-input v-model="ruleForm.weNum"></el-input>
                  </el-form-item>
                  <el-form-item label="电话" prop="telephone">
                      <el-input v-model="ruleForm.telephone"></el-input>
                  </el-form-item>
                  <el-form-item label="400电话" prop="phone400">
                      <el-input v-model="ruleForm.phone400"></el-input>
                  </el-form-item>
                  <el-form-item label="邮箱" prop="email">
                      <el-input v-model="ruleForm.email"></el-input>
                  </el-form-item>
                </el-collapse-item>
                <el-collapse-item title="虚拟数据" name="3">
                  <el-form-item label="浏览人数" prop="fakeNum">
                    <el-input v-model="ruleForm.fakeNum"></el-input>
                  </el-form-item>
                  <div class='item-tips'>虚拟浏览人数</div>
                  <el-form-item label="浏览人数" prop="realNum">
                    <el-input v-model="ruleForm.realNum"></el-input>
                  </el-form-item>
                  <div class='item-tips'>虚拟靠谱人数</div>
                </el-collapse-item>
                <el-collapse-item title="个人简介" name="4">
                  <el-form-item label="个人简介" prop="profile">
                    <el-input type="textarea" :rows="2" placeholder="请输入个人简介" v-model="ruleForm.profile"></el-input>
                  </el-form-item>
                </el-collapse-item>
                <el-collapse-item title="语音介绍" name="5">
                  <el-form-item label="语音介绍" prop="audio">
                    <div class="audio-name">
                      <span>{{ruleForm.audioName}}</span>
                      <el-upload
                        class="upload-audio"
                        action="https://jsonplaceholder.typicode.com/posts/"
                        :show-file-list="false"
                        :on-success="handleAudioSuccess"
                        :before-upload="beforeAudioUpload">
                          <el-button size="small">选择媒体文件</el-button>
                      </el-upload>
                    </div>
                  </el-form-item>
                  <el-form-item label="语音时长(秒)" prop="sec">
                    <el-input :disabled="true" v-model="ruleForm.sec"></el-input>
                  </el-form-item>
                </el-collapse-item>
                <el-collapse-item title="我的视频" name="6">
                  <el-form-item label="视频" prop="sec">
                    <div class="audio-name">
                      <span>{{ruleForm.videoName}}</span>
                      <el-upload
                        class="upload-audio"
                        action="https://jsonplaceholder.typicode.com/posts/"
                        :show-file-list="false"
                        :on-success="handleAudioSuccess"
                        :before-upload="beforeAudioUpload">
                          <el-button size="small">选择媒体文件</el-button>
                      </el-upload>
                    </div>
                  </el-form-item>
                  <el-form-item label="视频封面图" prop="sec">
                    <el-upload
                      class="avatar-uploader"
                      action="https://jsonplaceholder.typicode.com/posts/"
                      :show-file-list="false"
                      :on-success="handleAvatarSuccess"
                      :before-upload="beforeAvatarUpload">
                      <img v-if="ruleForm.avatar" :src="ruleForm.avatar" class="avatar">
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                  </el-form-item>
                </el-collapse-item>
                <el-collapse-item title="我的图片" name="7">
                  <el-form-item label="详情图片" prop="sec">
                    <el-upload
                      class="avatar-uploader"
                      action="https://jsonplaceholder.typicode.com/posts/"
                      :show-file-list="false"
                      :on-success="handleAvatarSuccess"
                      :before-upload="beforeAvatarUpload">
                      <img v-if="ruleForm.avatar" :src="ruleForm.avatar" class="avatar">
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                  </el-form-item>
                  <el-form-item label="详情链接" prop="sec">
                    <el-upload
                      class="avatar-uploader"
                      action="https://jsonplaceholder.typicode.com/posts/"
                      :show-file-list="false"
                      :on-success="handleAvatarSuccess"
                      :before-upload="beforeAvatarUpload">
                      <img v-if="ruleForm.avatar" :src="ruleForm.avatar" class="avatar">
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                  </el-form-item>
                  <div class='item-tips'>请输入链接，如：网址，或者：appid</div>
                </el-collapse-item>
                <el-collapse-item title="VR全景设置" name="8">
                  <el-form-item label="VR展示标题" prop="sec">
                    <el-input v-model="ruleForm.cWeNum"></el-input>
                  </el-form-item>
                  <div class='item-tips'>名片详情页VR展示的版块的标题</div>
                  <el-form-item label="VR封面图" prop="sec">
                    <el-upload
                      class="avatar-uploader"
                      action="https://jsonplaceholder.typicode.com/posts/"
                      :show-file-list="false"
                      :on-success="handleAvatarSuccess"
                      :before-upload="beforeAvatarUpload">
                      <img v-if="ruleForm.avatar" :src="ruleForm.avatar" class="avatar">
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                  </el-form-item>
                  <el-form-item label="VR链接地址" prop="sec">
                    <el-input v-model="ruleForm.cWeNum"></el-input>
                  </el-form-item>
                  <div class='item-tips'>名片详情页VR展示的版块的链接</div>
                  <el-form-item label="VR链接地址" prop="sec">
                    <el-input v-model="ruleForm.cWeNum"></el-input>
                  </el-form-item>
                </el-collapse-item>
                <el-collapse-item title="背景音乐" name="9">
                  <el-form-item label="背景音乐" prop="sec">
                    <div class="audio-name">
                      <span>{{ruleForm.musicName}}</span>
                      <el-upload
                        class="upload-audio"
                        action="https://jsonplaceholder.typicode.com/posts/"
                        :show-file-list="false"
                        :on-success="handleAudioSuccess"
                        :before-upload="beforeAudioUpload">
                          <el-button size="small">选择媒体文件</el-button>
                      </el-upload>
                    </div>
                  </el-form-item>
                  <div class='item-tips'>进入员工名片后的背景音乐</div>
                  <el-form-item label="是否自动播放" prop="musicSwitch">
                    <el-radio-group v-model="ruleForm.musicSwitch">
                      <el-radio label="1">关闭</el-radio>
                      <el-radio label="2">自动播放</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </el-collapse-item>
                <el-collapse-item title="企业微信" name="10">
                  <el-form-item label="企业微信" prop="realNum">
                    <el-input v-model="ruleForm.cWeNum"></el-input>
                  </el-form-item>
                  <div class='item-tips'>员工企业微信账号，当使用企业微信通知员工时，此条数据必填，否则消息无法通知成功，有问题请联系技术</div>
                </el-collapse-item>
              </el-collapse>
            </el-form>
          </div>
        </div>
    </div>
</template>

<script>
export default {
  name: 'DefaultContent',
  props: ['showIndex'],
  data () {
    return {
      tips: '默认内容：企业可编辑一套名片页默认内容，快速对员工名片页初始化装修',
      val: true,
      activeNames: ['1'],
      ruleForm: {
        name: '', // 姓名
        avatar: '', // 头像
        tel: '', // 手机号
        weNum: '', // 微信号
        telephone: '', // 固话号码
        phone400: '', // 400电话
        email: '', // 邮箱
        fakeNum: '0', // 虚拟浏览人数
        realNum: '0', // 浏览人数
        profile: '', // 个人简介
        audio: '',
        audioName: '', // 音频名称
        sec: '', // 音频秒数
        videoName: '', // 视频名称
        videoPic: '', // 视频封面图
        music: '', // 背景音乐
        musicName: '', // 背景音乐名称
        musicSwitch: '1', // 背景是否自动播放
        cWeNum: '' // 企业微信
      },
      rules: {
        name: {required: true, message: '请输入员工姓名', trigger: 'blur'},
        avatar: {required: true, message: '请上传头像', trigger: 'change'},
        tel: {required: true, pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur'},
        weNum: {required: true, message: '请输入微信号', trigger: 'blur'},
        telephone: {required: false, pattern: /0\d{2,3}-\d{7,8}$/, message: '请输入正确的电话', trigger: 'blur'},
        phone400: {required: false, pattern: /^400[0-9]{7}$/, message: '请输入正确的400电话', trigger: 'blur'},
        email: {required: true, pattern: /\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/, message: '请输入正确的邮箱', trigger: 'blur'},
        fakeNum: {required: true, pattern: /^\+?[0-9]*$/, message: '请输入正确的人数', trigger: 'blur'},
        realNum: {required: true, pattern: /^\+?[0-9]*$/, message: '请输入正确的人数', trigger: 'blur'},
        profile: {required: true, message: '请输入个人简介', trigger: 'blur'},

        cWeNum: {required: true, message: '请输入微信号', trigger: 'blur'}
      }
    }
  },
  created () {
    console.log('名片设置')
  },
  methods: {
    handleChange (val) {
    },
    handleAvatarSuccess (res, file) {
      let imgUrl = URL.createObjectURL(file.raw)
      console.log(imgUrl)
      this.ruleForm.avatar = imgUrl
    },
    beforeAvatarUpload (file) {
      const isLt2M = file.size / 1024 / 1024 < 2
      if (!isLt2M) {
        this.$message.error('上传头像图片大小不能超过 2MB!')
      }
      return isLt2M
    },
    beforeAudioUpload (file) {
      console.log(file)
      const isAudio = file.type === 'audio/mp3' || file.type === 'audio/mpeg'
      const isLt2M = file.size / 1024 / 1024 < 20
      this.getTimes(file)
      if (!isAudio) {
        this.$message.error('上传音频文件只能是 mp3和mpeg 格式!')
      }
      if (!isLt2M) {
        this.$message.error('上传音频文件大小不能超过 20MB!')
      }
      return isAudio && isLt2M
    },
    handleAudioSuccess (res, file) {
      console.log(res, file)
    },
    getTimes (file) {
      // 获取录音时长
      let url = URL.createObjectURL(file)
      console.log(url)
      // 经测试，发现audio也可获取视频的时长
      let audioElement = new Audio(url)
      audioElement.addEventListener('loadedmetadata', (_event) => {
        console.log(_event)
        this.sec = parseInt(audioElement.duration)
        console.log(this.sec)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-default-content{
        width: 100%;
        padding: 0 24px;
        margin: 32px 0;
        .card{
          width: 100%;
          display: flex;
          margin-top: 30px;
          &-show{
            min-width: 300px;
            height: 540px;
            border: 1px solid #ccc;
            margin-right: 20px;
          }
          &-form{
            width: 100%;
            height: 300px;
            .title{
              width: 100%;
              height: 40px;
              background: $bgColor;
              line-height: 40px;
              padding: 0 20px;
            }
            .el-form{
              .el-collapse-item__content{
                .el-input-number{
                    width: 300px;
                    text-align: left;
                    .el-input{
                      .el-input__inner{
                        text-align: left !important;
                      }
                    }
                  }
                .el-form-item{
                  .el-input{
                    width: 300px;
                  }
                  .el-textarea{
                    width: 300px;
                  }
                  .avatar-uploader {
                    width: 98px;
                    height: 98px;
                    border: 1px dashed #d9d9d9;
                    border-radius: 6px;
                    cursor: pointer;
                    position: relative;
                    overflow: hidden;
                  }
                  .avatar-uploader:hover {
                    border-color: #409EFF;
                  }
                  .avatar-uploader-icon {
                    font-size: 28px;
                    color: #8c939d;
                    width: 98px;
                    height: 98px;
                    line-height: 98px;
                    text-align: center;
                  }
                  .avatar {
                    width: 98px;
                    height: 98px;
                    display: block;
                  }
                  .audio-name{
                    width: 300px;
                    border: 1px solid #C3C3C3;
                    display: flex;
                    justify-content: space-between;
                    border-radius: 5px;
                    align-items: center;
                    overflow: hidden;
                    white-space: nowrap;
                    padding-left: 20px;
                  }
                }
                .item-tips{
                  margin-left: 120px;
                }
              }
            }
          }
        }
    }
</style>
