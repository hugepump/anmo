<template>
    <div class="lb-client-set">
      <top-nav></top-nav>
      <div class="page-main">
        <el-form :model="ruleForm" ref="ruleForm" label-width="120px">
          <el-form-item label="强制授权">
            <el-radio-group v-model="ruleForm.auth">
              <el-radio label="1">开启</el-radio>
              <el-radio label="2">关闭</el-radio>
            </el-radio-group>
          </el-form-item>
          <div class="item-tips">开启此功能后会强制要求用户授权手机号, 此功能影响用户体验, 谨慎开启。强制授权手机号与用户授权登录无关</div>
          <el-form-item label="交换名片">
            <el-radio-group v-model="ruleForm.change">
              <el-radio label="1">开启</el-radio>
              <el-radio label="2">关闭</el-radio>
            </el-radio-group>
          </el-form-item>
          <div class="item-tips">客户进入员工名片后是否弹出交换名片窗口</div>
          <el-form-item label="大图模式">
            <el-radio-group v-model="ruleForm.bigPic">
              <el-radio label="1">开启</el-radio>
              <el-radio label="2">关闭</el-radio>
            </el-radio-group>
          </el-form-item>
          <div class="item-tips">开启大图模式后，用户点击详情图片可以预览图片，并且可以长按图片识别二维码或者保存</div>
          <el-form-item label="名片版式">
            <el-radio-group v-model="ruleForm.cardStyle">
              <el-radio label="1">开启</el-radio>
              <el-radio label="2">关闭</el-radio>
            </el-radio-group>
          </el-form-item>
          <div class="item-tips">名片详情页面员工名片信息展示方式</div>
          <el-form-item label="个性签名">
            <el-radio-group v-model="ruleForm.sign">
              <el-radio label="1">开启</el-radio>
              <el-radio label="2">关闭</el-radio>
            </el-radio-group>
          </el-form-item>
          <div class="item-tips">员工名片是否显示个性签名栏目</div>
          <el-form-item label="交换名片">
            <el-input v-model="ruleForm.btnText" placeholder="请输入内容"></el-input>
          </el-form-item>
          <div class="item-tips">名片详情页咨询按钮文字</div>
          <el-form-item label="交换手机号">
            <el-input v-model="ruleForm.tel" placeholder="请输入手机号"></el-input>
          </el-form-item>
          <div class="item-tips">名片详情页交换手机号自定义文字</div>
          <el-form-item>
            <lb-button type='success' size='medium' @click="onSubmit">保存</lb-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      key: '获客功能设置',
      ruleForm: {
        auth: '1',
        change: '1',
        bigPic: '1',
        cardStyle: '1',
        sign: '1',
        btnText: '',
        tel: ''
      }
    }
  },
  methods: {
    onSubmit () {
      this.$refs['ruleForm'].validate(valid => {
        if (valid) {
          if (this.ruleForm.btnText && this.$reg.tel.test(this.ruleForm.tel)) {
            this.$message.success('提交成功！')
          } else {
            this.$message.error('提交失败！')
          }
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-client-set{
    width: 100%;
    .page-main{
      width: 100%;
      .el-form{
        .el-form-item{
          margin-top: 10px;
          .el-input{
            width: 300px;
          }
        }
        .item-tips{
          margin-left: 120px;
          color: #999999;
        }
      }
    }
  }
</style>
