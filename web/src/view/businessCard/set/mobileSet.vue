<template>
    <div class="lb-mobile-set">
        <top-nav></top-nav>
        <div class="set-form">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="120px">
              <el-form-item label="创建名片开关" prop='cardSwitch'>
                <el-radio-group v-model="ruleForm.cardSwitch">
                  <el-radio label="1">开启</el-radio>
                  <el-radio label="2">关闭</el-radio>
                </el-radio-group>
              </el-form-item>
              <el-form-item label="创建文案" prop="cardText">
                <el-input v-model="ruleForm.cardText" placeholder="请输入名片文案"></el-input>
              </el-form-item>
              <div class="item-tips">名片列表创建名片文案</div>
              <el-form-item label="免审口令" prop="word">
                <el-input v-model="ruleForm.word" placeholder="请输入免审口令"></el-input>
              </el-form-item>
              <div class="item-tips">设置了此选项后，用户在小程序端创建名片时输入此正确免审口令则无需管理员在后台设置员工，将自动成为员工</div>
              <el-form-item label="口令错误" prop="errWord">
                <el-input v-model="ruleForm.errWord" placeholder="请输入口令错误时的文案"></el-input>
              </el-form-item>
              <div class="item-tips">用户在小程序端创建名片时，免审口令填写错误提示文案</div>
              <el-form-item label="未填写口令" prop="noWord">
                <el-input v-model="ruleForm.noWord" placeholder="请输入未填写口令时的文案"></el-input>
              </el-form-item>
              <div class="item-tips">用户在小程序端创建名片时，免审口令没有填写提示文案</div>
              <el-form-item class="last-form-item">
                <lb-button type="success" size='medium' @click="onSubmit">保存</lb-button>
                <lb-button @click="onReset" size='medium' opType='reset'>重置</lb-button>
              </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      key: '手机端创建设置',
      ruleForm: {
        cardSwitch: '1',
        cardText: '',
        word: '',
        errWord: '',
        noWord: ''
      },
      rules: {
        cardSwitch: {required: true, message: '请选择开关', trigger: 'blur'},
        cardText: {required: true, message: '请输入名片文案', trigger: 'blur'},
        word: {required: true, message: '请输入免审口令', trigger: 'blur'},
        errWord: {required: true, message: '请输入口令错误时的文案', trigger: 'blur'},
        noWord: {required: true, message: '请输入未填写口令时的文案', trigger: 'blur'}
      }
    }
  },
  methods: {
    onSubmit () {
      console.log(133)
    },
    onReset () {

    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-mobile-set{
    width: 100%;
    .set-form{
      width: 100%;
      padding: 20px;
      .el-form{
        width: 100%;
        .el-form-item{

          margin-top: 10px;
          .el-input{
            width: 300px;
          }
        }
        .last-form-item{
          margin-top: 30px;
        }
        .item-tips{
          margin-left: 120px;
          color: #999999;
        }
      }
    }
  }
</style>
