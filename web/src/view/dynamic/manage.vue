<template>
    <div class="lb-dy-manage">
      <top-nav></top-nav>
      <div class="page-main">
        <lb-button type='success' opType='add'>发企业动态</lb-button>
        <lb-button type='success' opType='add'>添加链接动态</lb-button>
        <lb-button type='success' opType='add'>添加视频动态</lb-button>
        <div class="form-search">
          <el-form :inline="true" :model="formRules">
            <el-form-item label="动态状态">
              <el-select v-model="formRules.status" placeholder="请选择">
                <el-option label="全部" value="0"></el-option>
                <el-option label="公司" value="1"></el-option>
                <el-option label="个人" value="2"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="时间">
              <el-date-picker
                v-model="formRules.date"
                type="datetimerange"
                range-separator="至"
                start-placeholder="开始时间"
                end-placeholder="结束时间">
              </el-date-picker>
            </el-form-item>
            <el-form-item label="搜索">
              <el-input v-model="formRules.keywords" placeholder="请输入商品名称"></el-input>
            </el-form-item>
          </el-form>
        </div>
        <div class="message" v-for="(item,index) in msgList" :key='index'>
          <div class="msg-top">
            <img class="msg-l" :src="item.img">
            <div class='msg-r'>
              <div class="msg-r-head">
                <span class="cname">{{item.cname}}</span>
                <el-tag type="info">{{item.ctype}}</el-tag>
              </div>
              <div class="msg-r-content">
                <div>{{item | handleContent}}</div>
                <span class="all" @click="lookAll(index)">全部</span>
              </div>
              <div class="msg-r-img">
                <img v-for="(o,i) in item.imgList" :key='i' :src="o" >
              </div>
            </div>
          </div>
          <div class="msg-bot">
            <span>2019-04-29 14:53:23</span>
            <div class="bot-l">
              <div class="icon-btn">
                <i class="iconfont icon-like"></i>
                <span>1</span>
              </div>
              <div class="icon-btn">
                <i class="iconfont icon-liuyanguanli"></i>
                <span>1</span>
              </div>
              <div class="icon-btn">
                <i class="iconfont icon-zhiding"></i>
                <span>置顶</span>
              </div>
              <div class="icon-btn">
                <i class="iconfont icon-bianji"></i>
                <span>编辑</span>
              </div>
              <div class="icon-btn">
                <i class="iconfont icon-del"></i>
                <span>删除</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      formRules: {
        status: '',
        date: [],
        keywords: ''
      },
      msgList: [
        {
          img: require('../../assets/logo.png'),
          cname: '龙兵科技有限公司',
          ctype: '公司',
          content: '我们时常会在电影里看到很多的外星科幻电影，如《异形》、《变形金刚》、《阿凡达》等等外星生命的电影，虽然科学家们一直在探索宇宙，也在寻找其他外星生命，但目前还没有找到跟人类一样的碳基生命体存在，在这里就会不由自主地想到外星是否会存在其...',
          open: false,
          imgList: [
            require('../../assets/logo.png'),
            'https://fuss10.elemecdn.com/e/5d/4a731a90594a4af544c0c25941171jpeg.jpeg',
            require('../../assets/logo.png'),
            'https://fuss10.elemecdn.com/e/5d/4a731a90594a4af544c0c25941171jpeg.jpeg'
          ]
        }
      ]
    }
  },
  methods: {
    /**
     * @method 查看全部
     */
    lookAll (index) {
      if (!this.msgList[index].open) { this.msgList[index].open = true }
    }
  },
  filters: {
    handleContent (item) {
      let {content, open} = item
      return open ? content : content.substring(0, 80) + '...'
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-dy-manage{
    .el-button{
      margin: 5px;
    }
    .form-search{
      width: 100%;
      height: 104px;
      background: #F4F4F4;
      margin-top: 10px;
      display: flex;
      align-items: center;
      padding-left: 30px;
      margin-bottom: 20px;
      white-space: nowrap;
      .el-form{
        margin-top: 10px;
      }
    }
    .message{
      width: 100%;
      border-bottom: 1px solid #F4F4F4;
      .msg-top{
        display: flex;
        .msg-l{
          width: 50px;
          height: 50px;
          border-radius: 5px;
          margin-right: 20px;
          border: 1px solid #ccc;
        }
        .msg-r{
          width: 500px;
          margin: 10px 0;
          &-head{
            .cname{
              margin-right: 5px;
              font-size: 16px;
            }
          }
          &-content{
            font-size: 14px;
            div{
              margin: 10px 0;
            }
            .all{
              color: $themeColor;
              cursor: pointer;
            }
          }
          &-img{
            margin-top: 10px;
            img{
              display: block;
              height: 100px;
            }
          }
        }
      }
      .msg-bot{
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-left: 70px;
        .bot-l{
          display: flex;
          align-items: center;
          .icon-btn{
            font-size: 14px;
            margin-left: 20px;
            cursor: pointer;
            i{
              font-size: 16px;
            }
          }
        }
      }
    }
  }
</style>
