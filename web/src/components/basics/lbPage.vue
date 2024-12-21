<template>
    <div class="lb-page">
        <div v-if="batch">
            <div>已选{{selected}}条</div>
            <div>
                <span>批量</span>
                <slot></slot>
                <!-- <lb-button>批量下架</lb-button>
                <lb-button>批量上架</lb-button>
                <lb-button>批量删除</lb-button> -->
            </div>
        </div>
        <span v-else></span>
        <el-pagination
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :current-page="currentPage"
            :page-sizes="[5,10,20]"
            :page-size="currentPageSize"
            layout="total, sizes, prev, pager, next, jumper"
            :total="total">
        </el-pagination>
    </div>
</template>

<script>
export default {
  props: {
    batch: {
      type: Boolean,
      default: true
    },
    page: {
      type: Number,
      default: 1
    },
    pageSize: {
      type: Number,
      default: 10
    },
    total: {
      type: Number,
      default: 0
    },
    selected: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      currentPage: this.page,
      currentPageSize: this.pageSize
    }
  },
  methods: {
    handleSizeChange (val) {
      this.currentPageSize = val
      this.$emit('handleSizeChange', val)
    },
    handleCurrentChange (val) {
      this.currentPage = val
      this.$emit('handleCurrentChange', val)
    }
  }
}
</script>

<style lang="scss" scoped>
    .lb-page{
        width: 100%;
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        height: 40px;
        font-size: 14px;
        color: #101010;
        >div{
          display: flex;
          align-items: center;
          white-space: nowrap;
          >div{
            &:first-child{
              height: 40px;
              padding-right: 30px;
              margin-right: 30px;
              border-right: 1px solid #E8E8E8;
              line-height: 40px;
            }
            .el-button{
              margin-left: 10px;
            }
          }
        }
    }
</style>
