<template>
    <div class="lb-tag">
      <top-nav></top-nav>
      <div class="lb-tag-main">
        <lb-tips :title='title'></lb-tips>
        <div class="c-tags">
          <div class="label">企业标签</div>
          <el-tag
            :key="tag"
            v-for="tag in dynamicTags"
            closable
            type="success"
            size="medium"
            :disable-transitions="false"
            @close="handleClose(tag)">
            {{tag}}
          </el-tag>
          <el-input
            class="input-new-tag"
            v-if="inputVisible"
            v-model="inputValue"
            ref="saveTagInput"
            size="small"
            @keyup.enter.native="handleInputConfirm"
            @blur="handleInputConfirm"
          >
          </el-input>
          <el-button v-else class="button-new-tag" size="small" @click="showInput">+ 新增标签</el-button>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  data () {
    return {
      title: '企业标签将显示在您企业名下所有员工的名片中，最多30个标签。',
      dynamicTags: ['标签一', '标签二', '标签三'],
      inputVisible: false,
      inputValue: ''
    }
  },
  methods: {
    handleClose (tag) {
      this.dynamicTags.splice(this.dynamicTags.indexOf(tag), 1)
    },
    showInput () {
      this.inputVisible = true
      this.$nextTick(_ => {
        this.$refs.saveTagInput.$refs.input.focus()
      })
    },

    handleInputConfirm () {
      let inputValue = this.inputValue
      if (inputValue) {
        this.dynamicTags.push(inputValue)
      }
      this.inputVisible = false
      this.inputValue = ''
    }
  }
}
</script>

<style lang="scss" scoped>
  .lb-tag{
    width: 100%;
    &-main{
      padding: 20px;
    }
    .c-tags{
      margin-top: 30px;
      .label{
        margin-bottom: 20px;
      }
      .el-tag + .el-tag {
        margin-left: 10px;
      }
      .button-new-tag {
        margin-left: 10px;
        height: 29px;
        line-height: 29px;
        padding-top: 0;
        padding-bottom: 0;
      }
      .input-new-tag {
        width: 90px;
        margin-left: 10px;
        vertical-align: bottom;
      }
    }
  }
</style>
